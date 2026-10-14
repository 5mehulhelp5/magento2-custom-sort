<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Perspective\LoyaltyEngine\Model\Service\QuoteToOrderLoyaltyTransfer;
use Perspective\LoyaltyEngine\Observer\QuoteSubmitBeforeObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteSubmitBeforeObserverTest extends TestCase
{
    /**
     * @var QuoteToOrderLoyaltyTransfer|MockObject
     */
    private QuoteToOrderLoyaltyTransfer|MockObject $transferServiceMock;

    /**
     * @var QuoteSubmitBeforeObserver
     */
    private QuoteSubmitBeforeObserver $observer;

    protected function setUp(): void
    {
        $this->transferServiceMock = $this->createMock(QuoteToOrderLoyaltyTransfer::class);
        $this->observer = new QuoteSubmitBeforeObserver($this->transferServiceMock);
    }

    public function testExecuteDelegatesToTransferService(): void
    {
        $quoteMock = $this->createMock(Quote::class);
        $orderMock = $this->createMock(Order::class);

        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getOrder', 'getQuote'])
            ->getMock();

        $eventMock->method('getOrder')->willReturn($orderMock);
        $eventMock->method('getQuote')->willReturn($quoteMock);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->transferServiceMock->expects($this->once())
            ->method('execute')
            ->with($quoteMock, $orderMock);

        $this->observer->execute($observerMock);
    }

    public function testExecuteSkipsWhenInvalidTypes(): void
    {
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getOrder', 'getQuote'])
            ->getMock();

        $eventMock->method('getOrder')->willReturn(null);
        $eventMock->method('getQuote')->willReturn(null);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getEvent')->willReturn($eventMock);

        $this->transferServiceMock->expects($this->never())->method('execute');

        $this->observer->execute($observerMock);
    }
}
