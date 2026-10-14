<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for loyalty active balance flat table.
 */
class Balance extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('loyalty_balance_index', 'customer_id');
        $this->_isPkAutoIncrement = false;
    }
}
