<?php
namespace Webkul\Marketplace\Controller\Order\Invoice;

class Email extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoice = $this->_initInvoice()) {
            try {
                $this->_objectManager->create('Magento\Sales\Api\InvoiceManagementInterface')->notify($invoice->getEntityId());
                $this->messageManager->addSuccess(__('The message has been sent.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Failed to send the invoice email.'));
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                ['order_id' => $invoice->getOrder()->getId(), 'invoice_id' => $invoiceId, '_secure'=>$this->getRequest()->isSecure()]
            );
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
