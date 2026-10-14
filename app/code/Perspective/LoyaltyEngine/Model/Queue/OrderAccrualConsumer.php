<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Model\Queue;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Perspective\LoyaltyEngine\Api\Data\OrderAccrualMessageInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Api\LoyaltyCalculationServiceInterface;
use Perspective\LoyaltyEngine\Api\TransactionRepositoryInterface;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;
use Perspective\LoyaltyEngine\Model\TransactionFactory;
use Psr\Log\LoggerInterface;

/**
 * Consumer for processing order loyalty cashback accrual messages from RabbitMQ.
 */
class OrderAccrualConsumer
{
    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionRepositoryInterface $transactionRepository
     * @param TransactionFactory $transactionFactory
     * @param TransactionCollectionFactory $transactionCollectionFactory
     * @param LoyaltyCalculationServiceInterface $calculationService
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly TransactionRepositoryInterface $transactionRepository,
        private readonly TransactionFactory $transactionFactory,
        private readonly TransactionCollectionFactory $transactionCollectionFactory,
        private readonly LoyaltyCalculationServiceInterface $calculationService,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Process message from queue.
     *
     * @param OrderAccrualMessageInterface $message
     * @return void
     */
    public function process(OrderAccrualMessageInterface $message): void
    {
        $orderId = $message->getOrderId();
        $customerId = $message->getCustomerId();

        if ($orderId <= 0 || $customerId <= 0) {
            $this->logger->warning(
                sprintf('Invalid accrual message: orderId=%d, customerId=%d', $orderId, $customerId)
            );
            return;
        }

        if ($this->isAlreadyAccrued($orderId)) {
            $this->logger->info(
                sprintf('Loyalty points already accrued for order #%d. Skipping.', $orderId)
            );
            return;
        }

        $order = $this->loadValidOrder($orderId, $customerId);
        if ($order) {
            $this->accruePointsForOrder($order, $orderId, $customerId);
        }
    }

    /**
     * Check if cashback has already been accrued for the order.
     *
     * @param int $orderId
     * @return bool
     */
    private function isAlreadyAccrued(int $orderId): bool
    {
        $existingAccruals = $this->transactionCollectionFactory->create()
            ->addFieldToFilter(TransactionInterface::ORDER_ID, $orderId)
            ->addFieldToFilter(TransactionInterface::TYPE, TransactionInterface::TYPE_ACCRUAL);

        return $existingAccruals->getSize() > 0;
    }

    /**
     * Load order and verify customer ownership.
     *
     * @param int $orderId
     * @param int $customerId
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function loadValidOrder(int $orderId, int $customerId): ?\Magento\Sales\Api\Data\OrderInterface
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException $e) {
            $this->logger->error(
                sprintf('Order #%d not found during loyalty accrual: %s', $orderId, $e->getMessage())
            );
            return null;
        }

        if ((int)$order->getCustomerId() !== $customerId) {
            $this->logger->warning(
                sprintf(
                    'Customer ID mismatch for order #%d: expected %d, got %d',
                    $orderId,
                    $customerId,
                    (int)$order->getCustomerId()
                )
            );
            return null;
        }

        return $order;
    }

    /**
     * Calculate points and record accrual transaction.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param int $orderId
     * @param int $customerId
     * @return void
     */
    private function accruePointsForOrder(
        \Magento\Sales\Api\Data\OrderInterface $order,
        int $orderId,
        int $customerId
    ): void {
        $baseSubtotal = (float)$order->getBaseSubtotal();
        $points = $this->calculationService->calculateCashbackPoints($baseSubtotal);

        if ($points <= 0.0) {
            $this->logger->info(
                sprintf('Calculated 0 cashback points for order #%d (subtotal: %f).', $orderId, $baseSubtotal)
            );
            return;
        }

        $transaction = $this->transactionFactory->create();
        $transaction->setCustomerId($customerId);
        $transaction->setOrderId($orderId);
        $transaction->setPoints($points);
        $transaction->setType(TransactionInterface::TYPE_ACCRUAL);
        $transaction->setComment(sprintf('Cashback 5%% for order #%s', $order->getIncrementId()));

        $this->transactionRepository->save($transaction);

        $this->logger->info(
            sprintf('Accrued %f loyalty points for customer #%d on order #%d.', $points, $customerId, $orderId)
        );
    }
}
