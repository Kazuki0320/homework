<?php

class RssItem {
    public string $title;
    public string $link;
    public string $description;
    public string $pubDate;
    public string $guid;
    public ?array $categories;

    public function __construct(
        string $title,
        string $link = '',
        string $description = '',
        string $pubDate = '',
        string $guid = '',
        array $categories = []
    ) {
        $this->title = $title;
        $this->link = $link;
        $this->description = $description;
        $this->pubDate = $pubDate;
        $this->guid = $guid;
        $this->categories = $categories;
    }
}

class RssFetcher {
    /**
     * 単一のURLからRSSフィードを取得
     * @param string $url RSSフィードのURL
     * @return string 取得したコンテンツ
     * @throws Exception
     */
    public function fetch(string $url): string {
        $content = @file_get_contents($url);
        if ($content === false) {
            throw new Exception("RSSフィードを取得できませんでした: $url");
        }
        return $content;
    }

    /**
     * 複数のURLからRSSフィードを取得
     * @param array $urls RSSフィードのURL配列
     * @return array 各URLに対応するコンテンツの配列
     */
    public function fetchMultiple(array $urls): array {
        $contents = [];
        foreach ($urls as $url) {
            try {
                $contents[$url] = $this->fetch($url);
            } catch (Exception $e) {
                // エラーが発生したURLをスキップして続行
                error_log("Failed to fetch RSS from: $url - " . $e->getMessage());
                continue;
            }
        }
        return $contents;
    }
}

class RssParser {
    /**
     * XMLコンテンツを解析
     * @param string $xml XMLコンテンツ
     * @return array RssItemの配列
     * @throws Exception
     */
    public function parse(string $xml): array {
        $items = [];
        $rss = @simplexml_load_string($xml);
        if ($rss === false) {
            throw new Exception("XMLの解析に失敗しました");
        }

        foreach ($rss->channel->item as $item) {
            // カテゴリーの取得
            $categories = [];
            if (isset($item->category)) {
                foreach ($item->category as $category) {
                    $categories[] = (string)$category;
                }
            }
						

            $items[] = new RssItem(
                (string)$item->title,
                (string)$item->link,
                (string)$item->description,
                (string)$item->pubDate,
                (string)$item->guid,
                $categories
            );
        }
        return $items;
    }

    /**
     * 複数のXMLコンテンツを解析
     * @param array $contents XMLコンテンツの配列
     * @return array 全てのRSSアイテムの配列
     */
    public function parseMultiple(array $contents): array {
        $allItems = [];
        foreach ($contents as $content) {
            try {
                $items = $this->parse($content);
                $allItems = array_merge($allItems, $items);
            } catch (Exception $e) {
                error_log("Failed to parse RSS content: " . $e->getMessage());
                continue;
            }
        }
        return $allItems;
    }
}

/**
 * バリデーションエラーを表す例外クラス
 */
class ValidationException extends Exception {}

class RssValidator {
    /**
     * RSSアイテムの配列を検証
     * @param array $items RssItemの配列
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    public function validate(array $items): void {
        if (empty($items)) {
            throw new ValidationException('RSSアイテムが空です');
        }

        foreach ($items as $index => $item) {
            if (!$item instanceof RssItem) {
                throw new ValidationException("インデックス {$index} のアイテムがRssItemクラスではありません");
            }

            // 必須項目の存在確認
            if (empty($item->title)) {
                throw new ValidationException("インデックス {$index} のアイテムのタイトルが空です");
            }

            // URLの形式検証
            if (!empty($item->link) && !filter_var($item->link, FILTER_VALIDATE_URL)) {
                throw new ValidationException("インデックス {$index} のアイテムのリンクが無効なURLです");
            }

            // GUIDの存在確認
            if (empty($item->guid)) {
                throw new ValidationException("インデックス {$index} のアイテムのGUIDが空です");
            }

            // 日付形式の検証
            if (!empty($item->pubDate) && strtotime($item->pubDate) === false) {
                throw new ValidationException("インデックス {$index} のアイテムの公開日が無効な形式です");
            }
        }
    }
}

/**
 * RSSコンテンツのクリーニングを行うクラス
 */
class ContentCleaner {
    /**
     * 指定された文字列を削除する対象フィールド
     */
    private array $targetFields = ['title', 'description', 'category'];

    /**
     * 削除する文字列のパターン
     */
    private string $removePattern = 'NewsPicks';

    /**
     * RSSアイテムの内容をクリーニング
     * @param RssItem $item クリーニング対象のアイテム
     * @return RssItem クリーニング済みのアイテム
     */
    public function clean(RssItem $item): RssItem {
        // 各対象フィールドに対してクリーニングを実行
        foreach ($this->targetFields as $field) {
            if (isset($item->$field)) {
                $item->$field = $this->cleanText($item->$field);
            }
        }

        // カテゴリーのクリーニング
        if (!empty($item->categories)) {
            $item->categories = array_map(
                fn($category) => $this->cleanText($category),
                $item->categories
            );
        }

        return $item;
    }

