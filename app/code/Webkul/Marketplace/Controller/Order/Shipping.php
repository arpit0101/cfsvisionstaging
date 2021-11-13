<?php
namespace Webkul\Marketplace\Controller\Order;

class Shipping extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Webkul\Marketplace\Controller\Order\Shipping
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Manage Print PDF Header Info'));
        return $resultPage;
    }
}
