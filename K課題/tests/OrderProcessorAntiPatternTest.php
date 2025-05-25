<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../技術選考課題_20250523.php';

class OrderProcessorAntiPatternTest extends TestCase
{
    private OrderProcessor $processor;
    private array $productInventory;

    protected function setUp(): void
    {
        $this->processor = new OrderProcessor();
        // テストデータを直接セットアップ（密結合）
        $this->productInventory = [
            'ITEM001' => 50,
            'ITEM002' => 30,
            'ITEM003' => 0,
        ];
    }

    /**
     * アンチパターン1: 複数の検証を1つのテストケースに詰め込む
     */
    public function testProcessNewOrderEverything(): void
    {
        // 1. 正常系のテスト
        $customer = new Customer('CUST100', '田中一郎', 'tanaka@example.com');
        $p1 = new Product('ITEM001', 'マウス', 3000);
        $p2 = new Product('ITEM002', 'キーボード', 15000);
        $items = [new OrderItem($p1, 1), new OrderItem($p2, 1)];
        
        $result = $this->processor->processNewOrder($customer, $items, 'CREDIT_CARD');
        $this->assertStringStartsWith('ORD', $result);

        // 2. 同じテストケース内で異常系もテスト
        $invalidCustomer = new Customer('', '', '');
        $result2 = $this->processor->processNewOrder($invalidCustomer, $items, 'CREDIT_CARD');
        $this->assertStringStartsWith('ERROR:', $result2);

        // 3. さらに在庫切れのテストも追加
        $p3 = new Product('ITEM003', 'モニター', 40000);
        $items3 = [new OrderItem($p3, 1)];
        $result3 = $this->processor->processNewOrder($customer, $items3, 'CREDIT_CARD');
        $this->assertStringStartsWith('ERROR:', $result3);
    }

    /**
     * アンチパターン2: 実装の詳細に依存したテスト
     */
    public function testInternalImplementationDetails(): void
    {
        $customer = new Customer('CUST100', '田中一郎', 'tanaka@example.com');
        $p1 = new Product('ITEM001', 'マウス', 3000);
        $items = [new OrderItem($p1, 1)];

        // 内部実装の詳細に依存したテスト
        $reflection = new ReflectionClass($this->processor);
        $property = $reflection->getProperty('productInventory');
        $property->setAccessible(true);
        
        $beforeStock = $property->getValue($this->processor)['ITEM001'];
        $this->processor->processNewOrder($customer, $items, 'CREDIT_CARD');
        $afterStock = $property->getValue($this->processor)['ITEM001'];
        
        $this->assertEquals($beforeStock - 1, $afterStock);
    }

    /**
     * アンチパターン3: 不適切な検証方法
     */
    public function testWithInappropriateAssertions(): void
    {
        $customer = new Customer('CUST100', '田中一郎', 'tanaka@example.com');
        $p1 = new Product('ITEM001', 'マウス', 3000);
        $items = [new OrderItem($p1, 1)];

        // 文字列の部分一致で検証（脆弱な検証）
        $result = $this->processor->processNewOrder($customer, $items, 'CREDIT_CARD');
        $this->assertTrue(strpos($result, 'ORD') !== false);

        // 実装の詳細に依存した検証
        $this->assertEquals(13, strlen($result));
    }

    /**
     * アンチパターン4: グローバルな状態に依存
     */
    public function testWithGlobalState(): void
    {
        // グローバルな状態に依存したテスト
        date_default_timezone_set('Asia/Tokyo');
        
        $customer = new Customer('CUST100', '田中一郎', 'tanaka@example.com');
        $p1 = new Product('ITEM001', 'マウス', 3000);
        $items = [new OrderItem($p1, 1)];

        $result = $this->processor->processNewOrder($customer, $items, 'CREDIT_CARD');
        
        // 時刻に依存したテスト
        $expectedPrefix = 'ORD' . time();
        $this->assertEquals($expectedPrefix, $result);
    }

    /**
     * アンチパターン5: 不適切なセットアップとティアダウン
     */
    public function testWithoutProperSetupAndTeardown(): void
    {
        // テストケースごとに重複したセットアップ
        $processor = new OrderProcessor();
        $customer = new Customer('CUST100', '田中一郎', 'tanaka@example.com');
        $p1 = new Product('ITEM001', 'マウス', 3000);
        $items = [new OrderItem($p1, 1)];

        $result = $processor->processNewOrder($customer, $items, 'CREDIT_CARD');
        $this->assertNotEmpty($result);

        // クリーンアップが必要な状態を残したまま
        // 次のテストに影響を与える可能性がある
    }
}
