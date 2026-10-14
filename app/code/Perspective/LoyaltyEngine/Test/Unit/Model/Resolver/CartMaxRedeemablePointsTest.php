<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Perspective\LoyaltyEngine\Api\BalanceRepositoryInterface;
use Perspective\LoyaltyEngine\Api\Data\BalanceInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;
use Perspective\LoyaltyEngine\Model\Resolver\CartMaxRedeemablePoints;

/**
 * Unit test for CartMaxRedeemablePoints GraphQL resolver.
 */
class CartMaxRedeemablePointsTest extends TestCase
{
    /**
     * @var BalanceRepositoryInterface|MockObject
     */
    private BalanceRepositoryInterface|MockObject $balanceRepositoryMock;

    /**
     * @var LoyaltyCalculationServiceInterface|MockObject
     */
    private LoyaltyCalculationServiceInterface|MockObject $calculationServiceMock;

    /**
     * @var CartMaxRedeemablePoints
     */
    private CartMaxRedeemablePoints $resolver;

    protected function setUp(): void
    {
        $this->balanceRepositoryMock = $this->createMock(BalanceRepositoryInterface::class);
        $this->calculationServiceMock = $this->createMock(LoyaltyCalculationServiceInterface::class);

        $this->resolver = new CartMaxRedeemablePoints(
            $this->balanceRepositoryMock,
            $this->calculationServiceMock
        );
    }

    public function testResolveReturnsZeroWhenCartModelMissing(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $contextMock = $this->createMock(\Magento\Framework\GraphQl\Query\Resolver\ContextInterface::class);
        $infoMock = $this->createMock(ResolveInfo::class);

        $result = $this->resolver->resolve($fieldMock, $contextMock, $infoMock, []);
        $this->assertEquals(0.0, $result);
    }

    public function testResolveReturnsCalculatedMaxPoints(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $contextMock = $this->createMock(\Magento\Framework\GraphQl\Query\Resolver\ContextInterface::class);
        $infoMock = $this->createMock(ResolveInfo::class);
        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getCustomerId', 'getBaseSubtotal'])
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(2);
        $quoteMock->expects($this->once())
            ->method('getBaseSubtotal')
            ->willReturn(100.0);

        $balanceMock = $this->createMock(BalanceInterface::class);
        $balanceMock->expects($this->once())
            ->method('getTotalActivePoints')
            ->willReturn(60.0);

        $this->balanceRepositoryMock->expects($this->once())
            ->method('getByCustomerId')
            ->with(2)
            ->willReturn($balanceMock);

        $this->calculationServiceMock->expects($this->once())
            ->method('calculateMaxRedeemablePoints')
            ->with(100.0, 60.0)
            ->willReturn(50.0);

        $result = $this->resolver->resolve($fieldMock, $contextMock, $infoMock, ['model' => $quoteMock]);
        $this->assertEquals(50.0, $result);
    }
}
