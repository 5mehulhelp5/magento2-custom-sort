<?php

declare(strict_types=1);

namespace Perspective\LoyaltyEngine\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller displaying customer loyalty points balance and transaction history.
 */
class Index implements HttpGetActionInterface
{
    /**
     * @param \Magento\Customer\Model\Session\Proxy|Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     */
    public function __construct(
        private readonly Session $customerSession,
        private readonly PageFactory $resultPageFactory,
        private readonly RedirectFactory $resultRedirectFactory
    ) {
    }

    /**
     * Execute action based on request and return result
     *
     * @return Page|Redirect
     */
    public function execute(): Page|Redirect
    {
        if (!$this->customerSession->isLoggedIn()) {
            $redirect = $this->resultRedirectFactory->create();
            $redirect->setPath('customer/account/login');
            return $redirect;
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Loyalty Points'));

        return $resultPage;
    }
}
