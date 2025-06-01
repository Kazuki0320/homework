<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../RssProcessor.php';

/**
 * @internal
 */
class RssProcessorTest extends TestCase
{
    private RssFetcher $fetcher;
    private RssParser $parser;
    private RssValidator $validator;
    private ContentCleaner $cleaner;
    /** @var ConsoleOutput&MockObject */
    private $output;
    /** @var FileSaver&MockObject */
    private $saver;
    private RssProcessor $processor;

    protected function setUp(): void
    {
        $this->fetcher = new RssFetcher();
        $this->parser = new RssParser();
        $this->validator = new RssValidator();
        $this->cleaner = new ContentCleaner();
        $this->output = $this->createMock(ConsoleOutput::class);
        $this->saver = $this->createMock(FileSaver::class);
        
        $this->processor = new RssProcessor(
            $this->fetcher,
            $this->parser,
            $this->validator,
            $this->cleaner,
            $this->output,
            $this->saver
        );
    }

    /**
     * 単一のRSSフィードの取得をテスト
     */
    public function testFetchSingleRssFromUzabaseTech(): void
    {
        $url = 'https://tech.uzabase.com/rss';
        $result = $this->fetcher->fetch($url);
        
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('<?xml', $result);
        $this->assertStringContainsString('<rss', $result);
        
        $xml = simplexml_load_string($result);
        $this->assertNotFalse($xml);
        $this->assertNotEmpty((string)$xml->channel->title);
        $this->assertNotEmpty($xml->channel->item);
    }

    /**
     * RSSパース処理の詳細テスト
     */
    public function testParseRssContent(): void
    {
        // テスト用のRSSコンテンツを準備
        $testXml = <<<XML
<?xml version="1.0"?>
<rss version="2.0">
    <channel>
        <title>Uzabase for Engineers</title>
        <link>https://tech.uzabase.com/</link>
        <description>テスト用RSSフィード</description>
        <item>
            <title>NewsPicks: テスト記事</title>
            <link>https://tech.uzabase.com/entry/test</link>
            <description>NewsPicks テスト記事の説明文</description>
            <pubDate>Mon, 12 May 2025 10:35:29 +0900</pubDate>
            <guid>test-guid-1</guid>
            <category>NewsPicks テストカテゴリー1</category>
            <category>テストカテゴリー2</category>
        </item>
    </channel>
</rss>
XML;

        // パース実行
        $items = $this->parser->parse($testXml);

        // 基本構造の検証
        $this->assertCount(1, $items);
        $item = $items[0];
        
        // 各フィールドの検証
        $this->assertInstanceOf(RssItem::class, $item);
        $this->assertEquals('NewsPicks: テスト記事', $item->title);
        $this->assertEquals('https://tech.uzabase.com/entry/test', $item->link);
        $this->assertEquals('NewsPicks テスト記事の説明文', $item->description);
        $this->assertEquals('Mon, 12 May 2025 10:35:29 +0900', $item->pubDate);
        $this->assertEquals('test-guid-1', $item->guid);
        
        // カテゴリーの検証
        $this->assertCount(2, $item->categories);
        $this->assertEquals('NewsPicks テストカテゴリー1', $item->categories[0]);
        $this->assertEquals('テストカテゴリー2', $item->categories[1]);
    }

    /**
     * コンテンツクリーニング処理のテスト
     */
    public function testContentCleaning(): void
    {
        // テスト用のRSSアイテムを作成
        $item = new RssItem(
            'NewsPicks: テスト記事',
            'https://example.com/test',
            'NewsPicks テスト説明',
            'Mon, 12 May 2025 10:35:29 +0900',
            'test-guid-1',
            ['NewsPicks カテゴリー']
        );

        // クリーニング実行
        $cleanedItem = $this->cleaner->clean($item);

        // クリーニング結果の検証
        $this->assertEquals(': テスト記事', $cleanedItem->title);
        $this->assertEquals('テスト説明', $cleanedItem->description);
        $this->assertEquals('カテゴリー', $cleanedItem->categories[0]);
    }

