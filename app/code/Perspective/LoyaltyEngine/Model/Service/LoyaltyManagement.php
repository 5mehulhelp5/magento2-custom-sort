<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Service;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Perspective\LoyaltyEngine\Api\BalanceRepositoryInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyManagementInterface;
use Perspective\LoyaltyEngine\Api\TransactionRepositoryInterface;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Perspective\LoyaltyEngine\Model\TransactionFactory;
use Psr\Log\LoggerInterface;

/**
 * Service implementation for applying and removing loyalty points in cart.
 */
class LoyaltyManagement implements LoyaltyManagementInterface
{
    /**
     * @param CartRepositoryInterface $cartRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param LoyaltyCalculationServiceInterface $calculationService
     * @param TransactionRepositoryInterface $transactionRepository
     * @param TransactionFactory $transactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly BalanceRepositoryInterface $balanceRepository,
        private readonly LoyaltyCalculationServiceInterface $calculationService,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly TransactionFactory $transactionFactory,
        private readonly TransactionCollectionFactory $transactionCollectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @inheritdoc
     */
    public function applyPoints(int $cartId, float $points): bool
    {
        $quote = $this->cartRepository->get($cartId);

        $customerId = (int)$quote->getCustomerId();
        if ($customerId <= 0) {
            throw new LocalizedException(__('Loyalty points can only be redeemed by registered customers.'));
        }

        if ($points <= 0.0) {
            throw new LocalizedException(__('Applied points amount must be greater than zero.'));
        }

        $balance = $this->balanceRepository->getByCustomerId($customerId);
        if ($balance->getTotalActivePoints() < $points) {
            throw new LocalizedException(
                __('Cannot apply %1 points. Active balance is only %2.', $points, $balance->getTotalActivePoints())
            );
        }

        $baseSubtotal = (float)$quote->getBaseSubtotal();
        $maxPoints = $this->calculationService->calculateMaxRedeemablePoints(
            $baseSubtotal,
            $balance->getTotalActivePoints()
        );

        if ($points > $maxPoints) {
            throw new LocalizedException(
                __(
                    'Cannot apply more than %1 points (maximum %2%% of cart value).',
                    $maxPoints,
                    $this->calculationService->getMaxRedemptionPercentage()
                )
            );
        }

        $discount = $this->calculationService->convertPointsToCurrency($points);

        $quote->setData('loyalty_points_applied', $points);
        $quote->setData('loyalty_discount_amount', $discount);
        $quote->setData('base_loyalty_discount_amount', $discount);

        if ($quote instanceof Quote) {
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
        }

        $this->cartRepository->save($quote);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function removePoints(int $cartId): bool
    {
        $quote = $this->cartRepository->get($cartId);

        $quote->setData('loyalty_points_applied', 0.0);
        $quote->setData('loyalty_discount_amount', 0.0);
        $quote->setData('base_loyalty_discount_amount', 0.0);

        if ($quote instanceof Quote) {
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
        }

        $this->cartRepository->save($quote);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function recordOrderRedemption(OrderInterface $order): void
    {
        $orderId = (int)$order->getEntityId();
        $customerId = (int)$order->getCustomerId();
        $points = (float)$order->getData('loyalty_points_applied');

        if ($orderId <= 0 || $customerId <= 0 || $points <= 0.0) {
            return;
        }

        $existing = $this->transactionCollectionFactory->create()
            ->addFieldToFilter(TransactionInterface::ORDER_ID, $orderId)
            ->addFieldToFilter(TransactionInterface::TYPE, TransactionInterface::TYPE_REDEMPTION)
            ->getSize();

        if ($existing > 0) {
            return;
        }

        try {
            $transaction = $this->transactionFactory->create();
            $transaction->setCustomerId($customerId);
            $transaction->setOrderId($orderId);
            $transaction->setPoints(-$points);
            $transaction->setType(TransactionInterface::TYPE_REDEMPTION);
            $transaction->setComment(sprintf('Redeemed points for order #%s', (string)$order->getIncrementId()));

            $this->transactionRepository->save($transaction);

            $this->logger->info(
                sprintf(
                    'Recorded redemption of %f points for customer #%d on order #%d',
                    $points,
                    $customerId,
                    $orderId
                )
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Failed to record loyalty redemption for order #%d: %s', $orderId, $e->getMessage())
            );
        }
    }
}
