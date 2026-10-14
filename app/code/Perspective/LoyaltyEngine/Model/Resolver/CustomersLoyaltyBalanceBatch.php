<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Root resolver for loyalty_customers_balance batch query.
 */
class CustomersLoyaltyBalanceBatch implements ResolverInterface
{
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
        if (empty($args['customer_ids']) || !is_array($args['customer_ids'])) {
            throw new GraphQlInputException(__('Required parameter "customer_ids" must be a non-empty array of IDs.'));
        }

        $items = [];
        foreach ($args['customer_ids'] as $customerId) {
            $items[] = [
                'customer_id' => (int)$customerId
            ];
        }

        return $items;
    }
}
