<?php
namespace Webkul\Marketplace\Controller\Order\Invoice;

class View extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($invoice = $this->_initInvoice()) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__(__('Order #%1', $invoice->getOrder()->getRealOrderId())));
            return $resultPage;
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
