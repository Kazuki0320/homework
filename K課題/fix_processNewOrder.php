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
    private static array $testProcessors = [];

    /**
     * テスト用のプロセッサを設定
     */
    public static function setTestProcessor(string $paymentType, PaymentProcessor $processor): void {
        self::$testProcessors[$paymentType] = $processor;
    }

    /**
     * テスト用のプロセッサをクリア
     */
    public static function clearTestProcessors(): void {
        self::$testProcessors = [];
    }

    public static function create(string $paymentType): PaymentProcessor {
        // テスト用のプロセッサが設定されている場合はそれを返す
        if (isset(self::$testProcessors[$paymentType])) {
            return self::$testProcessors[$paymentType];
        }

        return match($paymentType) {
            'CREDIT_CARD' => new CreditCardPaymentProcessor(),
            'BANK_TRANSFER' => new BankTransferPaymentProcessor(),
            default => throw new UnsupportedPaymentTypeException("未対応の支払い方法です: {$paymentType}")
        };
    }
}

// 在庫管理サービス
class InventoryService {
    private array $productInventory;

    public function __construct(array $initialInventory = []) {
        $this->productInventory = $initialInventory;
    }

    public function checkStock(array $items): bool {
        foreach ($items as $item) {
            if ($item->product === null || empty($item->product->id) || $item->quantity <= 0) {
                throw new InventoryException("不正な商品情報が含まれています");
            }
            $stock = $this->productInventory[$item->product->id] ?? 0;
            if ($stock < $item->quantity) {
                throw new InventoryException("在庫不足 - 商品ID={$item->product->id}, 要求={$item->quantity}, 在庫={$stock}");
            }
        }
        return true;
    }

    public function updateStock(array $items): void {
        foreach ($items as $item) {
            $this->productInventory[$item->product->id] -= $item->quantity;
        }
    }
}

// 注文IDジェネレーターサービス
class OrderIdGenerator {
    public function generate(): string {
        return 'ORD' . time();
    }
}

// メール通知サービス
class NotificationService {
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger) {
        $this->logger = $logger;
    }

    public function sendOrderConfirmation(
        Customer $customer,
        string $orderId,
        float $totalAmount,
        string $paymentType
    ): void {
        $subject = "ご注文ありがとうございます (注文ID: {$orderId})";
        $body = "{$customer->name}様\n\nご注文ありがとうございます。\n合計金額: {$totalAmount}円\n支払い方法: {$paymentType}\n";
        
        // 実際のメール送信処理（実装は省略）
        $this->logger->log("[Internal] メール送信 (To={$customer->email}, Subject={$subject})");
    }
}

// ロガーインターフェース
interface LoggerInterface {
    public function log(string $message): void;
}

// 標準出力ロガー
class ConsoleLogger implements LoggerInterface {
    public function log(string $message): void {
        $timestamp = (new DateTime())->format(DateTime::ATOM);
        echo "[{$timestamp}] {$message}\n";
    }
}

// 注文処理サービス
class OrderService {
    private OrderValidator $validator;
    private InventoryService $inventoryService;
    private PaymentProcessorFactory $paymentFactory;
    private OrderIdGenerator $orderIdGenerator;
    private NotificationService $notificationService;
    private LoggerInterface $logger;

    public function __construct(
        OrderValidator $validator,
        InventoryService $inventoryService,
        PaymentProcessorFactory $paymentFactory,
        OrderIdGenerator $orderIdGenerator,
        NotificationService $notificationService,
        LoggerInterface $logger
    ) {
        $this->validator = $validator;
        $this->inventoryService = $inventoryService;
        $this->paymentFactory = $paymentFactory;
        $this->orderIdGenerator = $orderIdGenerator;
        $this->notificationService = $notificationService;
        $this->logger = $logger;
    }

    public function processNewOrder(Customer $customer, array $items, string $paymentType): string {
        $this->logger->log("注文処理開始: 顧客ID=" . $customer->id);

        try {
            // 1. 入力検証
            $this->validator->validateOrder($customer, $items, $paymentType);
            $this->logger->log('入力検証OK');

            // 2. 在庫確認
            $this->inventoryService->checkStock($items);
            
            // 3. 金額計算
            $totalAmount = $this->calculateTotalAmount($items);
            $this->logger->log("在庫確認 OK. 合計金額={$totalAmount}");

            // 4. 決済処理
            $processor = $this->paymentFactory->create($paymentType);
            if (!$processor->process($customer, $totalAmount)) {
                throw new PaymentProcessingException("決済処理に失敗しました");
            }

            // 5. 在庫更新
            $this->inventoryService->updateStock($items);
            $this->logger->log("在庫更新完了");

            // 6. 注文ID生成
            $orderId = $this->orderIdGenerator->generate();
            $this->logger->log("注文確定: 注文ID={$orderId}");

            // 7. 通知メール送信
            $this->notificationService->sendOrderConfirmation(
                $customer,
                $orderId,
                $totalAmount,
                $paymentType
            );

            $this->logger->log("注文処理正常終了: 注文ID={$orderId}");
            return $orderId;

        } catch (OrderValidationException $e) {
            $this->logger->log("バリデーションエラー: " . $e->getMessage());
            throw $e;
        } catch (InventoryException $e) {
            $this->logger->log("在庫エラー: " . $e->getMessage());
            throw $e;
        } catch (PaymentException $e) {
            $this->logger->log("決済エラー: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateTotalAmount(array $items): float {
        return array_reduce($items, function($total, $item) {
            return $total + ($item->product->price * $item->quantity);
        }, 0.0);
    }
}

// 例外クラス
class InventoryException extends Exception {}

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
