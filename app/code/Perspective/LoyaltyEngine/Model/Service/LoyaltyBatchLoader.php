<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Service;

use Magento\Framework\App\ResourceConnection;

/**
 * In-memory batch loader for customer loyalty balances to prevent GraphQL N+1 database queries.
 */
class LoyaltyBatchLoader
{
    /**
     * @var array<int, int>
     */
    private array $buffer = [];

    /**
     * @var array<int, float>
     */
    private array $loaded = [];

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        private readonly ResourceConnection $resource
    ) {
    }

    /**
     * Buffer a customer ID for batch loading.
     *
     * @param int $customerId
     * @return void
     */
    public function add(int $customerId): void
    {
        if (!isset($this->loaded[$customerId])) {
            $this->buffer[$customerId] = $customerId;
        }
    }

    /**
     * Load all buffered customer balances in a single SQL query.
     *
     * @return void
     */
    public function loadBuffer(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('loyalty_balance_index');

        $select = $connection->select()
            ->from($tableName, ['customer_id', 'total_active_points'])
            ->where('customer_id IN (?)', array_values($this->buffer));

        $results = $connection->fetchPairs($select);

        foreach ($this->buffer as $customerId) {
            $this->loaded[$customerId] = isset($results[$customerId]) ? (float)$results[$customerId] : 0.0;
        }

        $this->buffer = [];
    }

    /**
     * Retrieve customer balance from batch cache, executing single query if needed.
     *
     * @param int $customerId
     * @return float
     */
    public function getBalance(int $customerId): float
    {
        if (!isset($this->loaded[$customerId])) {
            $this->loadBuffer();
        }

        return $this->loaded[$customerId] ?? 0.0;
    }
}
