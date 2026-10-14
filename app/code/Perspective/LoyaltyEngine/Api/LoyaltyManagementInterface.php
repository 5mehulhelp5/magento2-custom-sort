<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Service contract for applying and removing loyalty points in cart.
 *
 * @api
 */
interface LoyaltyManagementInterface
{
    /**
     * Apply loyalty points to quote.
     *
     * @param int $cartId
     * @param float $points
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function applyPoints(int $cartId, float $points): bool;

    /**
     * Remove loyalty points from quote.
     *
     * @param int $cartId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function removePoints(int $cartId): bool;

    /**
     * Record loyalty points redemption transaction when order is placed.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function recordOrderRedemption(\Magento\Sales\Api\Data\OrderInterface $order): void;
}
