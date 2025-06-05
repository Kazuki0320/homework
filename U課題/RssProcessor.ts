import * as fs from 'fs';

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

	constructor(title:string, description: string, link: string, pubDate: string) {
		this.title = title;
		this.description = description;
		this.link = link;
		this.pubDate = pubDate;
	}
}

export class RssFetcherError extends Error {
	constructor(message: string) {
		super(message);
		this.name = 'RssFetcherError';
	}
}

export class RssFetcher {
	fetch(url: string):string {
		const fileData = fs.readFileSync(url, 'utf-8');
		if (empty(fileData)) throw new Exception("指定されたURLからRSSフィールドを読み取りできませんでした");
		console.log(fileData);
		return fileData;
	}
}

export class RssParser {

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
		this.fetcher.fetch(url);
	}
}