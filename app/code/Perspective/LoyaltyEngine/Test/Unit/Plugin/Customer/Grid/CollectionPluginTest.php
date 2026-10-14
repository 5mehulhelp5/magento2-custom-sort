<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Plugin\Customer\Grid;

use Magento\Customer\Model\ResourceModel\Grid\Collection;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Perspective\LoyaltyEngine\Plugin\Customer\Grid\CollectionPlugin;

class CollectionPluginTest extends TestCase
{
    /**
     * @var CollectionPlugin
     */
    private CollectionPlugin $plugin;

    /**
     * @var Collection|MockObject
     */
    private Collection|MockObject $collectionMock;

    /**
     * @var Select|MockObject
     */
    private Select|MockObject $selectMock;

    protected function setUp(): void
    {
        $this->plugin = new CollectionPlugin();
        $this->collectionMock = $this->createMock(Collection::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->collectionMock->method('getSelect')
            ->willReturn($this->selectMock);
        $this->collectionMock->method('getTable')
            ->with('loyalty_balance_index')
            ->willReturn('loyalty_balance_index');
    }

    public function testBeforeLoadJoinsLoyaltyBalanceWhenNotJoined(): void
    {
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['main_table' => []]);

        $this->selectMock->expects($this->once())
            ->method('joinLeft')
            ->with(
                ['loyalty_balance' => 'loyalty_balance_index'],
                'loyalty_balance.customer_id = main_table.entity_id',
                $this->anything()
            );

        $this->collectionMock->expects($this->once())
            ->method('addFilterToMap')
            ->with('loyalty_points', 'loyalty_balance.total_active_points');

        $result = $this->plugin->beforeLoad($this->collectionMock, false, false);

        $this->assertSame([false, false], $result);
    }

    public function testBeforeGetSelectCountSqlJoinsLoyaltyBalance(): void
    {
        $this->selectMock->expects($this->once())
            ->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['main_table' => []]);

        $this->selectMock->expects($this->once())
            ->method('joinLeft');

        $result = $this->plugin->beforeGetSelectCountSql($this->collectionMock);

        $this->assertSame([], $result);
    }

    public function testAroundAddFieldToFilterRewritesLoyaltyPointsField(): void
    {
        $this->selectMock->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['loyalty_balance' => []]);

        $proceedCalled = false;
        $proceed = function ($field, $condition) use (&$proceedCalled) {
            $proceedCalled = true;
            $this->assertSame('loyalty_balance.total_active_points', $field);
            $this->assertSame(['from' => 10, 'to' => 50], $condition);
            return $this->collectionMock;
        };

        $result = $this->plugin->aroundAddFieldToFilter(
            $this->collectionMock,
            $proceed,
            'loyalty_points',
            ['from' => 10, 'to' => 50]
        );

        $this->assertTrue($proceedCalled);
        $this->assertSame($this->collectionMock, $result);
    }

    public function testAroundAddFieldToFilterPassesThroughOtherFields(): void
    {
        $this->selectMock->method('getPart')
            ->with(Select::FROM)
            ->willReturn(['loyalty_balance' => []]);

        $proceedCalled = false;
        $proceed = function ($field, $condition) use (&$proceedCalled) {
            $proceedCalled = true;
            $this->assertSame('email', $field);
            $this->assertSame('test@example.com', $condition);
            return $this->collectionMock;
        };

        $result = $this->plugin->aroundAddFieldToFilter(
            $this->collectionMock,
            $proceed,
            'email',
            'test@example.com'
        );

        $this->assertTrue($proceedCalled);
        $this->assertSame($this->collectionMock, $result);
    }
}
