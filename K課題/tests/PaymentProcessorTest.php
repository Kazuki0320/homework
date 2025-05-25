<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../fix_processNewOrder.php';

class PaymentProcessorTest extends TestCase
{
    private Customer $testCustomer;
    
    protected function setUp(): void
    {
        $this->testCustomer = new Customer('TEST001', 'テストユーザー', 'test@example.com');
        // 各テストの前にテストプロセッサをクリア
        PaymentProcessorFactory::clearTestProcessors();
    }

    /**
     * PaymentProcessorFactoryのテスト - クレジットカード決済
     */
    public function testFactoryCreateCreditCard(): void
    {
        $processor = PaymentProcessorFactory::create('CREDIT_CARD');
        $this->assertInstanceOf(CreditCardPaymentProcessor::class, $processor);
        
        // 生成されたプロセッサが正しく機能することを確認
        $result = $processor->process($this->testCustomer, 10000);
        $this->assertTrue($result);
    }

    /**
     * PaymentProcessorFactoryのテスト - 銀行振込
     */
    public function testFactoryCreateBankTransfer(): void
    {
        $processor = PaymentProcessorFactory::create('BANK_TRANSFER');
        $this->assertInstanceOf(BankTransferPaymentProcessor::class, $processor);
        
        // 生成されたプロセッサが正しく機能することを確認
        $result = $processor->process($this->testCustomer, 10000);
        $this->assertTrue($result);
    }

    /**
     * PaymentProcessorFactoryのテスト - 未対応の支払い方法
     */
    public function testFactoryCreateUnsupportedType(): void
    {
        $this->expectException(UnsupportedPaymentTypeException::class);
        $this->expectExceptionMessage('未対応の支払い方法です: CASH');
        PaymentProcessorFactory::create('CASH');
    }

    /**
     * PaymentProcessorFactoryのテスト - 空の支払い方法
     */
    public function testFactoryCreateEmptyType(): void
    {
        $this->expectException(UnsupportedPaymentTypeException::class);
        $this->expectExceptionMessage('未対応の支払い方法です: ');
        PaymentProcessorFactory::create('');
    }

    /**
     * モックを使用したクレジットカード決済のテスト
     */
    public function testCreditCardPaymentWithMock(): void
    {
        // クレジットカード決済プロセッサのモックを作成
        $mockProcessor = $this->createMock(CreditCardPaymentProcessor::class);
        
        // process()メソッドが呼ばれた時の振る舞いを設定
        $mockProcessor->expects($this->once())
            ->method('process')
            ->with(
                $this->equalTo($this->testCustomer),
                $this->equalTo(10000.0)
            )
            ->willReturn(true);

        // モックを使用してテスト
        $result = $mockProcessor->process($this->testCustomer, 10000.0);
        $this->assertTrue($result);
    }

    /**
     * モックを使用した決済成功のテスト
     * このテストでは、実際のクレジットカードAPIを呼び出すことなく、
     * 決済処理の成功パターンをテストできます
     */
    public function testPaymentSuccessWithMock(): void
    {
        // モックプロセッサを作成
        /** @var PaymentProcessor $mockProcessor */
        $mockProcessor = $this->createMock(PaymentProcessor::class);
        $mockProcessor->expects($this->once())  // processメソッドが1回呼ばれることを期待
            ->method('process')
            ->with(
                $this->equalTo($this->testCustomer),
                $this->equalTo(10000.0)
            )
            ->willReturn(true);  // 決済成功を模擬

        // モックをファクトリーに注入
        PaymentProcessorFactory::setTestProcessor('CREDIT_CARD', $mockProcessor);

        // テスト実行
        $processor = PaymentProcessorFactory::create('CREDIT_CARD');
        $result = $processor->process($this->testCustomer, 10000.0);
        
        $this->assertTrue($result);
    }

    /**
     * モックを使用した決済失敗のテスト
     * このテストでは、実際のクレジットカードAPIを呼び出すことなく、
     * 決済処理の失敗パターンをテストできます
     */
    public function testPaymentFailureWithMock(): void
    {
        // モックプロセッサを作成（決済失敗を模擬）
        /** @var PaymentProcessor $mockProcessor */
        $mockProcessor = $this->createMock(PaymentProcessor::class);
        $mockProcessor->expects($this->once())
            ->method('process')
            ->willReturn(false);  // 決済失敗を模擬

        // モックをファクトリーに注入
        PaymentProcessorFactory::setTestProcessor('CREDIT_CARD', $mockProcessor);

        // テスト実行
        $processor = PaymentProcessorFactory::create('CREDIT_CARD');
        $result = $processor->process($this->testCustomer, 10000.0);
        
        $this->assertFalse($result);
    }

    /**
     * 複数の支払い方法でのモックテスト
     * 異なる支払い方法に対して異なるモックを注入できることを示します
     */
    public function testMultiplePaymentTypesWithMocks(): void
    {
        // クレジットカード決済のモック（成功）
        /** @var PaymentProcessor $mockCreditCard */
        $mockCreditCard = $this->createMock(PaymentProcessor::class);
        $mockCreditCard->method('process')->willReturn(true);
        PaymentProcessorFactory::setTestProcessor('CREDIT_CARD', $mockCreditCard);

        // 銀行振込のモック（失敗）
        /** @var PaymentProcessor $mockBankTransfer */
        $mockBankTransfer = $this->createMock(PaymentProcessor::class);
        $mockBankTransfer->method('process')->willReturn(false);
        PaymentProcessorFactory::setTestProcessor('BANK_TRANSFER', $mockBankTransfer);

        // テスト実行
        $creditCardProcessor = PaymentProcessorFactory::create('CREDIT_CARD');
        $bankTransferProcessor = PaymentProcessorFactory::create('BANK_TRANSFER');

        $this->assertTrue($creditCardProcessor->process($this->testCustomer, 10000.0));
        $this->assertFalse($bankTransferProcessor->process($this->testCustomer, 10000.0));
    }
} 