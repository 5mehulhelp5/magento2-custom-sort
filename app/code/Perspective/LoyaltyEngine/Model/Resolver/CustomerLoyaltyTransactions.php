<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Resolver for Customer.loyalty_transactions history.
 */
class CustomerLoyaltyTransactions implements ResolverInterface
{
    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $customerId = 0;

        if (isset($value['model']) && method_exists($value['model'], 'getId')) {
            $customerId = (int)$value['model']->getId();
        } elseif ($context->getUserId()) {
            $customerId = (int)$context->getUserId();
        }

        if ($customerId <= 0) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $pageSize = isset($args['pageSize']) ? max(1, (int)$args['pageSize']) : 20;
        $currentPage = isset($args['currentPage']) ? max(1, (int)$args['currentPage']) : 1;

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(TransactionInterface::CUSTOMER_ID, $customerId);
        $collection->setOrder(TransactionInterface::CREATED_AT, 'DESC');

        $totalCount = $collection->getSize();

        $collection->setPageSize($pageSize);
        $collection->setCurPage($currentPage);

        $items = [];
        /** @var \Perspective\LoyaltyEngine\Model\Transaction $transaction */
        foreach ($collection as $transaction) {
            $items[] = [
                'transaction_id' => $transaction->getTransactionId(),
                'order_id' => $transaction->getOrderId(),
                'points' => $transaction->getPoints(),
                'type' => $transaction->getType(),
                'comment' => $transaction->getComment(),
                'created_at' => $transaction->getCreatedAt()
            ];
        }

        return [
            'total_count' => $totalCount,
            'items' => $items
        ];
    }
}
