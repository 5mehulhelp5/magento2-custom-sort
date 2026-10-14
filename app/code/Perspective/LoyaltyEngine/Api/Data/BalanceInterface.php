<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface BalanceInterface representing customer active points balance.
 *
 * @api
 */
interface BalanceInterface extends ExtensibleDataInterface
{
    public const CUSTOMER_ID = 'customer_id';
    public const TOTAL_ACTIVE_POINTS = 'total_active_points';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get total active points.
     *
     * @return float
     */
    public function getTotalActivePoints(): float;

    /**
     * Set total active points.
     *
     * @param float $totalActivePoints
     * @return $this
     */
    public function setTotalActivePoints(float $totalActivePoints): self;

    /**
     * Get updated at date.
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated at date.
     *
     * @param string|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?string $updatedAt): self;
}
