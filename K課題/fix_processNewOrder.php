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

// 1. 入力検証
class OrderValidator {
    public function validateOrder(Customer $customer, array $items, string $paymentType): void
    {
        $this->validateCustomer($customer);
        $this->validateItems($items);
        $this->validatePaymentType($paymentType);
    }

    private function validateCustomer(Customer $customer): void
    {
        if (empty($customer->id) || empty($customer->email)) {
            throw new Error('顧客情報が不足しています');
        }
    }

    private function validateItems(array $items): void
    {
        if (empty($items)) {
            throw new Error('注文商品が空です');
        }
    }

    private function validatePaymentType(string $paymentType): void
    {
        if (empty($paymentType)) {
            throw new Error('注文方法が指定されていません');
        }
    }
}

// 支払い処理の例外クラス
class PaymentException extends Exception {
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class UnsupportedPaymentTypeException extends PaymentException {}
class PaymentProcessingException extends PaymentException {}

// 支払い処理のインターフェース
interface PaymentProcessor {
    public function process(Customer $customer, float $amount): bool;
}

// クレジットカード決済の実装
class CreditCardPaymentProcessor implements PaymentProcessor {
    public function process(Customer $customer, float $amount): bool {
        // クレジットカード決済の実装
        return $this->callCreditCardApi($customer->id, $amount);
    }

    private function callCreditCardApi(string $customerId, float $amount): bool {
        // ここで実際のAPI呼び出しを行う（省略）
        return true;
    }
}

// 銀行振込の実装
class BankTransferPaymentProcessor implements PaymentProcessor {
    public function process(Customer $customer, float $amount): bool {
        // 銀行振込の処理（振込確認待ち）
        return true;
    }
}

// 支払い処理のファクトリー
class PaymentProcessorFactory {
    public static function create(string $paymentType): PaymentProcessor {
        return match($paymentType) {
            'CREDIT_CARD' => new CreditCardPaymentProcessor(),
            'BANK_TRANSFER' => new BankTransferPaymentProcessor(),
            default => throw new UnsupportedPaymentTypeException("未対応の支払い方法です: {$paymentType}")
        };
    }
}

// --- 責務が混在しているクラス ---
class OrderProcessor
{
    private array $productInventory = [];
    private array $orderLog          = [];
    private OrderValidator $validator;

    public function __construct()
    {
        $this->validator = new OrderValidator();
        // 初期在庫ダミーデータ
        $this->productInventory = [
            'ITEM001' => 50,
            'ITEM002' => 30,
            'ITEM003' => 0,   // 在庫切れ
        ];
    }

    public function inventoryCheck(array $items): bool
    {
        foreach ($items as $item) {
            if ($item->product === null || empty($item->product->id) || $item->quantity <= 0) {
                $this->log("エラー: 不正な商品情報が含まれています。");
                return false;
            }
            $stock = $this->productInventory[$item->product->id] ?? 0;
            if ($stock < $item->quantity) {
                $this->log("エラー: 在庫不足 - 商品ID={$item->product->id}, 要求={$item->quantity}, 在庫={$stock}");
                return false;
            }
        }
        return true;
    }

    public function calculateTotalAmount(array $items): float
    {
        return array_reduce($items, function($total, $item) {
            return $total + ($item->product->price * $item->quantity);
        }, 0.0);
    }

    /**
     * 決済処理を実行する
     * 
     * @param Customer $customer 顧客情報
     * @param float $amount 決済金額
     * @param string $paymentType 支払い方法
     * @throws PaymentProcessingException 決済処理に失敗した場合
     * @throws UnsupportedPaymentTypeException 未対応の支払い方法の場合
     */
    private function processPayment(Customer $customer, float $amount, string $paymentType): void {
        $this->log("決済処理開始: 方法={$paymentType}, 金額={$amount}");
        
        try {
            $processor = PaymentProcessorFactory::create($paymentType);
            $success = $processor->process($customer, $amount);
            
            if (!$success) {
                throw new PaymentProcessingException("決済処理に失敗しました");
            }
            
            $this->log("決済処理成功");
        } catch (UnsupportedPaymentTypeException $e) {
            $this->log("エラー: " . $e->getMessage());
            throw $e;
        } catch (PaymentProcessingException $e) {
            $this->log("エラー: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 注文を処理する（在庫確認・価格計算・決済・更新・通知・ロギング）
     * @param Customer|null $customer
     * @param OrderItem[]   $items
     * @param string|null   $paymentType
     * @return string 注文IDまたはエラーメッセージ
     */
    public function processNewOrder(Customer $customer, array $items, string $paymentType): string
    {
        $this->log("注文処理開始: 顧客ID=" . $customer->id);

        try {
            // 1. 入力検証
            $this->validator->validateOrder($customer, $items, $paymentType);
            $this->log('入力検証OK');

            // 2. 在庫確認
            if (!$this->inventoryCheck($items)) {
                return 'ERROR: Insufficient stock.';
            }

            // 3. 金額計算
            $totalAmount = $this->calculateTotalAmount($items);
            $this->log("在庫確認 OK. 合計金額={$totalAmount}");

            // 4. 決済処理
            try {
                $this->processPayment($customer, $totalAmount, $paymentType);
            } catch (PaymentException $e) {
                return 'ERROR: ' . $e->getMessage();
            }

            // 5. 在庫更新
            foreach ($items as $item) {
                $this->productInventory[$item->product->id] -= $item->quantity;
            }
            $this->log("在庫更新完了");

            // 6. 注文ID生成 & ログ
            $orderId = 'ORD' . time();
            $this->log("注文確定: 注文ID={$orderId}");

            // 7. 通知メール
            $subject = "ご注文ありがとうございます (注文ID: {$orderId})";
            $body = "{$customer->name}様\n\nご注文ありがとうございます。\n合計金額: {$totalAmount}円\n支払い方法: {$paymentType}\n";
            $this->sendEmail($customer->email, $subject, $body);
            $this->log("通知メール送信完了: To={$customer->email}");

            $this->log("注文処理正常終了: 注文ID={$orderId}");
            return $orderId;

        } catch (OrderValidationException $e) {
            return 'ERROR: ' . $e->getMessage();
        }
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
