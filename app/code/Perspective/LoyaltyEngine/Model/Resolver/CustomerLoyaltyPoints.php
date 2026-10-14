<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;
use Perspective\LoyaltyEngine\Model\Service\LoyaltyBatchLoader;

/**
 * Resolver for Customer.loyalty_points utilizing Deferred batch loader.
 */
class CustomerLoyaltyPoints implements ResolverInterface
{
    /**
     * @param ValueFactory $valueFactory
     * @param LoyaltyBatchLoader $batchLoader
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ValueFactory $valueFactory,
        private readonly LoyaltyBatchLoader $batchLoader,
        private readonly StoreManagerInterface $storeManager
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
    ): Value {
        $customerId = 0;

        if (isset($value['model']) && method_exists($value['model'], 'getId')) {
            $customerId = (int)$value['model']->getId();
        } elseif ($context->getUserId()) {
            $customerId = (int)$context->getUserId();
        }

        if ($customerId <= 0) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $this->batchLoader->add($customerId);

        return $this->valueFactory->create(function () use ($customerId) {
            return [
                'balance' => $this->batchLoader->getBalance($customerId),
                'currency_code' => $this->storeManager->getStore()->getCurrentCurrencyCode()
            ];
        });
    }
}
