<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;

/**
 * Resolver for cart maximum redemption percentage from store configuration.
 */
class CartMaxRedemptionPercentage implements ResolverInterface
{
    /**
     * @param LoyaltyCalculationServiceInterface $calculationService
     */
    public function __construct(
        private readonly LoyaltyCalculationServiceInterface $calculationService
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): float {
        return $this->calculationService->getMaxRedemptionPercentage();
    }
}
