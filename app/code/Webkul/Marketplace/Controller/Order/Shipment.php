<?php
namespace Webkul\Marketplace\Controller\Order;

class Shipment extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($order,$items,$tracking_data)
    {
        $shipment = $this->shipmentFactory->create(
            $order,
            $items,
            $tracking_data
        );

        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }
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
                $marketplace_order = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getOrderinfo($order_id);
                $trackingid = '';
                $carrier = '';
                $tracking_data = array();
                if(!empty($this->getRequest()->getParam('tracking_id'))){
                    $trackingid = $this->getRequest()->getParam('tracking_id');

                    $tracking_data[1]['number'] = $trackingid;
                    $tracking_data[1]['carrier_code'] = 'custom';
                }
                if (!empty($this->getRequest()->getParam('tracking_id'))) {
                    $carrier = $this->getRequest()->getParam('carrier');
                    $tracking_data[1]['title'] = $carrier;
                }

                if(!empty($this->getRequest()->getParam('api_shipment'))){
                    $this->_eventManager->dispatch(
                        'generate_api_shipment',
                        ['api_shipment' => $this->getRequest()->getParam('api_shipment'), 'order_id' => $order_id]
                    );
                    $shipment_data = $this->_customerSession->getData('shipment_data');
                    $api_name = $shipment_data['api_name'];
                    $trackingid = $shipment_data['tracking_number'];
                    $this->_customerSession->unsetData('shipment_data');
                }

                if(!empty($this->getRequest()->getParam('api_shipment')) && $this->getRequest()->getParam('api_shipment') && $trackingid == ''){
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/view',
                        [
                            'id' => $order->getEntityId(),
                            '_secure'=>$this->getRequest()->isSecure()
                        ]
                    );
                }else{
                    if($order->canUnhold()) { 
                        $this->messageManager->addError(__("Can not create shipment as order is in HOLD state"));
                    } else {
                        $items=array();
                        $shippingAmount=0;

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
                        }

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
                            array_push($items, $saleproduct['order_item_id']);
                        }

                        $itemsarray = $this->_getShippingItemQtys($order,$items);
                        
                        if(count($itemsarray)>0){
                            $shipment = false;
                            $shipmentId = 0;
                            if(!empty($this->getRequest()->getParam('shipment_id'))){   
                                $shipmentId = $this->getRequest()->getParam('shipment_id');
                            }  
                            if($shipmentId){
                                $shipment = $this->_objectManager->create('Magento\Sales\Model\Order\Shipment')->load($shipmentId);
                            }elseif($order_id){
                                if($order->getForcedDoShipmentWithInvoice()){
                                    $this->messageManager->addError(__('Cannot do shipment for the order separately from invoice.'));
                                }
                                if(!$order->canShip()){
                                    $this->messageManager->addError(__('Cannot do shipment for the order.'));
                                }

                                $shipment = $this->_prepareShipment($order,$itemsarray['data'],$tracking_data);
                            }
                            $comment = '';
                            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                            $isNeedCreateLabel = !empty($data['create_shipping_label']) && $data['create_shipping_label'];
                            $shipment->getOrder()->setIsInProcess(true);

                            $transactionSave = $this->_objectManager->create(
                                'Magento\Framework\DB\Transaction'
                            )->addObject(
                                $shipment
                            )->addObject(
                                $shipment->getOrder()
                            );
                            $transactionSave->save();

                            $shipment_id = $shipment->getId();
                            
                            $courrier="custom";
                            $seller_collection = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                                                ->getCollection()
                                                ->addFieldToFilter(
                                                    'order_id',
                                                    ['eq' => $order_id]
                                                )
                                                ->addFieldToFilter(
                                                    'seller_id',
                                                    ['eq' => $seller_id]
                                                );
                            foreach($seller_collection as $row) {
                                if($shipment->getId() != '') {
                                    $row->setShipmentId($shipment->getId());
                                    $row->setTrackingNumber($trackingid);
                                    $row->setCarrierName($carrier);
                                    $row->save();
                                }
                            }

                            $this->shipmentSender->send($shipment);

                            $shipmentCreatedMessage = __('The shipment has been created.');
                            $labelCreatedMessage    = __('The shipping label has been created.');
                            $this->messageManager->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                                : $shipmentCreatedMessage); 
                            //$this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->getCommsionCalculation($order);
                        }
                    }
                }                               
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t save the shipment right now.'));
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
