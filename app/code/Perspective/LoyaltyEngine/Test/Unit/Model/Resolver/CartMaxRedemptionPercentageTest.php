<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;
use Perspective\LoyaltyEngine\Model\Resolver\CartMaxRedemptionPercentage;

class CartMaxRedemptionPercentageTest extends TestCase
{
    /**
     * @var LoyaltyCalculationServiceInterface|MockObject
     */
    private LoyaltyCalculationServiceInterface|MockObject $calculationServiceMock;

    /**
     * @var CartMaxRedemptionPercentage
     */
    private CartMaxRedemptionPercentage $resolver;

    protected function setUp(): void
    {
        $this->calculationServiceMock = $this->createMock(LoyaltyCalculationServiceInterface::class);

        $this->resolver = new CartMaxRedemptionPercentage(
            $this->calculationServiceMock
        );
    }

    public function testResolveReturnsConfiguredMaxPercentage(): void
    {
        $fieldMock = $this->createMock(Field::class);
        $contextMock = $this->createMock(\Magento\Framework\GraphQl\Query\Resolver\ContextInterface::class);
        $resolveInfoMock = $this->createMock(ResolveInfo::class);

        $this->calculationServiceMock->expects($this->once())
            ->method('getMaxRedemptionPercentage')
            ->willReturn(50.0);

        $result = $this->resolver->resolve($fieldMock, $contextMock, $resolveInfoMock);

        $this->assertSame(50.0, $result);
    }
}
