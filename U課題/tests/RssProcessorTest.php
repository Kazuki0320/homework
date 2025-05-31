<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../RssProcessor.php';

class RssProcessorTest extends TestCase
{
    private RssFetcher $fetcher;
    private RssParser $parser;

    protected function setUp(): void
    {
        $this->fetcher = new RssFetcher();
        $this->parser = new RssParser();
    }

    /**
     * 単一のRSSフィードの取得をテスト
     */
    public function testFetchSingleRssFromUzabaseTech(): void
    {
        // 実際のRSSフィードURLをテスト
        $url = 'https://tech.uzabase.com/rss';
        
        // RSSフィードを取得
        $result = $this->fetcher->fetch($url);
        
        // 取得したコンテンツが空でないことを確認
        $this->assertNotEmpty($result);
        
        // XMLとして解析可能であることを確認
        $xml = simplexml_load_string($result);
        $this->assertNotFalse($xml);
        
        // 基本的なRSS要素が含まれていることを確認
        $this->assertNotEmpty((string)$xml->channel->title);
        $this->assertNotEmpty($xml->channel->item);
    }

    /**
     * 複数のRSSフィードの取得をテスト
     */
    public function testFetchMultipleRssFeeds(): void
    {
        // テスト用のURL配列
        $urls = [
            'https://tech.uzabase.com/rss',
            'https://gihyo.jp/feed/atom' // 技術評論社のフィード
        ];

        // 複数のRSSフィードを取得
        $contents = $this->fetcher->fetchMultiple($urls);

        // 取得結果の検証
        $this->assertIsArray($contents);
        $this->assertCount(2, $contents);
        
        // 各フィードの内容を検証
        foreach ($contents as $url => $content) {
            $this->assertNotEmpty($content);
            $xml = simplexml_load_string($content);
            $this->assertNotFalse($xml);
        }
    }

    /**
     * RSSパース処理の単体テスト
     */
    public function testParseRssContent(): void
    {
        // テスト用のRSSコンテンツを準備
        $url = 'https://tech.uzabase.com/rss';
        $content = $this->fetcher->fetch($url);

        // コンテンツをパース
        $items = $this->parser->parse($content);

        // パース結果の検証
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
        
        // 各アイテムの構造を検証
        foreach ($items as $item) {
            $this->assertInstanceOf(RssItem::class, $item);
            $this->assertNotEmpty($item->title);
            $this->assertNotEmpty($item->link);
        }
    }

    /**
     * 複数フィードのパース処理をテスト
     */
    public function testParseMultipleRssContents(): void
    {
        // テスト用のURL配列
        $urls = [
            'https://tech.uzabase.com/rss',
            'https://www.publickey1.jp/atom.xml'
        ];

        // フィードを取得
        $contents = $this->fetcher->fetchMultiple($urls);

        // 複数フィードをパース
        $items = $this->parser->parseMultiple($contents);

        // パース結果の検証
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);

        // 各アイテムの構造とソース情報を検証
        foreach ($items as $item) {
            $this->assertInstanceOf(RssItem::class, $item);
            $this->assertNotEmpty($item->title);
            $this->assertNotEmpty($item->source);
            $this->assertTrue(in_array($item->source, $urls));
        }
    }

    /**
     * エラーケースのテスト
     */
    public function testHandleInvalidUrl(): void
    {
        // 無効なURLでの例外発生を確認
        $this->expectException(Exception::class);
        $this->fetcher->fetch('https://invalid-url.example.com/rss');
    }
} 