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
        $this->assertEquals('NewsPicks: テスト記事', $item->getTitle());
        $this->assertEquals('https://tech.uzabase.com/entry/test', $item->getLink());
        $this->assertEquals('NewsPicks テスト記事の説明文', $item->getDescription());
        $this->assertEquals('Mon, 12 May 2025 10:35:29 +0900', $item->getPubDate());
        $this->assertEquals('test-guid-1', $item->getGuid());
        
        // カテゴリーの検証
        $this->assertCount(2, $item->getCategories());
        $categories = $item->getCategories();
        $this->assertEquals('NewsPicks テストカテゴリー1', $categories[0]);
        $this->assertEquals('テストカテゴリー2', $categories[1]);
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
        $this->assertEquals(': テスト記事', $cleanedItem->getTitle());
        $this->assertEquals('テスト説明', $cleanedItem->getDescription());
        $categories = $cleanedItem->getCategories();
        $this->assertEquals('カテゴリー', $categories[0]);
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
            ->method('output')
            ->with($this->equalTo($items));

        // 出力実行
        $this->output->output($items);
    }

    /**
     * エラー出力処理のテスト
     */
    public function testConsoleErrorOutput(): void
    {
        $errorMessage = 'テストエラーメッセージ';

        // エラー出力の期待値を設定
        $this->output->expects($this->once())
            ->method('outputError')
            ->with($this->equalTo($errorMessage));

        // エラー出力実行
        $this->output->outputError($errorMessage);
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
            $this->assertNotEmpty($item->getTitle());
            $this->assertNotEmpty($item->getGuid());
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
            ->method('outputError')
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
            ->method('output');
        
        $this->saver->expects($this->once())
            ->method('output')
            ->with($this->isType('array'));

        // 処理実行
        $this->processor->process('https://tech.uzabase.com/rss');
    }

    /**
     * ファイル保存処理のテスト
     */
    public function testFileSaving(): void
    {
        // テスト用の一時ファイル名を生成
        $tempFile = sys_get_temp_dir() . '/rss_test_' . uniqid() . '.txt';

        // テスト用のRSSアイテムを作成
        $items = [
            new RssItem(
                'テストタイトル1',
                'https://example.com/1',
                'テスト説明1',
                '2024-03-20 10:00:00',
                'guid1',
                ['カテゴリー1', 'カテゴリー2']
            ),
            new RssItem(
                'テストタイトル2',
                'https://example.com/2',
                'テスト説明2'
            )
        ];

        // 実際のFileSaverインスタンスを作成
        $saver = new FileSaver($tempFile);

        // ファイルに保存
        $saver->output($items);

        // ファイルが作成されたことを確認
        $this->assertFileExists($tempFile);

        // ファイルの内容を確認
        $content = file_get_contents($tempFile);
        $this->assertStringContainsString('テストタイトル1', $content);
        $this->assertStringContainsString('https://example.com/1', $content);
        $this->assertStringContainsString('テスト説明1', $content);
        $this->assertStringContainsString('2024-03-20 10:00:00', $content);
        $this->assertStringContainsString('カテゴリー1, カテゴリー2', $content);
        $this->assertStringContainsString('テストタイトル2', $content);

        // テスト後のクリーンアップ
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
    }

    /**
     * ファイル保存のエラーケースのテスト
     */
    public function testFileSavingError(): void
    {
        // 書き込み権限のないディレクトリにファイルを作成しようとする
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('ファイル保存中にエラーが発生しました');

        $saver = new FileSaver('/root/test.txt');
        $saver->output([new RssItem('テスト', 'https://example.com', 'テスト')]);
    }
} 