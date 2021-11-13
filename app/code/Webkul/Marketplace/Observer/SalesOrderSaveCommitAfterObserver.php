<?php
/**
 * Webkul Marketplace SalesOrderSaveCommitAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;

class SalesOrderSaveCommitAfterObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        UrlInterface $urlBuilder
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
		$resource		=	 $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection		=	 $resource->getConnection();
		$tableName		=	 $resource->getTableName('sales_order_address');
		$region_id		=	 $this->_customerSession->getCityId();
		$area_id		=	 $this->_customerSession->getAreaId();
		$sql 			=    "UPDATE " . $tableName . " SET area_id = '".$area_id."', region_id = '".$region_id."' WHERE `parent_id` = " . $lastOrderId;
		$connection->query($sql);
		
        if($order->getState() == 'complete'){
			
			
            $percent = $helper->getConfigCommissionRate();
            /*
            * Calculate cod and shipping charges if applied
            */
            $paymentCode = '';
            if($order->getPayment()){
                $paymentCode = $order->getPayment()->getMethod();
            }
            $cod_charges= 0;
            $shipping_charges= 0;
            $cod_charges_arr = array();
            $shipping_charges_arr = array();
            $seller_order = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                            ->getCollection()
                            ->addFieldToFilter('order_id',$lastOrderId);
            foreach($seller_order as $info){
                if($paymentCode == 'mpcashondelivery'){
                    $cod_charges = $info->getCodCharges();
                }
                $shipping_charges = $info->getShippingCharges();
                $cod_charges_arr[$info->getSellerId()] = $cod_charges;
                $shipping_charges_arr[$info->getSellerId()] = $shipping_charges;
            }
            $ordercollection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection()
                                    ->addFieldToFilter('order_id',array('eq'=>$lastOrderId))
                                    ->addFieldToFilter('cpprostatus',array('eq'=>0));
            foreach($ordercollection as $item){
                $seller_id = $item->getSellerId();      
                $tax_amount = $item['total_tax'];
                if(!$helper->getConfigTaxManage()){
                    $tax_amount = 0;
                }
                $actparterprocost = $item->getActualSellerAmount()+$tax_amount+$cod_charges_arr[$seller_id]+$shipping_charges_arr[$seller_id];
                $totalamount = $item->getTotalAmount()+$tax_amount+$cod_charges_arr[$seller_id]+$shipping_charges_arr[$seller_id];

                $cod_charges_arr[$seller_id]= 0;
                $shipping_charges_arr[$seller_id]= 0;
                                
                $collectionverifyread = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();
                $collectionverifyread->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                if(count($collectionverifyread)>=1){
                    foreach($collectionverifyread as $verifyrow){
                        $totalsale=$verifyrow->getTotalSale()+$totalamount;
                        $totalremain=$verifyrow->getAmountRemain()+$actparterprocost;
                        $verifyrow->setTotalSale($totalsale);
                        $verifyrow->setAmountRemain($totalremain);
                        $verifyrow->setCommissionRate($percent);
                        $totalcommission=$verifyrow->getTotalCommission()+($totalamount-$actparterprocost);
                        $verifyrow->setTotalCommission($totalcommission);
                        $verifyrow->setUpdatedAt($this->_date->gmtDate());
                        $verifyrow->save();
                    }
                }
                else{
                    $percent = $helper->getConfigCommissionRate();          
                    $collectionf=$this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner');
                    $collectionf->setSellerId($seller_id);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setAmountRemain($actparterprocost);
                    $collectionf->setCommissionRate($percent);
                    $totalcommission=$totalamount-$actparterprocost;
                    $collectionf->setTotalCommission($totalcommission);
                    $collectionf->setCreatedAt($this->_date->gmtDate());
                    $collectionf->setUpdatedAt($this->_date->gmtDate());
                    $collectionf->save();                       
                }
                if($seller_id){
                    $ordercount = 0;
                    $feedbackcount = 0;
                    $feedcountid = 0;
                    $collectionfeed = $this->_objectManager->create('Webkul\Marketplace\Model\Feedbackcount')->getCollection()
                                    ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
                    foreach ($collectionfeed as $value) {
                        $feedcountid = $value->getEntityId();
                        $ordercount = $value->getOrderCount();
                        $feedbackcount = $value->getFeedbackCount();
                    }
                    $collectionfeed = $this->_objectManager->create('Webkul\Marketplace\Model\Feedbackcount')->load($feedcountid);
                    $collectionfeed->setBuyerId($order->getCustomerId());
                    $collectionfeed->setSellerId($seller_id);
                    $collectionfeed->setOrderCount($ordercount+1);
                    $collectionfeed->setFeedbackCount($feedbackcount);
                    $collectionfeed->setCreatedAt($this->_date->gmtDate());
                    $collectionfeed->setUpdatedAt($this->_date->gmtDate());
                    $collectionfeed->save();
                }
                $item->setUpdatedAt($this->_date->gmtDate());
                $item->setCpprostatus(1)->save();   
            }
        }
    }
}
