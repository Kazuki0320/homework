<?php
class RssFetcher {
	public function fetch(string $url): string {
		$content = file_get_contents($url);
		if ($content === false) {
			throw new Exception("RSSフィードを取得できませんでした");
		}
		return $content;
	}
}

class RssParser {
	public function parse(string $xml): array {
			// 処理は後で実装
			return [];
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

	public function rssProcessor(array $urls)
	{
		$allItems = [];

		foreach($urls as $url) {
			try {
				//1. RSSフィードを取得
				$getContet = $this->fetcher->fetch($url);
	
				//2. XMLを解析
				$this->parser->parse($getContet);
	
				//3. バリデーション
				$this->validator->RssValidate();
	
				//4. 「NewsPicks」の文字列を取り除く
				$this->cleaner->RssCleaner();
	
				//5. 標準出力
				$this->console->TextConsole();
	
				//6.　テキストファイルに保存
				$this->saver->TextSaver();
			} catch(Exception $e) {
				echo "Error processing $url: " . $e->getMessage() . PHP_EOL;
			}	
		}
	}
}
?> 