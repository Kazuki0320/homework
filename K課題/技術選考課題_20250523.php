<?php
// --- データクラス ---
class Product {
    public string $id;
    public string $name;
    public float $price;

    public function __construct(string $id, string $name, float $price)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->price = $price;
    }
}

class Customer {
    public string $id;
    public string $name;
    public string $email;

    public function __construct(string $id, string $name, string $email)
    {
        $this->id    = $id;
        $this->name  = $name;
        $this->email = $email;
    }
}

class OrderItem {
    public Product $product;
    public int $quantity;

    public function __construct(Product $product, int $quantity)
    {
        $this->product  = $product;
        $this->quantity = $quantity;
    }
}

// --- 責務が混在しているクラス ---
class OrderProcessor
{
    private array $productInventory = [];
    private array $orderLog          = [];

    public function __construct()
    {
        // 初期在庫ダミーデータ
        $this->productInventory = [
            'ITEM001' => 50,
            'ITEM002' => 30,
            'ITEM003' => 0,   // 在庫切れ
        ];
    }

    /**
     * 注文を処理する（検証・在庫確認・価格計算・決済・更新・通知・ロギング）
     * @param Customer|null $customer
     * @param OrderItem[]   $items
     * @param string|null   $paymentType
     * @return string 注文IDまたはエラーメッセージ
     */
    public function processNewOrder(?Customer $customer, array $items, ?string $paymentType): string
    {
        $this->log("注文処理開始: 顧客ID=" . ($customer->id ?? 'null'));

        // 1. 入力検証
        if ($customer === null || empty($customer->id) || empty($customer->email)) {
            $this->log("エラー: 顧客情報が無効です。");
            return 'ERROR: Invalid customer data.';
        }
        if (empty($items)) {
            $this->log("エラー: 注文商品が空です。");
            return 'ERROR: Order items cannot be empty.';
        }
        if (empty($paymentType)) {
            $this->log("エラー: 支払い方法が指定されていません。");
            return 'ERROR: Payment type is required.';
        }
        $this->log("入力検証 OK.");

        // 2. 在庫確認 & 金額計算
        $totalAmount = 0.0;
        foreach ($items as $item) {
            if ($item->product === null || empty($item->product->id) || $item->quantity <= 0) {
                $this->log("エラー: 不正な商品情報が含まれています。");
                return 'ERROR: Invalid product data in order items.';
            }
            $stock = $this->productInventory[$item->product->id] ?? 0;
            if ($stock < $item->quantity) {
                $this->log("エラー: 在庫不足 - 商品ID={$item->product->id}, 要求={$item->quantity}, 在庫={$stock}");
                return "ERROR: Insufficient stock for product {$item->product->id}";
            }
            $totalAmount += $item->product->price * $item->quantity;
        }
        $this->log("在庫確認 OK. 合計金額={$totalAmount}");

        // 3. 決済処理（将来的に代引にも対応したい）
        $this->log("決済処理開始: 方法={$paymentType}, 金額={$totalAmount}");
        $success = false;
        if ($paymentType === 'CREDIT_CARD') {
            $this->log("[Payment Gateway] クレジットカード決済実行 (金額={$totalAmount})");
            $success = $this->callCreditCardApi($customer->id, $totalAmount);
        } elseif ($paymentType === 'BANK_TRANSFER') {
            $this->log("[Bank System] 銀行振込確認待ち");
            $success = true;
        } else {
            $this->log("エラー: 未対応の支払い方法です: {$paymentType}");
            return 'ERROR: Unsupported payment type.';
        }
        if (!$success) {
            $this->log("エラー: 決済処理に失敗しました。");
            return 'ERROR: Payment failed.';
        }
        $this->log("決済処理成功.");

        // 4. 在庫更新
        foreach ($items as $item) {
            $this->productInventory[$item->product->id] -= $item->quantity;
        }
        $this->log("在庫更新完了.");

        // 5. 注文ID生成 & ログ
        $orderId = 'ORD' . time();
        $this->log("注文確定: 注文ID={$orderId}");

        // 6. 通知メール
        $subject = "ご注文ありがとうございます (注文ID: {$orderId})";
        $body    = "{$customer->name}様\n\nご注文ありがとうございます。\n合計金額: {$totalAmount}円\n支払い方法: {$paymentType}\n";
        $this->sendEmail($customer->email, $subject, $body);
        $this->log("通知メール送信完了: To={$customer->email}");

        $this->log("注文処理正常終了: 注文ID={$orderId}");
        return $orderId;
    }

    private function callCreditCardApi(string $customerId, float $amount): bool
    {
        // ここで実際は処理をおこなう（省略）
        return true;
    }

    private function sendEmail(string $to, string $subject, string $body): void
    {
        // ここで実際は処理をおこなう（省略）
        $this->log("[Internal] メール送信 (To={$to}, Subject={$subject})");
    }

    private function log(string $msg): void
    {
        $timestamp = (new DateTime())->format(DateTime::ATOM);
        echo "[{$timestamp}] {$msg}\n";
    }
}

// --- PHPUnit テストクラス ---
use PHPUnit\Framework\TestCase;

class OrderProcessorTest extends TestCase
{
    private OrderProcessor $processor;
    private Customer $customer;
    private Product $p1;
    private Product $p2;
    private Product $p3;

    protected function setUp(): void
    {
        $this->processor = new OrderProcessor();
        $this->customer  = new Customer('CUST100', '田中一郎', 'ichiro.tanaka@example.com');
        $this->p1        = new Product('ITEM001', '高品質マウス', 3000);
        $this->p2        = new Product('ITEM002', 'メカニカルキーボード', 15000);
        $this->p3        = new Product('ITEM003', '4Kモニター', 40000);
    }

    public function testProcessNewOrderSuccess(): void
    {
        $items = [new OrderItem($this->p1, 1), new OrderItem($this->p2, 1)];
        $this->expectOutputRegex('/注文処理正常終了/');
        $orderId = $this->processor->processNewOrder($this->customer, $items, 'CREDIT_CARD');
        $this->assertStringStartsWith('ORD', $orderId);
    }

    public function testProcessNewOrderInsufficientStock(): void
    {
        $items = [new OrderItem($this->p1, 1), new OrderItem($this->p3, 1)];
        $this->expectOutputRegex('/在庫不足/');
        $result = $this->processor->processNewOrder($this->customer, $items, 'BANK_TRANSFER');
        $this->assertSame('ERROR: Insufficient stock for product ITEM003', $result);
    }

    public function testProcessNewOrderInvalidPayment(): void
    {
        $items = [new OrderItem($this->p1, 1)];
        $this->expectOutputRegex('/支払い方法が指定されていません/');
        $result = $this->processor->processNewOrder($this->customer, $items, null);
        $this->assertSame('ERROR: Payment type is required.', $result);
    }
}
