<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use Perspective\LoyaltyEngine\Api\LoyaltyManagementInterface;
use Perspective\LoyaltyEngine\Observer\OrderPlaceRedemptionObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for OrderPlaceRedemptionObserver.
 */
class OrderPlaceRedemptionObserverTest extends TestCase
{
    /**
     * @var LoyaltyManagementInterface|MockObject
     */
    private LoyaltyManagementInterface|MockObject $loyaltyManagementMock;

    /**
     * @var OrderPlaceRedemptionObserver
     */
    private OrderPlaceRedemptionObserver $observer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->loyaltyManagementMock = $this->createMock(LoyaltyManagementInterface::class);
        $this->observer = new OrderPlaceRedemptionObserver($this->loyaltyManagementMock);
    }

    public function testExecuteDelegatesToLoyaltyManagement(): void
    {
        $orderMock = $this->createMock(Order::class);
        $eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOrder'])
            ->getMock();

        $eventMock->expects($this->once())->method('getOrder')->willReturn($orderMock);

        $observerMock = $this->createMock(Observer::class);
        $observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);

        $this->loyaltyManagementMock->expects($this->once())
            ->method('recordOrderRedemption')
            ->with($orderMock);

        $this->observer->execute($observerMock);
    }
}
