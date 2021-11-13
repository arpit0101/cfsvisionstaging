<?php
namespace Webkul\Marketplace\Controller\Order\Creditmemo;

class View extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($creditmemo = $this->_initCreditmemo()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Order #%1', $creditmemo->getOrder()->getRealOrderId()));
            return $resultPage;
        }else{
            return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
