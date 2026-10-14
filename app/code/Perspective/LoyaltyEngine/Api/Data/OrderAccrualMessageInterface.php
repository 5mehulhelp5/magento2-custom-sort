<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api\Data;

/**
 * Message schema interface for RabbitMQ points accrual queue.
 *
 * @api
 */
interface OrderAccrualMessageInterface
{
    public const ORDER_ID = 'order_id';
    public const CUSTOMER_ID = 'customer_id';

    /**
     * Get order ID.
     *
     * @return int
     */
    public function getOrderId(): int;

    /**
     * Set order ID.
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId): self;

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
}
