<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Perspective\LoyaltyEngine\Api\Data\TransactionInterface;
use Perspective\LoyaltyEngine\Api\Data\TransactionSearchResultsInterfaceFactory;
use Perspective\LoyaltyEngine\Model\Indexer\BalanceIndexerHandler;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction as TransactionResource;
use Perspective\LoyaltyEngine\Model\ResourceModel\Transaction\CollectionFactory;
use Perspective\LoyaltyEngine\Model\Transaction;
use Perspective\LoyaltyEngine\Model\TransactionFactory;
use Perspective\LoyaltyEngine\Model\TransactionRepository;

/**
 * Unit test for TransactionRepository verifying immutable ledger behavior.
 */
class TransactionRepositoryTest extends TestCase
{
    private TransactionResource|MockObject $resourceMock;
    private TransactionFactory|MockObject $transactionFactoryMock;
    private CollectionFactory|MockObject $collectionFactoryMock;
    private TransactionSearchResultsInterfaceFactory|MockObject $searchResultsFactoryMock;
    private CollectionProcessorInterface|MockObject $collectionProcessorMock;
    private BalanceIndexerHandler|MockObject $indexerHandlerMock;
    private TransactionRepository $repository;

    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(TransactionResource::class);
        $this->transactionFactoryMock = $this->createMock(TransactionFactory::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(TransactionSearchResultsInterfaceFactory::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);
        $this->indexerHandlerMock = $this->createMock(BalanceIndexerHandler::class);

        $this->repository = new TransactionRepository(
            $this->resourceMock,
            $this->transactionFactoryMock,
            $this->collectionFactoryMock,
            $this->searchResultsFactoryMock,
            $this->collectionProcessorMock,
            $this->indexerHandlerMock
        );
    }

    public function testSaveThrowsExceptionForExistingTransaction(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn(123);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Loyalty ledger transactions are immutable and cannot be updated.');

        $this->resourceMock->expects($this->never())->method('save');

        $this->repository->save($transactionMock);
    }

    public function testSaveSuccessfulForNewTransaction(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn(null);
        $transactionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(42);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($transactionMock);

        $this->indexerHandlerMock->expects($this->once())
            ->method('executeList')
            ->with([42]);

        $result = $this->repository->save($transactionMock);
        $this->assertSame($transactionMock, $result);
    }

    public function testSaveSuccessfulWithoutCustomerDoesNotTriggerIndex(): void
    {
        $transactionMock = $this->createMock(Transaction::class);
        $transactionMock->expects($this->once())
            ->method('getTransactionId')
            ->willReturn(null);
        $transactionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(0);

        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($transactionMock);

        $this->indexerHandlerMock->expects($this->never())
            ->method('executeList');

        $result = $this->repository->save($transactionMock);
        $this->assertSame($transactionMock, $result);
    }
}
