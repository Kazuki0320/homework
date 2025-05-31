<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../RssProcessor.php';

class RssProcessorTest extends TestCase
{
    private RssFetcher $fetcher;

    protected function setUp(): void
    {
        $this->fetcher = new RssFetcher();
    }

    public function testFetchRssFromUzabaseTech(): void
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
} 