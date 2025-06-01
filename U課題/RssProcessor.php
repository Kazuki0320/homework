<?php

class RssItem {
    private string $title;
    private string $link;
    private string $description;
    private string $pubDate;
    private string $guid;
    private ?array $categories;

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

    // Getters
    public function getTitle(): string
    {
        return $this->title;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPubDate(): string
    {
        return $this->pubDate;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getCategories(): ?array
    {
        return $this->categories;
    }

    // Setters
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function setLink(string $link): void
    {
        $this->link = $link;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setPubDate(string $pubDate): void
    {
        $this->pubDate = $pubDate;
    }

    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    public function setCategories(array $categories): void
    {
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
            if (empty($item->getTitle())) {
                throw new ValidationException("インデックス {$index} のアイテムのタイトルが空です");
            }

            // URLの形式検証
            if (!empty($item->getLink()) && !filter_var($item->getLink(), FILTER_VALIDATE_URL)) {
                throw new ValidationException("インデックス {$index} のアイテムのリンクが無効なURLです");
            }

            // GUIDの存在確認
            if (empty($item->getGuid())) {
                throw new ValidationException("インデックス {$index} のアイテムのGUIDが空です");
            }

            // 日付形式の検証
            if (!empty($item->getPubDate()) && strtotime($item->getPubDate()) === false) {
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
        // タイトルのクリーニング
        $item->setTitle($this->cleanText($item->getTitle()));
        
        // 説明のクリーニング
        $item->setDescription($this->cleanText($item->getDescription()));

        // カテゴリーのクリーニング
        if (!empty($item->getCategories())) {
            $item->setCategories(array_map(
                fn($category) => $this->cleanText($category),
                $item->getCategories()
            ));
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

interface OutputStrategyInterface {
    /**
     * RSSアイテムを出力
     * @param array $items 出力するRssItemの配列
     * @throws Exception 出力処理中にエラーが発生した場合
     */
    public function output(array $items): void;

    /**
     * エラーメッセージを出力
     * @param string $message エラーメッセージ
     */
    public function outputError(string $message): void;
}

/**
 * RSSアイテムをファイルに保存するクラス
 */
class FileSaver implements OutputStrategyInterface {
    private string $filename;

    public function __construct(string $filename = 'output.txt') {
        $this->filename = $filename;
    }

    /**
     * RSSアイテムをファイルに出力
     * @param array $items 出力するRssItemの配列
     * @throws Exception ファイルの書き込みに失敗した場合
     */
    public function output(array $items): void {
        try {
            $content = '';
            foreach ($items as $item) {
                // 各アイテムの内容を整形
                $content .= "タイトル: " . $item->getTitle() . "\n";
                $content .= "リンク: " . $item->getLink() . "\n";
                $content .= "説明: " . $item->getDescription() . "\n";
                if (!empty($item->getPubDate())) {
                    $content .= "公開日: " . $item->getPubDate() . "\n";
                }
                if (!empty($item->getCategories())) {
                    $content .= "カテゴリー: " . implode(", ", $item->getCategories()) . "\n";
                }
                $content .= str_repeat("-", 50) . "\n\n";
            }

            // ファイルに書き込み
            $result = file_put_contents($this->filename, $content);
            if ($result === false) {
                throw new Exception("ファイルの書き込みに失敗しました: " . $this->filename);
            }
        } catch (Exception $e) {
            throw new Exception("ファイル保存中にエラーが発生しました: " . $e->getMessage());
        }
    }

    /**
     * エラーメッセージをエラーログファイルに出力
     * @param string $message エラーメッセージ
     */
    public function outputError(string $message): void {
        $errorLog = date('Y-m-d H:i:s') . " - " . $message . "\n";
        error_log($errorLog, 3, $this->filename . '.error.log');
    }
}

/**
 * 標準出力を管理するクラス
 */
class ConsoleOutput implements OutputStrategyInterface {
    /**
     * RSSアイテムを標準出力に出力
     * @param array $items 出力するRssItemの配列
     */
    public function output(array $items): void {
        foreach ($items as $item) {
            echo "タイトル: " . $item->getTitle() . "\n";
            echo "リンク: " . $item->getLink() . "\n";
            echo "説明: " . $item->getDescription() . "\n";
            if (!empty($item->getPubDate())) {
                echo "公開日: " . $item->getPubDate() . "\n";
            }
            if (!empty($item->getCategories())) {
                echo "カテゴリー: " . implode(", ", $item->getCategories()) . "\n";
            }
            echo str_repeat('-', 50) . "\n\n";
        }
    }

    /**
     * エラーメッセージを標準エラー出力に出力
     * @param string $message エラーメッセージ
     */
    public function outputError(string $message): void {
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
            $this->saver->output($cleanedItems);

            // 6. 標準出力に結果を表示
            $this->output->output($cleanedItems);

        } catch (ValidationException $e) {
            // バリデーションエラーの場合
            $this->output->outputError("バリデーションエラー: " . $e->getMessage());
        } catch (Exception $e) {
            // その他のエラーの場合
            $this->output->outputError("エラーが発生しました: " . $e->getMessage());
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
            $this->saver->output($cleanedItems);

            // 6. 標準出力に結果を表示
            $this->output->output($cleanedItems);

        } catch (ValidationException $e) {
            // バリデーションエラーの場合
            $this->output->outputError("バリデーションエラー: " . $e->getMessage());
        } catch (Exception $e) {
            // その他のエラーの場合
            $this->output->outputError("エラーが発生しました: " . $e->getMessage());
        }
    }
}