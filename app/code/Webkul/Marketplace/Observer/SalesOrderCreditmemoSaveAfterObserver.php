<?php
/**
 * Webkul Marketplace SalesOrderCreditmemoSaveAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class SalesOrderCreditmemoSaveAfterObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;    

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;        
        $this->_customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        $this->_date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditmemo = $observer->getCreditmemo();
        $order_id=$creditmemo->getOrderId();
        $order = $creditmemo->getOrder();

        $paymentCode = '';
        $payment_method = '';
        if($order->getPayment()){
            $paymentCode = $order->getPayment()->getMethod();
        }

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        // refund calculation check

        $adjustment_positive = $creditmemo['adjustment_positive'];
        $adjustment_negative = $creditmemo['adjustment_negative'];
        if($adjustment_negative > $adjustment_positive){
            $adjustment_negative = $adjustment_negative - $adjustment_positive;
        }else{
            $adjustment_negative = 0;
        }
        $creditmemo_items_ids = array();
        $creditmemo_items_qty = array();
        $creditmemo_items_price = array();
        foreach ($creditmemo->getAllItems() as $item) {                         
            $creditmemo_items_ids[$item->getOrderItemId()] = $item->getProductId();
            $creditmemo_items_qty[$item->getOrderItemId()] = $item->getQty();
            $creditmemo_items_price[$item->getOrderItemId()] = $item->getPrice()*$item->getQty();
        }
        arsort($creditmemo_items_price);
        $creditmemoCommissionRateArr = array();
        foreach ($creditmemo_items_price as $key => $item) {
            $refunded_qty=$creditmemo_items_qty[$key];
            $refunded_price=$creditmemo_items_price[$key];
            $product_id=$creditmemo_items_ids[$key];
            $seller_products = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'order_id',
                                        ['eq' => $order_id]
                                    )
                                    ->addFieldToFilter(
                                        'order_item_id',
                                        ['eq' => $key]
                                    )
                                    ->addFieldToFilter(
                                        'mageproduct_id',
                                        ['eq' => $product_id]
                                    );
            foreach ($seller_products as $seller_product) {
                $updated_qty = $seller_product['magequantity']-$refunded_qty;
                if($adjustment_negative*1){
                    if($adjustment_negative >= $refunded_price){
                        $adjustment_negative = $adjustment_negative - $seller_product['total_amount'];
                        $updated_price = $seller_product['total_amount'];
                        $refunded_price=0;
                    }else{
                        $refunded_price=$refunded_price-$adjustment_negative;
                        $updated_price = $seller_product['total_amount'] - $refunded_price;
                        $adjustment_negative = 0;
                    }
                }else{
                    $updated_price = $seller_product['total_amount']-$refunded_price;
                }
                if(!($seller_product['total_amount']*1)){
                    $seller_product['total_amount'] = 1;
                }
                if($seller_product['total_commission']*1){
                    $commission_percentage = ($seller_product['total_commission']*100)/$seller_product['total_amount'];
                }
                else{
                    $commission_percentage = 0;
                }
                if(empty($creditmemoCommissionRateArr[$key])){
                    $creditmemoCommissionRateArr[$key] = array();
                }
                $creditmemoCommissionRateArr[$key] = $seller_product->getData();
                $updated_commission = ($updated_price*$commission_percentage)/100;
                $updated_seller_amount = $updated_price-$updated_commission;

                if($updated_qty<0){
                    $updated_qty = 0;
                }
                if($updated_price<0){
                    $updated_price = 0;
                }
                if($updated_seller_amount<0){
                    $updated_seller_amount = 0;
                }
                if($updated_commission<0){
                    $updated_commission = 0;
                }
                if($refunded_qty){
                    $tax_amount = ($seller_product['total_tax']/$seller_product['magequantity'])*$refunded_qty;
                    $remain_tax_amount = $seller_product['total_tax']-$tax_amount;
                }else{
                    $tax_amount = 0;
                    $remain_tax_amount = 0;
                }
                if(!$helper->getConfigTaxManage()){
                    $tax_amount = 0;
                }
                $refunded_price=$refunded_price+$tax_amount;    
                $partner_remain_seller = ($seller_product->getActualSellerAmount()+$tax_amount)-$updated_seller_amount;
                
                $seller_arr[$seller_product['seller_id']]['updated_commission'] = $updated_commission;
                if(!isset($seller_arr[$seller_product['seller_id']])){
                    $mpcod_seller_coll[$seller_product['seller_id']]=array();
                }
                if($seller_product['cpprostatus']==1 && $seller_product['paid_status']==0){
                    if(!isset($seller_arr[$seller_product['seller_id']]['total_sale'])){
                        $seller_arr[$seller_product['seller_id']]['total_sale'] = 0;
                    }
                    if(!isset($seller_arr[$seller_product['seller_id']]['totalremain'])){
                        $seller_arr[$seller_product['seller_id']]['totalremain'] = 0;
                    }
                    if(!isset($seller_arr[$seller_product['seller_id']]['totalcommission'])){
                        $seller_arr[$seller_product['seller_id']]['totalcommission'] = 0;
                    }
                    $seller_arr[$seller_product['seller_id']]['total_sale'] = $seller_arr[$seller_product['seller_id']]['total_sale']+$refunded_price;
                    $seller_arr[$seller_product['seller_id']]['totalremain'] = $seller_arr[$seller_product['seller_id']]['totalremain']+$partner_remain_seller;
                    $seller_arr[$seller_product['seller_id']]['totalcommission'] = $seller_arr[$seller_product['seller_id']]['totalcommission']+($refunded_price-$partner_remain_seller);
                }else if($seller_product['cpprostatus']==1 && $seller_product['paid_status']==1){
                    if(!isset($seller_arr[$seller_product['seller_id']]['total_sale'])){
                        $seller_arr[$seller_product['seller_id']]['total_sale'] = 0;
                    }
                    if(!isset($seller_arr[$seller_product['seller_id']]['totalpaid'])){
                        $seller_arr[$seller_product['seller_id']]['totalpaid'] = 0;
                    }
                    if(!isset($seller_arr[$seller_product['seller_id']]['totalcommission'])){
                        $seller_arr[$seller_product['seller_id']]['totalcommission'] = 0;
                    }
                    $seller_arr[$seller_product['seller_id']]['total_sale'] = $seller_arr[$seller_product['seller_id']]['total_sale']+$refunded_price;
                    $seller_arr[$seller_product['seller_id']]['totalpaid'] = $seller_arr[$seller_product['seller_id']]['totalpaid']+$partner_remain_seller;
                    $seller_arr[$seller_product['seller_id']]['totalcommission'] = $seller_arr[$seller_product['seller_id']]['totalcommission']+($refunded_price-$partner_remain_seller);
                }
                $seller_product->setMagequantity($updated_qty);
                $seller_product->setTotalAmount($updated_price);
                $seller_product->setTotalCommission($updated_commission);
                $seller_product->setActualSellerAmount($updated_seller_amount);
                $seller_product->setTotalTax($remain_tax_amount);
                if($updated_seller_amount==0){
                    $seller_product->setPaidStatus(3);
                    if($paymentCode == 'mpcashondelivery'){
                        $seller_product->setCollectCodStatus(3);
                    }
                }
                $seller_product->save();
            }
        }
        $this->_customerSession->setMpCreditmemoCommissionRate($creditmemoCommissionRateArr);

        if(!isset($seller_arr)){
            $seller_arr = array();
        }

        foreach ($seller_arr as $seller_id => $value) {
            $shipping_charges = 0;
            $cod_charges = 0;
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
                if($paymentCode == 'mpcashondelivery'){
                    $cod_charges = $tracking->getCodCharges();
                }                
                $shipping_charges = $tracking->getShippingCharges();
            }
            if($shipping_charges>=$creditmemo['shipping_amount']){
                $shipping_charges = $creditmemo['shipping_amount'];
                $creditmemo['shipping_amount']=0;
            }else{
                $creditmemo['shipping_amount']=$creditmemo['shipping_amount']-$shipping_charges;
            }
            $collectionverifyread = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection();

            $collectionverifyread->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            foreach($collectionverifyread as $verifyrow){
                if(isset($seller_arr[$seller_id]['total_sale'])){
                    $verifyrow->setTotalSale($verifyrow->getTotalSale()-($seller_arr[$seller_id]['total_sale']+$cod_charges+$shipping_charges));
                }
                if(isset($seller_arr[$seller_id]['totalremain'])){
                    $verifyrow->setAmountRemain($verifyrow->getAmountRemain()-($seller_arr[$seller_id]['totalremain']+$cod_charges+$shipping_charges));
                }
                if(isset($seller_arr[$seller_id]['totalpaid'])){
                    $verifyrow->setAmountReceived($verifyrow->getAmountReceived()-($seller_arr[$seller_id]['totalpaid']+$cod_charges+$shipping_charges));
                }
                if(isset($seller_arr[$seller_id]['totalcommission'])){
                    $verifyrow->setTotalCommission($verifyrow->getTotalCommission()-$seller_arr[$seller_id]['totalcommission']);
                }
                $verifyrow->save();
            }
        }
    }
}
