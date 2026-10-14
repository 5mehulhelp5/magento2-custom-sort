<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api;

/**
 * Service contract for loyalty calculation and conversion rules.
 *
 * @api
 */
interface LoyaltyCalculationServiceInterface
{
    public const DEFAULT_ACCRUAL_PERCENTAGE = 0.05;
    public const DEFAULT_MAX_REDEMPTION_PERCENTAGE = 0.50;
    public const DEFAULT_POINTS_PER_CURRENCY_UNIT = 1.0;

    /**
     * Calculate 5% cashback points based on order base subtotal.
     *
     * @param float $baseSubtotal
     * @return float
     */
    public function calculateCashbackPoints(float $baseSubtotal): float;

    /**
     * Calculate maximum points that can be redeemed for quote base subtotal.
     *
     * @param float $baseSubtotal
     * @param float $customerBalance
     * @return float
     */
    public function calculateMaxRedeemablePoints(float $baseSubtotal, float $customerBalance): float;

    /**
     * Convert points amount to base currency discount amount.
     *
     * @param float $points
     * @return float
     */
    public function convertPointsToCurrency(float $points): float;

    /**
     * Convert currency discount amount to points.
     *
     * @param float $amount
     * @return float
     */
    public function convertCurrencyToPoints(float $amount): float;

    /**
     * Retrieve configured cashback accrual percentage (e.g. 5.0).
     *
     * @return float
     */
    public function getAccrualPercentage(): float;

    /**
     * Retrieve configured maximum cart redemption percentage (e.g. 50.0).
     *
     * @return float
     */
    public function getMaxRedemptionPercentage(): float;
}
