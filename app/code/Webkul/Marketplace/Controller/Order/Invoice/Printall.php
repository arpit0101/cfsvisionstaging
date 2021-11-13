<?php
namespace Webkul\Marketplace\Controller\Order\Invoice;

use Magento\Framework\App\Filesystem\DirectoryList;

class Printall extends \Webkul\Marketplace\Controller\Order
{
    public function execute()
    {
        $get=$this->getRequest()->getParams();
        $todate = date_create($get['special_to_date']);
        $to = date_format($todate, 'Y-m-d H:i:s');
        $fromdate = date_create($get['special_from_date']);
        $from = date_format($fromdate, 'Y-m-d H:i:s');

        $invoiceIds = array();
        try {
            $seller_id = $this->_customerSession->getCustomerId();
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $seller_id]
            )
            ->addFieldToFilter('created_at', array('datetime' => true,'from' => $from,'to' =>  $to))
            ->addFieldToSelect('order_id')
            ->distinct(true);
            foreach($collection as $coll){              
                $shipping_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $coll->getOrderId()]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $seller_id]
                );
                foreach ($shipping_coll as $tracking) {
                    if($tracking->getInvoiceId()){
                        array_push($invoiceIds, $tracking->getInvoiceId());
                    }
                }
            }
            if (!empty($invoiceIds)) {
                $invoices = $this->_objectManager->create('Magento\Sales\Model\ResourceModel\Order\Invoice\Collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $invoiceIds))
                ->load();

                if (!$invoices->getSize()) {
                    $this->messageManager->addError(__('There are no printable documents related to selected date range.'));
                    return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
                }
                $pdf = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Order\Pdf\Invoice'
                )->getPdf($invoices);
                $date = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->date('Y-m-d_H-i-s');
                return $this->_objectManager->get('Magento\Framework\App\Response\Http\FileFactory')->create(
                    'invoiceslip' . $date . '.pdf',
                    $pdf->render(),
                    DirectoryList::VAR_DIR,
                    'application/pdf'
                );
            }else{
                $this->messageManager->addError(__('There are no printable documents related to selected date range.'));
                return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->messageManager->addError(__('We can\'t print the invoice right now.'));
            return $this->resultRedirectFactory->create()->setPath('marketplace/order/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
