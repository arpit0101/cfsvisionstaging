<?php
namespace Webkul\Marketplace\Controller\Order\Creditmemo;

use Magento\Framework\App\Filesystem\DirectoryList;

class Printpdf extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        if ($creditmemo = $this->_initCreditmemo()) {
            try {
                $pdf = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Order\Pdf\Creditmemo'
                )->getPdf(
                    [$creditmemo]
                );
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create(
                    'creditmemo' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t print the creditmemo right now.').$e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
