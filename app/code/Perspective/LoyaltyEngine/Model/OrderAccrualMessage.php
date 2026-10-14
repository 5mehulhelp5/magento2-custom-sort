<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model;

use Perspective\LoyaltyEngine\Api\Data\OrderAccrualMessageInterface;

/**
 * Message data object for RabbitMQ accrual queue.
 */
class OrderAccrualMessage implements OrderAccrualMessageInterface
{
    /**
     * @var int
     */
    private int $orderId = 0;

    /**
     * @var int
     */
    private int $customerId = 0;

    /**
     * @inheritdoc
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @inheritdoc
     */
    public function setOrderId(int $orderId): self
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $customerId): self
    {
        $this->customerId = $customerId;
        return $this;
    }
}
