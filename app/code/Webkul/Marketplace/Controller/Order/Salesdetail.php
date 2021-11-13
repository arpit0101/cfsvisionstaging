<?php
namespace Webkul\Marketplace\Controller\Order;

class Salesdetail extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default Salesdetail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */

        $productId = (int) $this->getRequest()->getParam('id');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order Details of Product : %1', $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId)->getName()));
        return $resultPage;
    }
}
