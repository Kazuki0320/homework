<?php

class RssItem {
	public string $title;
	public string $link;
	public string $description;
	public string $source;

	public function __construct(string $title, string $link = '', string $description = '', string $source = '') {
		$this->title = $title;
		$this->link = $link;
		$this->description = $description;
		$this->source = $source;
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
			$items[] = new RssItem(
				(string)$item->title,
				(string)$item->link,
				(string)$item->description
			);
		}
		return $items;
	}

	/**
	 * 複数のXMLコンテンツを解析
	 * @param array $contents URLとXMLコンテンツのマップ
	 * @return array 全てのRSSアイテムの配列
	 */
	public function parseMultiple(array $contents): array {
		$allItems = [];
		foreach ($contents as $url => $content) {
			try {
				$items = $this->parse($content);
				// ソース情報を追加
				foreach ($items as $item) {
					$item->source = $url;
				}
				$allItems = array_merge($allItems, $items);
			} catch (Exception $e) {
				error_log("Failed to parse RSS from: $url - " . $e->getMessage());
				continue;
			}
		}
		return $allItems;
	}
}

class RssValidator {
	public function validate(array $items): void {
			// 処理は後で実装
	}
}

class TitleCleaner {
	public function clean(RssItem $item): RssItem {
			// 処理は後で実装
			return $item;
	}
}

class ConsoleOutput {
	public function print(array $items): void {
			// 処理は後で実装
	}
}

class FileSaver {
	public function save(array $items, string $filename): void {
			// 処理は後で実装
	}
}

class RssProcessor {
	private RssFetcher $fetcher;
	private RssParser $parser;
	private RssValidator $validator;
	private TitleCleaner $cleaner;
	private ConsoleOutput $console;
	private FileSaver $saver;

	public function __construct(
		RssFetcher $fetcher,
		RssParser $parser,
		RssValidator $validator,
		TitleCleaner $cleaner,
		ConsoleOutput $console,
		FileSaver $saver
	)
	{
		$this->fetcher = $fetcher;
		$this->parser = $parser;
		$this->validator = $validator;
		$this->cleaner = $cleaner;
		$this->console = $console;
		$this->saver = $saver;
	}

	/**
	 * 単一のRSSフィードを処理
	 * @param string $url RSSフィードのURL
	 */
	public function process(string $url): void {
		// 1. RSSフィードを取得
		$content = $this->fetcher->fetch($url);

		// 2. XMLを解析
		$items = $this->parser->parse($content);

		// 3. バリデーション
		$this->validator->validate($items);

		// 4. タイトルのクリーニング
		$cleanedItems = array_map(fn($item) => $this->cleaner->clean($item), $items);

		// 5. 結果を表示
		$this->console->print($cleanedItems);

		// 6. ファイルに保存
		$this->saver->save($cleanedItems, 'output.txt');
	}

	/**
	 * 複数のRSSフィードを処理
	 * @param array $urls RSSフィードのURL配列
	 */
	public function processMultiple(array $urls): void {
		// 1. 複数のRSSフィードを取得
		$contents = $this->fetcher->fetchMultiple($urls);

		// 2. 取得したコンテンツを解析
		$items = $this->parser->parseMultiple($contents);

		// 3. バリデーション
		$this->validator->validate($items);

		// 4. タイトルのクリーニング
		$cleanedItems = array_map(fn($item) => $this->cleaner->clean($item), $items);

		// 5. 結果を表示
		$this->console->print($cleanedItems);

		// 6. ファイルに保存
		$this->saver->save($cleanedItems, 'output.txt');
	}
}
?> 