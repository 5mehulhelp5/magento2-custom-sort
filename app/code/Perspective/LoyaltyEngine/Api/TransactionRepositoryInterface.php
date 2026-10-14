<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionSearchResultsInterface;

/**
 * Service contract for managing loyalty transactions.
 * Note: Ledger is immutable; entries can only be appended, never updated or deleted.
 *
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Save transaction record (Insert only).
     *
     * @param TransactionInterface $transaction
     * @return TransactionInterface
     * @throws LocalizedException
     */
    public function save(TransactionInterface $transaction): TransactionInterface;

    /**
     * Retrieve transaction by ID.
     *
     * @param int $transactionId
     * @return TransactionInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $transactionId): TransactionInterface;

    /**
     * Retrieve transactions matching the search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return TransactionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TransactionSearchResultsInterface;
}
