<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Test\Unit\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Page\Config;
use Magento\Framework\View\Page\Title;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Perspective\LoyaltyEngine\Controller\Account\Index;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Customer Account Loyalty Index controller.
 */
class IndexTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    private Session|MockObject $customerSessionMock;

    /**
     * @var PageFactory|MockObject
     */
    private PageFactory|MockObject $pageFactoryMock;

    /**
     * @var RedirectFactory|MockObject
     */
    private RedirectFactory|MockObject $redirectFactoryMock;

    /**
     * @var Index
     */
    private Index $controller;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->pageFactoryMock = $this->createMock(PageFactory::class);
        $this->redirectFactoryMock = $this->createMock(RedirectFactory::class);

        $this->controller = new Index(
            $this->customerSessionMock,
            $this->pageFactoryMock,
            $this->redirectFactoryMock
        );
    }

    public function testExecuteRedirectsToLoginWhenNotLoggedIn(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $redirectMock = $this->createMock(Redirect::class);
        $redirectMock->expects($this->once())
            ->method('setPath')
            ->with('customer/account/login')
            ->willReturnSelf();

        $this->redirectFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($redirectMock);

        $this->assertSame($redirectMock, $this->controller->execute());
    }

    public function testExecuteReturnsPageWhenLoggedIn(): void
    {
        $this->customerSessionMock->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);

        $titleMock = $this->createMock(Title::class);
        $titleMock->expects($this->once())
            ->method('set')
            ->with(__('My Loyalty Points'));

        $configMock = $this->createMock(Config::class);
        $configMock->expects($this->once())
            ->method('getTitle')
            ->willReturn($titleMock);

        $pageMock = $this->createMock(Page::class);
        $pageMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configMock);

        $this->pageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($pageMock);

        $this->assertSame($pageMock, $this->controller->execute());
    }
}
