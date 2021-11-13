<?php
namespace Webkul\Marketplace\Controller\Order;

class Cancel extends \Webkul\Marketplace\Controller\Order
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
                $flag = $this->_objectManager->create('Webkul\Marketplace\Helper\Orders')->cancelorder($order,$seller_id);
                if($flag){
                    $paymentCode = '';
                    $payment_method = '';
                    if($order->getPayment()){
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    $order_id = $this->getRequest()->getParam('id');
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
                        $saleproduct->setCpprostatus(2);
                        $saleproduct->setPaidStatus(2);    
                        if($paymentCode == 'mpcashondelivery'){
                            $saleproduct->setCollectCodStatus(2);
                            $saleproduct->setAdminPayStatus(2);   
                        }           
                        $saleproduct->save();
                        
                        $trackingcoll = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'order_id',
                                        ['eq' => $order_id]
                                    )
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $seller_id]
                                    );
                        foreach($trackingcoll as $tracking){
                            $tracking->setTrackingNumber('canceled');
                            $tracking->setCarrierName('canceled');
                            $tracking->setIsCanceled(1);
                            $tracking->save();
                        }
                    }
                    $this->messageManager->addSuccess(__('The order has been cancelled.'));
                }else{
                    $this->messageManager->addError(__('You are not permitted to cancel this order.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/history', ['_secure'=>$this->getRequest()->isSecure()]);
                }                
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError(__('We can\'t send the email order right now.'));
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
