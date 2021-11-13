<?php namespace Webkul\Marketplace\Controller\Order\Invoice;
use Magento\Framework\App\Filesystem\DirectoryList;
class Pdfprintmanager extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
		if ($invoice = $this->_initInvoiceForManager()) {
			
            try {
                $pdf = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Order\Pdf\Invoice'
                )->getPdf(
                    [$invoice]
                );
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create(
                    'invoice' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t print the invoice right now.'));
                return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
