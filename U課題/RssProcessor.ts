import * as fs from 'fs';
const { XMLParser } = require("fast-xml-parser");

/**
 * Rssフィールドから"NewsPicks"という文字列を取り除き、結果を出力する問題
 * 
 * 必要な処理
 * ・ 全ての処理を管理する
 * ・ RSSフィールドを読み込む
 * ・ ファイルの中身が正しいかチェック
 * ・ "NewsPicks"の文字列を取り除く
 * ・ 標準出力
 */

/**
 * 必要なクラス
 * ・ RssProcessor
 * ・ RssReader
 * ・ RssChecker
 * ・ RssTrim
 * ・ RssOutput
 * ・ RssItem
 */

export class RssItem {
	title: string;
	description: string;
	link: string;
	pubDate: string;
	guid: string;
	categories: string[];

	constructor(
		title: string,
		link: string,
		description: string,
		pubDate: string,
		guid: string = '',
		categories: string[] = []
	) {
		this.title = title;
		this.link = link;
		this.description = description;
		this.pubDate = pubDate;
		this.guid = guid;
		this.categories = categories;
	}
}

export class RssFetcherError extends Error {
	constructor(message: string) {
		super(message);
		this.name = 'RssFetcherError';
	}
}

export class RssFetcher {
	fetch(filePath: string):string {
		const fileData = fs.readFileSync(filePath, 'utf-8');
		if (!fileData || fileData.trim().length === 0) throw new RssFetcherError("RSSフィールドの内容が空です");

		return fileData;
	}
}

export class RssParserError extends Error {
	constructor(message: string) {
		super(message);
		this.name = 'RssParserError';
	}
}

export class RssParser {
	/**
	 * XML形式のRSSフィードを解析してRssItemの配列を返す
	 * @param xmlContent XML形式のRSSフィード文字列
	 * @returns RssItemの配列
	 * @throws RssParserError
	 */
	parse(xmlContent: string): RssItem[] {
		try {
			const parser = new XMLParser();
			const parsed = parser.parse(xmlContent);

			if (!parsed?.rss?.channel?.item) {
				throw new RssParserError("RSSフィードのXML形式が不正です。正しいXML形式であることを確認してください。");
			}

			const rawItems = parsed.rss.channel.item;
			const itemsList = Array.isArray(rawItems) ? rawItems : [rawItems];
			
			return itemsList.map((item: any) => {
				const categories: string[] = [];

				if (item.category) {
					if (Array.isArray(item.category)) {
						item.category.forEach((cat: any) => categories.push(String(cat)));
					} else {
						categories.push(String(item.category));
					}
				}

				return new RssItem(
					String(item.title || ''),
					String(item.link || ''),
					String(item.description || ''),
					String(item.pubDate || ''),
					String(item.guid || ''),
					categories
				);
			});
		} catch (error) {
			if (error instanceof RssParserError) {
				throw error;
			}
			throw new RssParserError(`RSSフィードの解析に失敗しました: ${error instanceof Error ? error.message : '不明なエラー'}`);
		}
	}
}

export class RssValidator {

}

export class RssCleaner {

}

export interface OutputStrategyInterface {
	output(itmes: RssItem[]):void
}

export class RssOutput implements OutputStrategyInterface{
	output(items: RssItem[]):void {
		items.forEach(item => {
			console.log(item.title);
		})
	}
}

export class RssService {
	private fetcher: RssFetcher;
	private parser: RssParser;
	private validator: RssValidator;
	private cleaner: RssCleaner;
	private outputStrategy: OutputStrategyInterface;

	constructor(
		fetcher: RssFetcher,
		parser: RssParser,
		validator: RssValidator,
		cleaner: RssCleaner,
		outputStrategy: OutputStrategyInterface,
	) {
		this.fetcher = fetcher;
		this.parser = parser;
		this.validator = validator;
		this.cleaner = cleaner;
		this.outputStrategy = outputStrategy;
	}

	run(url: string) {
		// ここに処理を追記
		const content = this.fetcher.fetch(url);

		const items = this.parser.parse(content);
	}
}