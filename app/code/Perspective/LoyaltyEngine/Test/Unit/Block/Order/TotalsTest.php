<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Block\Order;

use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Totals as ParentTotalsBlock;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Perspective\LoyaltyEngine\Block\Order\Totals;

class TotalsTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private Context|MockObject $contextMock;

    /**
     * @var Totals
     */
    private Totals $block;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->block = new Totals($this->contextMock);
    }

    public function testInitTotalsAddsTotalWhenDiscountExists(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getData')
            ->willReturnMap([
                ['loyalty_discount_amount', null, 20.0],
                ['base_loyalty_discount_amount', null, 20.0]
            ]);

        $parentBlockMock = $this->createMock(ParentTotalsBlock::class);
        $parentBlockMock->method('getSource')->willReturn($orderMock);

        $parentBlockMock->expects($this->once())
            ->method('addTotalBefore')
            ->with(
                $this->callback(function (DataObject $total) {
                    return $total->getData('code') === 'loyalty_points'
                        && $total->getData('value') === -20.0
                        && $total->getData('base_value') === -20.0;
                }),
                'grand_total'
            );

        $this->block->setParentBlock($parentBlockMock);
        $result = $this->block->initTotals();

        $this->assertSame($this->block, $result);
    }

    public function testInitTotalsDoesNothingWhenZeroDiscount(): void
    {
        $orderMock = $this->createMock(Order::class);
        $orderMock->method('getData')
            ->with('loyalty_discount_amount')
            ->willReturn(0.0);

        $parentBlockMock = $this->createMock(ParentTotalsBlock::class);
        $parentBlockMock->method('getSource')->willReturn($orderMock);
        $parentBlockMock->expects($this->never())->method('addTotalBefore');

        $this->block->setParentBlock($parentBlockMock);
        $result = $this->block->initTotals();

        $this->assertSame($this->block, $result);
    }
}
