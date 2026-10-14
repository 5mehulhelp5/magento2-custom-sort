<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Perspective\LoyaltyEngine\Api\LoyaltyManagementInterface;

/**
 * Observer recording points redemption transaction upon order placement.
 */
class OrderPlaceRedemptionObserver implements ObserverInterface
{
    /**
     * @param LoyaltyManagementInterface $loyaltyManagement
     */
    public function __construct(
        private readonly LoyaltyManagementInterface $loyaltyManagement
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        /** @var Order|null $order */
        $order = $observer->getEvent()->getOrder();

        if ($order instanceof Order) {
            $this->loyaltyManagement->recordOrderRedemption($order);
        }
    }
}
