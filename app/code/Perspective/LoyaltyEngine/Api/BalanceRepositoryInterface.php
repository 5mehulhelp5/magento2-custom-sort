<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\LoyaltyEngine\Api\Data\BalanceInterface;

/**
 * Service contract for retrieving customer balance from flat table.
 *
 * @api
 */
interface BalanceRepositoryInterface
{
    /**
     * Retrieve active loyalty balance by customer ID.
     *
     * @param int $customerId
     * @return BalanceInterface
     * @throws NoSuchEntityException
     */
    public function getByCustomerId(int $customerId): BalanceInterface;

    /**
     * Save customer balance index record.
     *
     * @param BalanceInterface $balance
     * @return BalanceInterface
     */
    public function save(BalanceInterface $balance): BalanceInterface;
}
