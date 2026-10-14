<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Indexer;

use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\Mview\ActionInterface as MviewActionInterface;

/**
 * Custom indexer and Mview handler for customer loyalty balance.
 */
class BalanceIndexer implements IndexerActionInterface, MviewActionInterface
{
    /**
     * @param BalanceIndexerHandler $handler
     */
    public function __construct(
        private readonly BalanceIndexerHandler $handler
    ) {
    }

    /**
     * Mview execution triggered when changelog records changes.
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids): void
    {
        $this->handler->executeList((array)$ids);
    }

    /**
     * Full reindex of customer balances.
     *
     * @return void
     */
    public function executeFull(): void
    {
        $this->handler->executeFull();
    }

    /**
     * Reindex specific customer IDs list.
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids): void
    {
        $this->handler->executeList($ids);
    }

    /**
     * Reindex single customer ID.
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id): void
    {
        $this->handler->executeList([(int)$id]);
    }
}