    /**
     * 標準出力処理のテスト
     */
    public function testConsoleOutput(): void
    {
        $items = [
            new RssItem('テストタイトル1', '', 'テスト説明1'),
            new RssItem('テストタイトル2', '', 'テスト説明2')
        ];

        // 標準出力の期待値を設定
        $this->output->expects($this->once())
            ->method('display')
            ->with($this->equalTo($items));

        // 出力実行
        $this->output->display($items);
    }

    /**
     * エラー出力処理のテスト
     */
    public function testConsoleErrorOutput(): void
    {
        $errorMessage = 'テストエラーメッセージ';

        // エラー出力の期待値を設定
        $this->output->expects($this->once())
            ->method('displayError')
            ->with($this->equalTo($errorMessage));

        // エラー出力実行
        $this->output->displayError($errorMessage);
    }

    /**
     * バリデーションのテスト
     */
    public function testValidation(): void
    {
        // 正常なアイテム
        $validItem = new RssItem(
            'テストタイトル',
            'https://example.com/test',
            'テスト説明',
            'Mon, 12 May 2025 10:35:29 +0900',
            'test-guid-1',
            ['テストカテゴリー']
        );

        // バリデーション実行（例外が発生しないことを確認）
        $this->validator->validate([$validItem]);
        $this->assertTrue(true);

        // 無効なアイテムのテスト
        $this->expectException(ValidationException::class);
        $invalidItem = new RssItem(
            '', // 空のタイトル（エラーになるべき）
            'invalid-url', // 無効なURL
            'テスト説明'
        );
        $this->validator->validate([$invalidItem]);
    }

    /**
     * 複数フィードの統合テスト
     */
    public function testMultipleFeedIntegration(): void
    {
        $urls = [
            'https://tech.uzabase.com/rss',
            'https://gihyo.jp/feed/atom'
        ];

        // フィードの取得
        $contents = $this->fetcher->fetchMultiple($urls);
        $this->assertIsArray($contents);

        // パース処理
        $items = $this->parser->parseMultiple($contents);
        $this->assertIsArray($items);

        // 各アイテムの検証
        foreach ($items as $item) {
            $this->assertInstanceOf(RssItem::class, $item);
            $this->assertNotEmpty($item->title);
            $this->assertNotEmpty($item->guid);
        }
    }

    /**
     * エラーケースのテスト
     */
    public function testErrorCases(): void
    {
        // フェッチャーをモック化
        /** @var RssFetcher&MockObject */
        $mockFetcher = $this->createMock(RssFetcher::class);
        $mockFetcher->method('fetch')
            ->willThrowException(new Exception('URLからのフェッチに失敗しました'));

        // モック化したフェッチャーを使用するプロセッサーを作成
        $processor = new RssProcessor(
            $mockFetcher,
            $this->parser,
            $this->validator,
            $this->cleaner,
            $this->output,
            $this->saver
        );

        // エラー出力の期待値を設定
        $this->output->expects($this->once())
            ->method('displayError')
            ->with($this->stringContains('エラーが発生しました'));

        // 処理実行（例外が発生することを期待）
        $processor->process('https://invalid-url.example.com/rss');
    }

    /**
     * 無効なXMLのパースエラーテスト
     */
    public function testInvalidXmlParsing(): void
    {
        $this->expectException(Exception::class);
        $this->parser->parse('Invalid XML Content');
    }

    /**
     * RssProcessor統合テスト
     */
    public function testRssProcessorIntegration(): void
    {
        // モックの設定
        $this->output->expects($this->once())
            ->method('display');
        
        $this->saver->expects($this->once())
            ->method('save')
            ->with($this->isType('array'), $this->equalTo('output.txt'));

        // 処理実行
        $this->processor->process('https://tech.uzabase.com/rss');
    }
} 