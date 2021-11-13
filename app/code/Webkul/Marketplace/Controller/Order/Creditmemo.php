<?php
namespace Webkul\Marketplace\Controller\Order;

class Creditmemo extends \Webkul\Marketplace\Controller\Order
{
    /**
     * @param \Magento\Sales\Model\Order $order
     * @return $this|bool
     */
    protected function _initCreditmemoInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = $this->invoiceRepository->get($invoiceId);
            $invoice->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }
        return false;
    }

    /**
     * Initialize creditmemo model instance
     *
     * @return \Magento\Sales\Model\Order\Creditmemo|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _initOrderCreditmemo($order)
    {
        $data = $this->getRequest()->getPost('creditmemo');
        $refund_data=$this->getRequest()->getParams();

        $creditmemo = false;

        $seller_id = $this->_customerSession->getCustomerId();
        $order_id = $order->getId();

        $invoice = $this->_initCreditmemoInvoice($order);
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

        $savedData = $this->_getItemData($order,$items);      
    
        $qtys = array();
        foreach ($savedData as $orderItemId =>$itemData) {
            if (isset($itemData['qty'])) {
                $qtys[$orderItemId] = $itemData['qty'];
            }
            if (isset($refund_data['creditmemo']['items'][$orderItemId]['back_to_stock'])) {
                $backToStock[$orderItemId] = true;
            }
        }
        
        if(empty($refund_data['creditmemo']['shipping_amount'])){
            $refund_data['creditmemo']['shipping_amount'] = 0;
        }
        if(empty($refund_data['creditmemo']['adjustment_positive'])){
            $refund_data['creditmemo']['adjustment_positive'] = 0;
        }
        if(empty($refund_data['creditmemo']['adjustment_negative'])){
            $refund_data['creditmemo']['adjustment_negative'] = 0;
        }
        if(!$shippingAmount>=$refund_data['creditmemo']['shipping_amount']){
            $refund_data['creditmemo']['shipping_amount'] = 0;
        }
        $refund_data['creditmemo']['qtys'] = $qtys;
        if ($invoice) {
            $creditmemo = $this->creditmemoFactory->createByInvoice($invoice, $refund_data['creditmemo']);
        } else {
            $creditmemo = $this->creditmemoFactory->createByOrder($order, $refund_data['creditmemo']);
        }

        /**
         * Process back to stock flags
         */
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            $orderItem = $creditmemoItem->getOrderItem();
            $parentId = $orderItem->getParentItemId();
            if (isset($backToStock[$orderItem->getId()])) {
                $creditmemoItem->setBackToStock(true);
            } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                $creditmemoItem->setBackToStock(true);
            } elseif (empty($savedData)) {
                $creditmemoItem->setBackToStock(
                    $this->stockConfiguration->isAutoReturnEnabled()
                );
            } else {
                $creditmemoItem->setBackToStock(false);
            }
        }

        $this->_coreRegistry->register('current_creditmemo', $creditmemo);

        return $creditmemo;
    }


    /**
     * Save creditmemo
     */
    public function execute()
    {
        $order_id = $this->getRequest()->getParam('id');
        $seller_id = $this->_customerSession->getCustomerId();
        if ($order = $this->_initOrder()) {
            try {                
                $creditmemo = $this->_initOrderCreditmemo($order);
                if($creditmemo) {
                    if (!$creditmemo->isValidGrandTotal()) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('The credit memo\'s total must be positive.')
                        );
                    }
                    $data=$this->getRequest()->getParam('creditmemo');

                    if (!empty($data['comment_text'])) {
                        $creditmemo->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );
                        $creditmemo->setCustomerNote($data['comment_text']);
                        $creditmemo->setCustomerNoteNotify(isset($data['comment_customer_notify']));
                    }

                    if (isset($data['do_offline'])) {
                        //do not allow online refund for Refund to Store Credit
                        if (!$data['do_offline'] && !empty($data['refund_customerbalance_return_enable'])) {
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Cannot create online refund for Refund to Store Credit.')
                            );
                        }
                    }
                    $creditmemoManagement = $this->_objectManager->create(
                        'Magento\Sales\Api\CreditmemoManagementInterface'
                    );
                    $creditmemo = $creditmemoManagement->refund($creditmemo, (bool)$data['do_offline'], !empty($data['send_email']));

                    /*update records*/
                    $creditmemo_ids = array();
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
                    foreach($trackingcol1 as $tracking) {
                        if($tracking->getCreditmemoId()){                           
                            $creditmemo_ids = explode(',', $tracking->getCreditmemoId());
                        }
                        array_push($creditmemo_ids, $creditmemo->getId());
                        $tracking->setCreditmemoId(implode(',', $creditmemo_ids));
                        $tracking->save();                        
                    }

                    if (!empty($data['send_email'])) {
                        $this->creditmemoSender->send($creditmemo);
                    }

                    if (!empty($data['send_email'])) {
                        $this->creditmemoSender->send($creditmemo);
                    }

                    $this->messageManager->addSuccess(__('You created the credit memo.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('We can\'t save the credit memo right now.').$e->getMessage());
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

    /**
     * Get requested items qtys
     */
    protected function _getItemData($order,$items)
    {
        $refund_data=$this->getRequest()->getParams();
        $data['items'] = array();
        foreach($order->getAllItems() as $item){            
            if(in_array($item->getItemId(),$items)){

                $data['items'][$item->getItemId()]['qty']=intval($refund_data['creditmemo']['items'][$item->getItemId()]['qty']);

                $_item = $item;
                // for bundle product
                $bundleitems = array_merge(array($_item), $_item->getChildrenItems());
                if ($_item->getParentItem()) continue;

                if($_item->getProductType()=='bundle'){
                    foreach ($bundleitems as $_bundleitem){ 
                        if ($_bundleitem->getParentItem()){
                            $data['items'][$_bundleitem->getItemId()]['qty']=intval($refund_data['creditmemo']['items'][$_bundleitem->getItemId()]['qty']);
                        }
                    }
                }
            }else{
                if(!$item->getParentItemId()){
                    $data['items'][$item->getItemId()]['qty']=0;
                }               
            }   
        }
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }
        return $qtys;
    }
}
