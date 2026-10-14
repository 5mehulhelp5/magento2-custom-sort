<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Service;

use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Service transferring applied loyalty points data from Quote to Order.
 */
class QuoteToOrderLoyaltyTransfer
{
    /**
     * Copy loyalty data from Quote to Order if points were applied.
     *
     * @param Quote $quote
     * @param Order $order
     * @return void
     */
    public function execute(Quote $quote, Order $order): void
    {
        $appliedPoints = (float)$quote->getData('loyalty_points_applied');
        if ($appliedPoints <= 0.0) {
            return;
        }

        $order->setData('loyalty_points_applied', $appliedPoints);
        $order->setData('loyalty_discount_amount', (float)$quote->getData('loyalty_discount_amount'));
        $order->setData('base_loyalty_discount_amount', (float)$quote->getData('base_loyalty_discount_amount'));
    }
}
