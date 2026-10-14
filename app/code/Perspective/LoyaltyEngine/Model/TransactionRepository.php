<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionSearchResultsInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionSearchResultsInterfaceFactory;
use Perspective\LoyaltyEngine\Api\TransactionRepositoryInterface;
use Perspective\LoyaltyEngine\Model\Indexer\BalanceIndexerHandler;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction as TransactionResource;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Repository for managing loyalty transactions.
 */
class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @param TransactionResource $resource
     * @param TransactionFactory $transactionFactory
     * @param CollectionFactory $collectionFactory
     * @param TransactionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param BalanceIndexerHandler $indexerHandler
     */
    public function __construct(
        private readonly TransactionResource $resource,
        private readonly TransactionFactory $transactionFactory,
        private readonly CollectionFactory $collectionFactory,
        private readonly TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly BalanceIndexerHandler $indexerHandler
    ) {
    }

    /**
     * @inheritdoc
     */
    public function save(TransactionInterface $transaction): TransactionInterface
    {
        if ($transaction->getTransactionId() !== null) {
            throw new LocalizedException(__('Loyalty ledger transactions are immutable and cannot be updated.'));
        }

        try {
            /** @var Transaction $transaction */
            $this->resource->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Could not save transaction: %1', $exception->getMessage()), $exception);
        }

        $customerId = (int)$transaction->getCustomerId();
        if ($customerId > 0) {
            $this->indexerHandler->executeList([$customerId]);
        }

        return $transaction;
    }

    /**
     * @inheritdoc
     */
    public function getById(int $transactionId): TransactionInterface
    {
        $transaction = $this->transactionFactory->create();
        $this->resource->load($transaction, $transactionId);

        if (!$transaction->getId()) {
            throw new NoSuchEntityException(__('Transaction with ID "%1" does not exist.', $transactionId));
        }

        return $transaction;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): TransactionSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Perspective\LoyaltyEngine\Api\Data\TransactionInterface[] $items */
        $items = array_values($collection->getItems());
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
