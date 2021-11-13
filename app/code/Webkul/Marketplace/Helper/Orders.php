<?php
namespace Webkul\Marketplace\Helper;
/**
 * Marketplace Orders helper
 */
class Orders extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

	/**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Framework\ObjectManagerInterface $objectManager
    * @param Magento\Customer\Model\Session $customerSession
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
    * @param \Magento\Framework\Stdlib\DateTime $dateTime
    */
    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Return the Customer seller status
     *
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     */    
    public function getOrderStatusData()
    {
        $model = $this->_objectManager->create('Magento\Sales\Model\Order\Status')
                    ->getResourceCollection()->getData();
        return $model;
    }

    /**
     * Return the seller Order data
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */    
    public function getOrderinfo($order_id='')
    {
        $data = array();
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $this->customerSession->getCustomerId()]
                    )
                    ->addFieldToFilter(
                        'order_id',
                        ['eq' => $order_id]
                    );
        foreach($model as $tracking){
            $data = $tracking;
        }
        return $data;
    }
	
	/**
     * Return the seller Order data
     *
     * @return \Webkul\Marketplace\Api\Data\OrdersInterface
     */    
    public function getOrderDetailsData($order_id='')
    {
		
        $collection1	=	$this->_objectManager->create('Webkul\Marketplace\Model\Orders')->load($order_id);
        if($collection1){
			return $collection1->getData();
		}
		return false;
    }

    /**
     * @param string $seller_id, $order
     * Cancel order
     * @return bool
     */
    public function cancelorder($order,$seller_id)
    {
        $flag = 0;
        if ($order->canCancel()) {
            $order->getPayment()->cancel();
            $flag = $this->mpregisterCancellation($order,$seller_id);
        }

        return $flag;
    }

    /**
     * @param string $order, $sellerid, $comment
     * @return bool
     * @throws Mage_Core_Exception
     */
    public function mpregisterCancellation($order,$seller_id,$comment = '')
    {
        $flag = 0;
        if ($order->canCancel()) {
            $cancelState = 'canceled';
            $items=array();
            $shippingAmount=0;
            $order_id = $order->getId();
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
                $items=explode(',',$tracking->getProductIds());

                $itemsarray = $this->_getItemQtys($order,$items);
                foreach($order->getAllItems() as $item){
                    if(in_array($item->getProductId(),$items)){
                        $flag = 1;
                        $item->setQtyCanceled($item->getQtyOrdered())->save();
                    }
                }
                foreach($order->getAllItems() as $item){
                    if ($cancelState != 'processing' && $item->getQtyToRefund()) {
                        if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                            $cancelState = 'processing';
                        } else {
                            $cancelState = 'complete';
                        }
                    }else if($item->getQtyToInvoice()){
                        $cancelState = 'processing';
                    }
                }
                $order->setState($cancelState, true, $comment)->save();
            }      
        }
        return $flag;
    }

    /**
     * Get requested items qtys
     * @return array()
     */
    protected function _getItemQtys($order,$items)
    {
        $data=array();
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach($order->getAllItems() as $item){
            if(in_array($item->getProductId(),$items)){
                $data[$item->getItemId()]=intval($item->getQtyOrdered());
                $subtotal+=$item->getRowTotal();
                $baseSubtotal+=$item->getBaseRowTotal();
            }else{
                $data[$item->getItemId()]=0;
            }   
        }
        return array('data'=>$data,'subtotal'=>$subtotal,'basesubtotal'=>$baseSubtotal);
    }
    
    public function getCommssionCalculation($order)
    {
        $percent = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getConfigCommissionRate();
        $lastOrderId=$order->getId();
        /*
        * Calculate cod and shipping charges if applied
        */
        $cod_charges= 0;
        $shipping_charges= 0;
        $cod_charges_arr = array();
        $shipping_charges_arr = array();
        $seller_order = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')->getCollection()
                            ->addFieldToFilter('order_id',$lastOrderId);
        foreach($seller_order as $info){
            if(!empty($info->getCodCharges())){
                $cod_charges = $info->getCodCharges();
            }
            $shipping_charges = $info->getShippingCharges();
            $cod_charges_arr[$info->getSellerId()] = $cod_charges;
            $shipping_charges_arr[$info->getSellerId()] = $shipping_charges;
        }

        $ordercollection=$this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')->getCollection()
                                        ->addFieldToFilter('order_id',array('eq'=>$lastOrderId))
                                        ->addFieldToFilter('cpprostatus',array('eq'=>0));
        foreach($ordercollection as $item){
            $seller_id = $item->getSellerId();
            $tax_amount = $item['totaltax'];
            if(!$this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getConfigTaxManage()){
                $tax_amount = 0;
            }
            if(empty($cod_charges_arr[$seller_id])){
                $cod_charges_arr[$seller_id] = 0;
            }
            if(empty($shipping_charges_arr[$seller_id])){
                $shipping_charges_arr[$seller_id] = 0;
            }
            $actual_seller_amount = $item->getActualSellerAmount()+$tax_amount+$cod_charges_arr[$seller_id]+$shipping_charges_arr[$seller_id];
            $totalamount = $item->getTotalAmount()+$tax_amount+$cod_charges_arr[$seller_id]+$shipping_charges_arr[$seller_id];
            
            $cod_charges_arr[$seller_id]= 0;
            $shipping_charges_arr[$seller_id]= 0;
                            
            $collectionverifyread = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
            $collectionverifyread->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            if(count($collectionverifyread)>=1){
                foreach($collectionverifyread as $verifyrow){
                    $totalsale=$verifyrow->getTotalSale()+$totalamount;
                    $totalremain=$verifyrow->getAmountRemain()+$actual_seller_amount;
                    $verifyrow->setTotalSale($totalsale);
                    $verifyrow->setAmountRemain($totalremain);
                    $totalcommission=$verifyrow->getTotalCommission()+($totalamount-$actual_seller_amount);
                    $verifyrow->setTotalCommission($totalcommission);
                    $verifyrow->setUpdatedAt($this->_date->gmtDate());
                    $verifyrow->save();
                }
            }
            else{
                $percent = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getConfigCommissionRate();          
                $collectionf=$this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
                $collectionf->setSellerId($seller_id);
                $collectionf->setTotalSale($totalamount);
                $collectionf->setAmountRemain($actual_seller_amount);
                $collectionf->setCommissionRate($percent);
                $totalcommission=$totalamount-$actual_seller_amount;
                $collectionf->setTotalCommission($totalcommission);
                $collectionf->save();                       
            }
            if($seller_id){
                $ordercount = 0;
                $feedbackcount = 0;
                $feedcountid = 0;
                $collectionfeed=$this->_objectManager->create('Webkul\Marketplace\Model\Feedbackcount')
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                foreach ($collectionfeed as $value) {
                    $feedcountid = $value->getEntityId();
                    $ordercount = $value->getOrderCount();
                    $feedbackcount = $value->getFeedbackCount();
                }
                $collectionfeed=$this->_objectManager->create('Webkul\Marketplace\Model\Feedbackcount')->load($feedcountid);
                $collectionfeed->setBuyerId($order->getCustomerId());
                $collectionfeed->setSellerId($seller_id);
                $collectionfeed->setOrderCount($ordercount+1);
                $collectionfeed->setFeedbackCount($feedbackcount);
                $collectionfeed->save();
            }
            $item->setCpprostatus(1)->save();   
        }
    }
    
    public function getTotalSellerShipping($orderId)
    {
        $seller_order = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')->getCollection();
        $seller_order->getSelect()
                    ->where('order_id ='.$orderId)
                    ->columns('SUM(shipping_charges) AS shipping')
                    ->group('order_id');     
        foreach($seller_order as $coll){
            if($coll->getOrderId() == $orderId){
                return $coll->getShipping();
            }
        }
        return 0;
    }

    public function paysellerpayment($order,$sellerid,$trid)
    {
        $lastOrderId=$order->getId();       
        $actparterprocost = 0;
        $totalamount = 0;
        /*
        * Calculate cod and shipping charges if applied
        */
        $cod_charges= 0;
        $shipping_charges= 0;
        $seller_order = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')->getCollection()
                            ->addFieldToFilter('seller_id',$sellerid)
                            ->addFieldToFilter('order_id',$lastOrderId);
        foreach($seller_order as $info){
            $cod_charges = $info->getCodCharges();
            $shipping_charges = $info->getShippingCharges();
        }
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $orderinfo = '';
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection()
                                    ->addFieldToFilter('seller_id',array('eq'=>$sellerid))
                                    ->addFieldToFilter('order_id',array('eq'=>$lastOrderId))
                                    ->addFieldToFilter('paid_status',array('eq'=>0))
                                    ->addFieldToFilter('cpprostatus',array('eq'=>1));
        foreach ($collection as $row) {
            $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($row['order_id']);
            $tax_amount = $row['total_tax'];
            $vendor_tax_amount = 0;
            if($helper->getConfigTaxManage()){
                $vendor_tax_amount = $tax_amount;
            }
            $actparterprocost = $actparterprocost+$row->getActualSellerAmount()+$vendor_tax_amount+$cod_charges+$shipping_charges;
            $totalamount = $totalamount + $row->getTotalAmount()+$tax_amount+$cod_charges+$shipping_charges;
            $cod_charges = 0;
            $shipping_charges = 0;
            $seller_id = $row->getSellerId();
            $orderinfo = $orderinfo."<tr>
                            <td class='item-info'>".$row['magerealorder_id']."</td>
                            <td class='item-info'>".$row['magepro_name']."</td>
                            <td class='item-qty'>".$row['magequantity']."</td>
                            <td class='item-price'>".$order->formatPrice($row['magepro_price'])."</td>
                            <td class='item-price'>".$order->formatPrice($row['total_commission'])."</td>
                            <td class='item-price'>".$order->formatPrice($row['actual_seller_amount'])."</td>
                        </tr>";
        }
        if($actparterprocost){      
            $collectionverifyread = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
            $collectionverifyread->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            if(count($collectionverifyread)>=1){
                foreach($collectionverifyread as $verifyrow){
                    if($verifyrow->getAmountRemain() >= $actparterprocost){
                        $totalremain=$verifyrow->getAmountRemain()-$actparterprocost;
                    }
                    else{
                        $totalremain=0;
                    }
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->save();
                    $totalremain;
                    $amountpaid=$verifyrow->getAmountReceived();
                    $totalrecived=$actparterprocost+$amountpaid;
                    $verifyrow->setLastAmountPaid($actparterprocost);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountReceived($totalrecived);
                    $verifyrow->setAmountRemain($totalremain);
                    $verifyrow->setUpdatedAt($this->_date->gmtDate());
                    $verifyrow->save();
                }
            }
            else{
                $percent = $helper->getConfigCommissionRate();          
                $collectionf = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
                $collectionf->setSellerId($seller_id);
                $collectionf->setTotalSale($totalamount);
                $collectionf->setLastAmountPaid($actparterprocost);
                $collectionf->setAmountReceived($actparterprocost);
                $collectionf->setAmountRemain(0);
                $collectionf->setCommissionRate($percent);
                $collectionf->setTotalCommission($totalamount-$actparterprocost);
                $collectionf->setCreatedAt($this->_date->gmtDate());
                $collectionf->setUpdatedAt($this->_date->gmtDate());
                $collectionf->save();                       
            }

            $unique_id = $this->checktransid();
            $transid = '';
            $transaction_number = '';
            if($unique_id!=''){
                $seller_trans = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')->getCollection()
                        ->addFieldToFilter('transaction_id',array('eq'=>$unique_id));            
                if(count($seller_trans)){
                    foreach ($seller_trans as $value) {
                        $id =$value->getId();
                        if($id){
                            $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')->load($id)->delete();
                        }
                    }
                }
                if($order->getPayment()){
                    $paymentCode = $order->getPayment()->getMethod();
                    $payment_type=$this->scopeConfig->getValue('payment/'.$paymentCode.'/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                }else{
                    $payment_type='Manual';
                }
                $seller_trans = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction');
                $seller_trans->setTransactionId($unique_id);
                $seller_trans->setTransactionAmount($actparterprocost);                
                $seller_trans->setOnlinetrId($trid);
                $seller_trans->setType('Online');
                $seller_trans->setMethod($payment_type);
                $seller_trans->setSellerId($seller_id);
                $seller_trans->setCustomNote('None');
                $seller_trans->setCreatedAt($this->_date->gmtDate());
                $seller_trans->setUpdatedAt($this->_date->gmtDate());
                $seller_trans = $seller_trans->save();
                $transid = $seller_trans->getId();
                $transaction_number = $seller_trans->getTransactionId();
            }

            
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter('seller_id',array('eq'=>$sellerid))
                            ->addFieldToFilter('order_id',array('eq'=>$lastOrderId))
                            ->addFieldToFilter('cpprostatus',array('eq'=>1))
                            ->addFieldToFilter('paid_status',array('eq'=>0));
            foreach ($collection as $row) {
                $row->setPaidStatus(1);
                $row->setTransId($transid)->save();
                $data['id']=$row->getOrderId();
                $data['seller_id']=$row->getSellerId();
                $this->_eventManager->dispatch(
                    'mp_pay_seller',
                    [$data]
                );
            }

            $seller = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller_id);
            
            $emailTempVariables = array();              

            $admin_storemail = $helper->getAdminEmailId();
            $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
            $adminUsername = 'Admin';

            $senderInfo = array();
            $receiverInfo = array();
            
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];

            $emailTempVariables['myvar1'] = $seller->getName();
            $emailTempVariables['myvar2'] = $transaction_number;
            $emailTempVariables['myvar3'] = $this->_date->gmtDate();
            $emailTempVariables['myvar4'] = $actparterprocost;
            $emailTempVariables['myvar5'] = $orderinfo;
            $emailTempVariables['myvar6'] = __('Seller has been paid online');

            $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendSellerPaymentEmail(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
        }
    }

    public function randString($length, $charset='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789')
    {
        $str = 'tr-';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }

        return $str;
    }

    public function checktransid(){
        $unique_id=$this->randString(11);
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')
                    ->getCollection()
                    ->addFieldToFilter('transaction_id',array('eq'=>$unique_id));
        $i=0;
        foreach ($collection as $value) {
                $i++;
        }   
        if($i!=0){
            $this->checktransid();
        }else{
            return $unique_id;
        }       
    }
}