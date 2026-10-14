<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model;

use Magento\Framework\Api\SearchResults;
use Perspective\LoyaltyEngine\Api\Data\TransactionSearchResultsInterface;

/**
 * Service Data Object representing Transaction Search Results.
 */
class TransactionSearchResults extends SearchResults implements TransactionSearchResultsInterface
{
}
