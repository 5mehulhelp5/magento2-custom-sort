<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\ResourceModel\Balance;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Perspective\LoyaltyEngine\Model\Balance as BalanceModel;
use Perspective\LoyaltyEngine\Model\ResourceModel\Balance as BalanceResource;

/**
 * Collection for loyalty balance index.
 */
class Collection extends AbstractCollection
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(BalanceModel::class, BalanceResource::class);
        $this->_idFieldName = 'customer_id';
    }
}
