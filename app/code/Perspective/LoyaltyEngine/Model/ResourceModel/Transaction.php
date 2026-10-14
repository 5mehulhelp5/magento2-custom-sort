<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource model for loyalty transaction ledger.
 */
class Transaction extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init('loyalty_transactions', 'transaction_id');
    }

    /**
     * Prevent deleting immutable ledger entries.
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    public function delete(AbstractModel $object)
    {
        throw new LocalizedException(__('Loyalty ledger transactions cannot be deleted.'));
    }
}
