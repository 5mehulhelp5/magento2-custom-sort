<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Perspective\LoyaltyEngine\Api\BalanceRepositoryInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;

/**
 * Resolver for calculating max redeemable loyalty points for a cart.
 */
class CartMaxRedeemablePoints implements ResolverInterface
{
    /**
     * @param BalanceRepositoryInterface $balanceRepository
     * @param LoyaltyCalculationServiceInterface $calculationService
     */
    public function __construct(
        private readonly BalanceRepositoryInterface $balanceRepository,
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
        if (!isset($value['model']) || !($value['model'] instanceof Quote)) {
            return 0.0;
        }

        /** @var Quote $cart */
        $cart = $value['model'];
        $customerId = (int)$cart->getCustomerId();

        if ($customerId <= 0) {
            return 0.0;
        }

        $balance = $this->balanceRepository->getByCustomerId($customerId)->getTotalActivePoints();
        $baseSubtotal = (float)$cart->getBaseSubtotal();

        return $this->calculationService->calculateMaxRedeemablePoints($baseSubtotal, $balance);
    }
}