    /**
     * テキストから指定パターンを削除し、整形する
     * @param string $text クリーニング対象のテキスト
     * @return string クリーニング済みのテキスト
     */
    private function cleanText(string $text): string {
        // 指定パターンの削除
        $text = str_replace($this->removePattern, '', $text);

        // 余分な空白の削除と整形
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return $text;
    }
}

/**
 * RSSアイテムをファイルに保存するクラス
 */
class FileSaver {
    /**
     * RSSアイテムをテキストファイルに保存
     * 
     * @param array $items 保存するRssItemの配列
     * @param string $filename 保存先のファイル名
     * @throws Exception ファイルの書き込みに失敗した場合
     */
    public function save(array $items, string $filename): void {
        try {
            $content = '';
            foreach ($items as $item) {
                // 各アイテムの内容を整形
                $content .= "タイトル: " . $item->title . "\n";
                $content .= "リンク: " . $item->link . "\n";
                $content .= "説明: " . $item->description . "\n";
                if (!empty($item->pubDate)) {
                    $content .= "公開日: " . $item->pubDate . "\n";
                }
                if (!empty($item->categories)) {
                    $content .= "カテゴリー: " . implode(", ", $item->categories) . "\n";
                }
                $content .= str_repeat("-", 50) . "\n\n";
            }

            // ファイルに書き込み
            $result = file_put_contents($filename, $content);
            if ($result === false) {
                throw new Exception("ファイルの書き込みに失敗しました: " . $filename);
            }
        } catch (Exception $e) {
            throw new Exception("ファイル保存中にエラーが発生しました: " . $e->getMessage());
        }
    }
}

/**
 * 標準出力を管理するクラス
 */
class ConsoleOutput {
    /**
     * RSSアイテムを標準出力に表示
     * @param array $items 表示するRssItemの配列
     */
    public function display(array $items): void {
        foreach ($items as $item) {
            echo $item->title . "\n";
            echo $item->description . "\n";
            echo str_repeat('-', 50) . "\n";
        }
    }

    /**
     * エラーメッセージを標準エラー出力に表示
     * @param string $message エラーメッセージ
     */
    public function displayError(string $message): void {
        fwrite(STDERR, $message . "\n");
    }
}

class RssProcessor {
    private RssFetcher $fetcher;
    private RssParser $parser;
    private RssValidator $validator;
    private ContentCleaner $cleaner;
    private ConsoleOutput $output;
    private FileSaver $saver;

    public function __construct(
        RssFetcher $fetcher,
        RssParser $parser,
        RssValidator $validator,
        ContentCleaner $cleaner,
        ConsoleOutput $output,
        FileSaver $saver
    ) {
        $this->fetcher = $fetcher;
        $this->parser = $parser;
        $this->validator = $validator;
        $this->cleaner = $cleaner;
        $this->output = $output;
        $this->saver = $saver;
    }

    /**
     * 単一のRSSフィードを処理
     * @param string $url RSSフィードのURL
     */
    public function process(string $url): void {
        try {
            // 1. RSSフィードを取得
            $content = $this->fetcher->fetch($url);

            // 2. XMLを解析
            $items = $this->parser->parse($content);

            // 3. バリデーション
            $this->validator->validate($items);

            // 4. タイトルのクリーニング
            $cleanedItems = array_map(fn($item) => $this->cleaner->clean($item), $items);

            // 5. ファイルに保存
            $this->saver->save($cleanedItems, 'output.txt');

            // 6. 標準出力に結果を表示
            $this->output->display($cleanedItems);

        } catch (ValidationException $e) {
            // バリデーションエラーの場合
            $this->output->displayError("バリデーションエラー: " . $e->getMessage());
        } catch (Exception $e) {
            // その他のエラーの場合
            $this->output->displayError("エラーが発生しました: " . $e->getMessage());
        }
    }

    /**
     * 複数のRSSフィードを処理
     * @param array $urls RSSフィードのURL配列
     */
    public function processMultiple(array $urls): void {
        try {
            // 1. 複数のRSSフィードを取得
            $contents = $this->fetcher->fetchMultiple($urls);

            // 2. 取得したコンテンツを解析
            $items = $this->parser->parseMultiple($contents);

            // 3. バリデーション
            $this->validator->validate($items);

            // 4. タイトルのクリーニング
            $cleanedItems = array_map(fn($item) => $this->cleaner->clean($item), $items);

            // 5. ファイルに保存
            $this->saver->save($cleanedItems, 'output.txt');

            // 6. 標準出力に結果を表示
            $this->output->display($cleanedItems);

        } catch (ValidationException $e) {
            // バリデーションエラーの場合
            $this->output->displayError("バリデーションエラー: " . $e->getMessage());
        } catch (Exception $e) {
            // その他のエラーの場合
            $this->output->displayError("エラーが発生しました: " . $e->getMessage());
        }
    }
}