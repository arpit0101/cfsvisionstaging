<?php
namespace Webkul\Marketplace\Controller\Order;

class Invoice extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($order = $this->_initOrder()) {
            try {
                $seller_id = $this->_customerSession->getCustomerId();
                $order_id = $order->getId();
                if($order->canUnhold()) { 
                    $this->messageManager->addError(__("Can not create invoice as order is in HOLD state"));
                } else {
                    $data = array();
                    $data['send_email']=1;
                    $marketplace_order = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($order_id);
                    $invoiceId = $marketplace_order->getInvoiceId();
                    if(!$invoiceId){
                        $items=array();
                        $itemsarray=array();
                        $shippingAmount=0;
                        $codcharges=0;
                        $paymentCode = '';
                        $payment_method = '';
                        if($order->getPayment()){
                            $paymentCode = $order->getPayment()->getMethod();
                        }
                        $trackingsdata = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'order_id',
                                        ['eq' => $order_id]
                                    )
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $seller_id]
                                    );
                        foreach($trackingsdata as $tracking){
                            $shippingAmount=$tracking->getShippingCharges();
                            if($paymentCode == 'mpcashondelivery'){
                                $codcharges=$tracking->getCodCharges();
                            }
                        }
                        $codCharges = 0;
                        $tax = 0;
                        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'order_id',
                                        ['eq' => $order_id]
                                    )
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $seller_id]
                                    );
                        foreach($collection as $saleproduct){
                            if($paymentCode == 'mpcashondelivery'){
                                $codCharges = $codCharges+$saleproduct->getCodCharges();
                            }
                            $tax = $tax + $saleproduct->getTotalTax();
                            array_push($items, $saleproduct['order_item_id']);
                        }

                        $itemsarray = $this->_getItemQtys($order,$items);

                        if(count($itemsarray)>0){
                            if($order->canInvoice()) {

                                $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order, $itemsarray['data']);
                                if (!$invoice) {
                                    throw new LocalizedException(__('We can\'t save the invoice right now.'));
                                }
                                if (!$invoice->getTotalQty()) {
                                    throw new \Magento\Framework\Exception\LocalizedException(
                                        __('You can\'t create an invoice without products.')
                                    );
                                }
                                $this->_coreRegistry->register('current_invoice', $invoice);

                                if (!empty($data['capture_case'])) {
                                    $invoice->setRequestedCaptureCase($data['capture_case']);
                                }

                                if (!empty($data['comment_text'])) {
                                    $invoice->addComment(
                                        $data['comment_text'],
                                        isset($data['comment_customer_notify']),
                                        isset($data['is_visible_on_front'])
                                    );

                                    $invoice->setCustomerNote($data['comment_text']);
                                    $invoice->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                                }

                                $invoice->setShippingAmount($shippingAmount);
                                $invoice->setBaseShippingInclTax($shippingAmount);
                                $invoice->setBaseShippingAmount($shippingAmount);
                                $invoice->setSubtotal($itemsarray['subtotal']);
                                $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
                                if($paymentCode == 'mpcashondelivery'){
                                    $invoice->setMpcashondelivery($codCharges);
                                }
                                $invoice->setGrandTotal($itemsarray['subtotal']+$shippingAmount+$codcharges+$tax);
                                $invoice->setBaseGrandTotal($itemsarray['subtotal']+$shippingAmount+$codcharges+$tax);

                                $invoice->register();

                                $invoice->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                                $invoice->getOrder()->setIsInProcess(true);
                            
                                $transactionSave = $this->_objectManager->create(
                                    'Magento\Framework\DB\Transaction'
                                )->addObject(
                                    $invoice
                                )->addObject(
                                    $invoice->getOrder()
                                );
                                $transactionSave->save();

                                $invoice_id = $invoice->getId();

                                $this->invoiceSender->send($invoice);

                                $this->messageManager->addSuccess(__('Invoice has been created for this order.'));
                            }
                        }
                        /*update mpcod table records*/
                        if($invoice_id != '') {
                            if($paymentCode == 'mpcashondelivery'){
                                $saleslist_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                                        ->getCollection()
                                                        ->addFieldToFilter(
                                                            'order_id',
                                                            ['eq' => $order_id]
                                                        )
                                                        ->addFieldToFilter(
                                                            'seller_id',
                                                            ['eq' => $seller_id]
                                                        );
                                foreach($saleslist_coll as $saleslist){
                                    $saleslist->setCollectCodStatus(1);
                                    $saleslist->save();
                                }
                            }

                            $trackingcol1 = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                                                ->getCollection()
                                                ->addFieldToFilter(
                                                    'order_id',
                                                    ['eq' => $order_id]
                                                )
                                                ->addFieldToFilter(
                                                    'seller_id',
                                                    ['eq' => $seller_id]
                                                );
                            foreach($trackingcol1 as $row) {
                                $row->setInvoiceId($invoice_id);
                                $row->save();                           
                            }
                        }
                    }else{
                        $this->messageManager->addError(__('Cannot create Invoice for this order.'));
                    }
                }                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t save the invoice right now.'));
                $this->messageManager->addError($e->getMessage());
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                [
                    'id' => $order->getEntityId(),
                    '_secure'=>$this->getRequest()->isSecure()
                ]
            );
        }else{
            return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
