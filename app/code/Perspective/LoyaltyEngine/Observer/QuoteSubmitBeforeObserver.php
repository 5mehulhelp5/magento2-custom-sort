<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Perspective\LoyaltyEngine\Model\Service\QuoteToOrderLoyaltyTransfer;

/**
 * Observer delegating loyalty data transfer from Quote to Order before order submission.
 */
class QuoteSubmitBeforeObserver implements ObserverInterface
{
    /**
     * @param QuoteToOrderLoyaltyTransfer $transferService
     */
    public function __construct(
        private readonly QuoteToOrderLoyaltyTransfer $transferService
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        if ($order instanceof Order && $quote instanceof Quote) {
            $this->transferService->execute($quote, $order);
        }
    }
}
