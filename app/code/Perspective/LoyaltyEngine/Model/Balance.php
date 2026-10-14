<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model;

use Magento\Framework\Model\AbstractModel;
use Perspective\LoyaltyEngine\Api\Data\BalanceInterface;
use Perspective\LoyaltyEngine\Model\ResourceModel\Balance as BalanceResource;

/**
 * Model representing active loyalty balance entity.
 */
class Balance extends AbstractModel implements BalanceInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(BalanceResource::class);
        $this->_idFieldName = self::CUSTOMER_ID;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return (int)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): self
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritdoc
     */
    public function getTotalActivePoints(): float
    {
        return (float)$this->getData(self::TOTAL_ACTIVE_POINTS);
    }

    /**
     * @inheritdoc
     */
    public function setTotalActivePoints(float $totalActivePoints): self
    {
        return $this->setData(self::TOTAL_ACTIVE_POINTS, $totalActivePoints);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): ?string
    {
        $updatedAt = $this->getData(self::UPDATED_AT);
        return $updatedAt !== null ? (string)$updatedAt : null;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(?string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
