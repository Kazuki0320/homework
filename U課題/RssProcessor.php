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
            throw new Exception("指定されたURLからRSSフィードを取得できませんでした: {$url}");
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
                error_log("RSSフィードの取得に失敗しました - URL: {$url} - エラー: " . $e->getMessage());
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
            throw new Exception("RSSフィードのXML形式が不正です。正しいXML形式であることを確認してください。");
        }

        foreach ($rss->channel->item as $item) {
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
                error_log("RSSフィードの解析に失敗しました - エラー: " . $e->getMessage());
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
            throw new ValidationException('RSSフィードにアイテムが含まれていません');
        }

        foreach ($items as $index => $item) {
            if (!$item instanceof RssItem) {
                throw new ValidationException("アイテム番号 {$index} の形式が不正です。RssItemクラスのインスタンスである必要があります。");
            }

            if (empty($item->getTitle())) {
                throw new ValidationException("アイテム番号 {$index} のタイトルが設定されていません");
            }

            if (!empty($item->getLink()) && !filter_var($item->getLink(), FILTER_VALIDATE_URL)) {
                throw new ValidationException("アイテム番号 {$index} のリンクが正しいURL形式ではありません");
            }

            if (empty($item->getGuid())) {
                throw new ValidationException("アイテム番号 {$index} の一意識別子（GUID）が設定されていません");
            }

            if (!empty($item->getPubDate()) && strtotime($item->getPubDate()) === false) {
                throw new ValidationException("アイテム番号 {$index} の公開日が正しい日付形式ではありません");
            }
        }
    }
}

/**
 * RSSコンテンツのクリーニングを行うクラス
 */
class ContentCleaner {
    /**
     * 削除する文字列のパターン
     */
    private string $removePattern;

    /**
     * コンストラクタ
     * @param string $removePattern 削除する文字列のパターン
     */
    public function __construct(string $removePattern = "NewsPicks")
    {
        $this->removePattern = $removePattern;
    }

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

            $result = file_put_contents($this->filename, $content);
            if ($result === false) {
                throw new Exception("ファイルの保存に失敗しました。保存先: {$this->filename}");
            }
        } catch (Exception $e) {
            throw new Exception("ファイルの保存処理中にエラーが発生しました: " . $e->getMessage());
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
    private OutputStrategyInterface $outputStrategy;

    public function __construct(
        RssFetcher $fetcher,
        RssParser $parser,
        RssValidator $validator,
        ContentCleaner $cleaner,
        OutputStrategyInterface $outputStrategy
    ) {
        $this->fetcher = $fetcher;
        $this->parser = $parser;
        $this->validator = $validator;
        $this->cleaner = $cleaner;
        $this->outputStrategy = $outputStrategy;
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

            // 5. 出力戦略を使用して結果を出力
            $this->outputStrategy->output($cleanedItems);

        } catch (ValidationException $e) {
            $this->outputStrategy->outputError("データの検証でエラーが発生しました: " . $e->getMessage());
        } catch (Exception $e) {
            $this->outputStrategy->outputError("処理中にエラーが発生しました: " . $e->getMessage());
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

            // 5. 出力戦略を使用して結果を出力
            $this->outputStrategy->output($cleanedItems);

        } catch (ValidationException $e) {
            $this->outputStrategy->outputError("データの検証でエラーが発生しました: " . $e->getMessage());
        } catch (Exception $e) {
            $this->outputStrategy->outputError("処理中にエラーが発生しました: " . $e->getMessage());
        }
    }
}
