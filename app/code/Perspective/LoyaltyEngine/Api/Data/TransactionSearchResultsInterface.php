<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface TransactionSearchResultsInterface for loyalty transaction search results.
 *
 * @api
 */
interface TransactionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get transactions list.
     *
     * @return \Perspective\LoyaltyEngine\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set transactions list.
     *
     * @param \Perspective\LoyaltyEngine\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
