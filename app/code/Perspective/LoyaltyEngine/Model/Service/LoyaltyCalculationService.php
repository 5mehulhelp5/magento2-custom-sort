<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;

/**
 * Service for loyalty calculation and conversion rules.
 */
class LoyaltyCalculationService implements LoyaltyCalculationServiceInterface
{
    private const XML_PATH_ACCRUAL_PERCENTAGE = 'loyalty_engine/general/accrual_percentage';
    private const XML_PATH_MAX_REDEMPTION_PERCENTAGE = 'loyalty_engine/general/max_redemption_percentage';
    private const XML_PATH_POINTS_RATE = 'loyalty_engine/general/points_rate';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function calculateCashbackPoints(float $baseSubtotal): float
    {
        if ($baseSubtotal <= 0.0) {
            return 0.0;
        }

        $percentageConfig = $this->scopeConfig->getValue(
            self::XML_PATH_ACCRUAL_PERCENTAGE,
            ScopeInterface::SCOPE_STORE
        );

        $rate = $percentageConfig !== null && $percentageConfig !== ''
            ? ((float)$percentageConfig) / 100.0
            : self::DEFAULT_ACCRUAL_PERCENTAGE;

        return round($baseSubtotal * $rate, 4);
    }

    /**
     * @inheritdoc
     */
    public function calculateMaxRedeemablePoints(float $baseSubtotal, float $customerBalance): float
    {
        if ($baseSubtotal <= 0.0 || $customerBalance <= 0.0) {
            return 0.0;
        }

        $maxPercentageConfig = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_REDEMPTION_PERCENTAGE,
            ScopeInterface::SCOPE_STORE
        );

        $maxPercentage = $maxPercentageConfig !== null && $maxPercentageConfig !== ''
            ? ((float)$maxPercentageConfig) / 100.0
            : self::DEFAULT_MAX_REDEMPTION_PERCENTAGE;

        $maxDiscount = $baseSubtotal * $maxPercentage;
        $maxPointsForDiscount = $this->convertCurrencyToPoints($maxDiscount);

        return min($customerBalance, $maxPointsForDiscount);
    }

    /**
     * @inheritdoc
     */
    public function convertPointsToCurrency(float $points): float
    {
        $rateConfig = $this->scopeConfig->getValue(
            self::XML_PATH_POINTS_RATE,
            ScopeInterface::SCOPE_STORE
        );

        $rate = $rateConfig !== null && $rateConfig !== ''
            ? (float)$rateConfig
            : self::DEFAULT_POINTS_PER_CURRENCY_UNIT;

        return round($points * $rate, 4);
    }

    /**
     * @inheritdoc
     */
    public function convertCurrencyToPoints(float $amount): float
    {
        $rateConfig = $this->scopeConfig->getValue(
            self::XML_PATH_POINTS_RATE,
            ScopeInterface::SCOPE_STORE
        );

        $rate = $rateConfig !== null && $rateConfig !== '' && (float)$rateConfig > 0.0
            ? (float)$rateConfig
            : self::DEFAULT_POINTS_PER_CURRENCY_UNIT;

        return round($amount / $rate, 4);
    }

    /**
     * @inheritdoc
     */
    public function getAccrualPercentage(): float
    {
        $percentageConfig = $this->scopeConfig->getValue(
            self::XML_PATH_ACCRUAL_PERCENTAGE,
            ScopeInterface::SCOPE_STORE
        );

        return $percentageConfig !== null && $percentageConfig !== ''
            ? (float)$percentageConfig
            : self::DEFAULT_ACCRUAL_PERCENTAGE * 100.0;
    }

    /**
     * @inheritdoc
     */
    public function getMaxRedemptionPercentage(): float
    {
        $maxPercentageConfig = $this->scopeConfig->getValue(
            self::XML_PATH_MAX_REDEMPTION_PERCENTAGE,
            ScopeInterface::SCOPE_STORE
        );

        return $maxPercentageConfig !== null && $maxPercentageConfig !== ''
            ? (float)$maxPercentageConfig
            : self::DEFAULT_MAX_REDEMPTION_PERCENTAGE * 100.0;
    }
}
