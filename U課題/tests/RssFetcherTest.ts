import { RssFetcher } from '../RssProcessor';
import * as fs from 'fs';
import * as path from 'path';
import { strict as assert } from 'assert';

describe('RssFetcherのテスト', () => {
    const testRssPath = path.join(__dirname, '../test.rss');
    let fetcher: RssFetcher;

    beforeEach(() => {
        fetcher = new RssFetcher();
    });

    it('正常系: RSSファイルを読み込めること', () => {
        const result = fetcher.fetch(testRssPath);
        assert.ok(result.includes('<?xml version="1.0" encoding="UTF-8"?>'));
        assert.ok(result.includes('<rss version="2.0">'));
        assert.ok(result.includes('NewsPicks テスト記事1'));
    });
}); 