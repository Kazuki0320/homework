# RSSフィード処理システム

## 概要
このシステムは、RSSフィードを取得、解析、検証、加工し、様々な形式で出力する機能を提供します。オブジェクト指向設計の原則に従い、各機能を独立したクラスとして実装しています。

## 主な機能
1. RSSフィードの取得（複数URL対応）
2. XMLデータの解析とRssItemオブジェクトへの変換
3. データの検証（タイトル、URL、日付形式など）
4. コンテンツのクリーニング（不要な文字列の削除）
5. 複数の出力形式対応（ファイル保存、コンソール出力）

### 基本クラス
- **RssItem**: RSSフィードの各アイテムを表現
  - タイトル、リンク、説明、公開日、GUID、カテゴリーを管理

### 処理クラス
- **RssFetcher**: URLからRSSフィードを取得
- **RssParser**: XMLを解析してRssItemオブジェクトを生成
- **RssValidator**: データの正当性を検証
- **ContentCleaner**: 特定のパターンを削除してテキストを整形

### 出力クラス
- **OutputStrategyInterface**: 出力方法の共通インターフェース
- **FileSaver**: ファイルへの保存を担当
- **ConsoleOutput**: 標準出力への表示を担当

### メインクラス
- **RssProcessor**: 全体の処理フローを制御

## 使用例

```php
// 必要なインスタンスを生成
$fetcher = new RssFetcher();
$parser = new RssParser();
$validator = new RssValidator();
$cleaner = new ContentCleaner();
$output = new ConsoleOutput();
$saver = new FileSaver('output.txt');

// プロセッサーを初期化
$processor = new RssProcessor(
    $fetcher,
    $parser,
    $validator,
    $cleaner,
    $output,
    $saver
);

// 単一のRSSフィードを処理
$processor->process('https://example.com/rss');

// 複数のRSSフィードを処理
$processor->processMultiple([
    'https://example.com/rss1',
    'https://example.com/rss2'
]);
```

## エラー処理
- 各処理段階で適切なエラーメッセージを生成
- バリデーションエラーは個別に捕捉
- ファイル操作やネットワークエラーにも対応
- エラーメッセージは日本語で分かりやすく表示

## 設計の特徴
1. **単一責任の原則**: 各クラスが特定の機能に特化
2. **Strategy パターン**: 出力方法を柔軟に切り替え可能
3. **依存性注入**: 各コンポーネントの結合度を低く保持
4. **エラー処理**: 段階的なエラーハンドリングで問題を特定しやすい

## 拡張性
- 新しい出力形式の追加が容易（OutputStrategyInterfaceを実装）
- クリーニングルールのカスタマイズが可能
- バリデーションルールの追加・変更が容易 