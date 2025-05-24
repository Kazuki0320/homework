<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../fix_processNewOrder.php';

class PaymentProcessorTest extends TestCase
{
    private Customer $testCustomer;
    
    protected function setUp(): void
    {
        $this->testCustomer = new Customer('TEST001', 'テストユーザー', 'test@example.com');
    }

    /**
     * クレジットカード決済の基本テスト
     */
    public function testCreditCardPayment(): void
    {
        $processor = new CreditCardPaymentProcessor();
        $result = $processor->process($this->testCustomer, 10000);
        $this->assertTrue($result);
    }
} 