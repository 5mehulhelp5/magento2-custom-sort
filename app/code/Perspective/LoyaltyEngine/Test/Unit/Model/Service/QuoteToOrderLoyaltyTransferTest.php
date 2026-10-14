<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Model\Service;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Perspective\LoyaltyEngine\Model\Service\QuoteToOrderLoyaltyTransfer;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for QuoteToOrderLoyaltyTransfer service.
 */
class QuoteToOrderLoyaltyTransferTest extends TestCase
{
    /**
     * @var QuoteToOrderLoyaltyTransfer
     */
    private QuoteToOrderLoyaltyTransfer $service;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->service = new QuoteToOrderLoyaltyTransfer();
    }

    public function testExecuteCopiesLoyaltyDataWhenPointsApplied(): void
    {
        $quoteMock = $this->createMock(Quote::class);
        $orderMock = $this->createMock(Order::class);

        $quoteMock->expects($this->exactly(3))
            ->method('getData')
            ->willReturnMap([
                ['loyalty_points_applied', null, 25.0],
                ['loyalty_discount_amount', null, 25.0],
                ['base_loyalty_discount_amount', null, 25.0]
            ]);

        $orderMock->expects($this->exactly(3))
            ->method('setData')
            ->willReturnMap([
                ['loyalty_points_applied', 25.0, $orderMock],
                ['loyalty_discount_amount', 25.0, $orderMock],
                ['base_loyalty_discount_amount', 25.0, $orderMock]
            ]);

        $this->service->execute($quoteMock, $orderMock);
    }

    public function testExecuteDoesNothingWhenNoPointsApplied(): void
    {
        $quoteMock = $this->createMock(Quote::class);
        $orderMock = $this->createMock(Order::class);

        $quoteMock->expects($this->once())
            ->method('getData')
            ->with('loyalty_points_applied')
            ->willReturn(0.0);

        $orderMock->expects($this->never())->method('setData');

        $this->service->execute($quoteMock, $orderMock);
    }
}
