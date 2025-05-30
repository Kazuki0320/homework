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

class OrderValidatorException extends Exception {}
class InventoryException extends Exception {}

// 1. 入力検証
class OrderValidator
{
	public function validateOrder(Customer $customer, array $items, string $paymentType): void
	{
		$this->validateCustomer($customer);
		$this->validateItems($items);
		$this->validatePaymentType($paymentType);
	}

	private function validateCustomer(Customer $customer)
	{
		if (empty($customer->id) || empty($customer->email)) {
			$this->log("エラー: 顧客情報が無効です。");
			throw new OrderValidatorException('ERROR: Invalid customer data.');
		}
	}

	private function validateItems(array $items)
	{
		if (empty($items)) {
			$this->log("エラー: 注文商品が空です。");
			throw new OrderValidatorException('ERROR: Order items cannot be empty.');
		}
	}

	private function validatePaymentType(string $paymentType)
	{
		if (empty($paymentType)) {
			$this->log("エラー: 支払い方法が指定されていません。");
			throw new OrderValidatorException('ERROR: Payment type is required.');
		}
	}
}

// 在庫管理サービス
class InventoryService
{
	private array $productInventory;
	
	public function __construct(array $initialInventory)
	{
		$this->productInventory = $initialInventory;
	}

	public function checkStock(array $items): void
	{
		foreach ($items as $item) {
				if ($item->product === null || empty($item->product->id) || $item->quantity <= 0) {
						$this->log("エラー: 不正な商品情報が含まれています。");
						throw new InventoryException('ERROR: Invalid product data in order items.');
				}
				$stock = $this->productInventory[$item->product->id] ?? 0;
				if ($stock < $item->quantity) {
						$this->log("エラー: 在庫不足 - 商品ID={$item->product->id}, 要求={$item->quantity}, 在庫={$stock}");
						throw new InventoryException("ERROR: Insufficient stock for product {$item->product->id}");
				}
		}
	}

	public function updateStock(array $items): void
	{
		foreach ($items as $item) {
			$this->productInventory[$item->product->id] -= $item->quantity;
		}
	}
}

// 合計金額
class AmountCalculator
{
	public function calculateTotalAmount(array $items): float
	{
		return array_reduce($items, function($total, $item) {
			return $total + ($item->product->price * $item->quantity);
		}, 0.0);
	}
}

// 支払い処理のインターフェース
interface PaymentProcessor {
    public function process(Customer $customer, float $amount): bool;
}

/**
 * クレジットカード決済の実装
 */
class CreditCardPaymentProcessor implements PaymentProcessor {
    public function process(Customer $customer, float $amount): bool {
        try {
            return $this->callCreditCardApi($customer->id, $amount);
        } catch (Exception $e) {
            throw new PaymentProcessingException('クレジットカード決済に失敗しました', 0, $e);
        }
    }

    private function callCreditCardApi(string $customerId, float $amount): bool {
        // クレジットカードAPIの呼び出し実装
        return true;
    }
}

/**
 * 銀行振込決済の実装
 */
class BankTransferPaymentProcessor implements PaymentProcessor {
    public function process(Customer $customer, float $amount): bool {
        try {
            // 銀行振込の処理実装
            return true;
        } catch (Exception $e) {
            throw new PaymentProcessingException('銀行振込処理に失敗しました', 0, $e);
        }
    }
}

/**
 * 支払い処理の例外クラス
 */
class PaymentException extends Exception {}
class UnsupportedPaymentTypeException extends PaymentException {}
class PaymentProcessingException extends PaymentException {}

/**
 * 支払い処理のファクトリークラス
 */
class PaymentProcessorFactory {
    /**
     * 支払い処理のインスタンスを生成
     * 
     * @param string $paymentType 支払い方法
     * @return PaymentProcessor
     * @throws UnsupportedPaymentTypeException
     */
    public static function create(string $paymentType): PaymentProcessor {
        return match($paymentType) {
            'CREDIT_CARD' => new CreditCardPaymentProcessor(),
            'BANK_TRANSFER' => new BankTransferPaymentProcessor(),
            default => throw new UnsupportedPaymentTypeException("未対応の支払い方法です: {$paymentType}")
        };
    }
}

// 注文IDジェネレーターサービス
class OrderIdGenerator {
    public function generate(): string {
        return 'ORD' . time();
    }
}

class NotificationService
{
	public function sendOrderConfirmation(
		Customer $customer,
		string $orderId,
		float $totalAmount,
		string $paymentType
	): void {
        $subject = "ご注文ありがとうございます (注文ID: {$orderId})";
        $body    = "{$customer->name}様\n\nご注文ありがとうございます。\n合計金額: {$totalAmount}円\n支払い方法: {$paymentType}\n";
        $this->sendEmail($customer->email, $subject, $body);
	}
}

class LoggerInterface
{
	public function log(string $msg): void
	{
		$timestamp = (new DateTime())->format(DateTime::ATOM);
		echo "[{$timestamp}] {$msg}\n";
	}
}

// --- 責務が混在しているクラス ---
class OrderService
{
	private OrderValidator $validator;
	private InventoryService $inventory;
	private AmountCalculator $amountCalculator;
	private PaymentProcessorFactory $paymentProcessorFactory;
	private OrderIdGenerator $orderIdGenerator;
	private NotificationService $notificationService;
	private LoggerInterface $logger;

	public function __construct(
		OrderValidator $validator,
		InventoryService $inventory,
		AmountCalculator $amountCalculator,
		PaymentProcessorFactory $paymentProcessorFactory,
		OrderIdGenerator $orderIdGenerator,
		NotificationService $notificationService,
		LoggerInterface $logger
	) {
		$this->validator = $validator;
		$this->inventory = $inventory;
		$this->amountCalculator = $amountCalculator;
		$this->paymentProcessorFactory = $paymentProcessorFactory;
		$this->orderIdGenerator = $orderIdGenerator;
		$this->notificationService = $notificationService;
		$this->logger = $logger;
	}

	public function processNewOrder(Customer $customer, array $items, string $paymentType): string
	{
		$this->logger->log("注文処理開始: 顧客ID=" . ($customer->id ?? 'null'));

		try {
			$this->validator->validateOrder($customer, $items, $paymentType);
			$this->logger->log("入力検証 OK.");

			$this->inventory->checkStock($items);
			$this->logger->log("在庫確認 OK.");

			$totalAmount = $this->amountCalculator->calculateTotalAmount($items);
			$this->logger->log("合計金額={$totalAmount}");

			$processor = $this->paymentProcessorFactory->create($paymentType);
			if (!$processor->process($customer, $totalAmount)) {
				throw new PaymentProcessingException("決済処理に失敗しました");
			}
			$this->logger->log("決済処理成功.");

			$this->inventory->updateStock($items);
			$this->logger->log("在庫更新完了.");

			$orderId = $this->orderIdGenerator->generate();
			$this->logger->log("注文確定: 注文ID={$orderId}");

			$this->notificationService->sendOrderConfirmation($customer, $orderId, $totalAmount, $paymentType);
			$this->logger->log("通知メール送信完了: To={$customer->email}");

			$this->logger->log("注文処理正常終了: 注文ID={$orderId}");
			return $orderId;

		} catch (OrderValidatorException $e) {
			$this->logger->log("バリデーションエラー: " . $e->getMessage());
			throw $e;
		} catch (InventoryException $e) {
			$this->logger->log("在庫エラー: " . $e->getMessage());
			throw $e;
		} catch (PaymentProcessingException $e) {
			$this->logger->log("決済エラー: " . $e->getMessage());
			throw $e;
		}
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
