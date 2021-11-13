<?php
/**
 * Webkul Marketplace SalesOrderInvoiceSaveAfterObserver Observer Model
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Magento\Framework\UrlInterface;

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
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
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param UrlInterface $urlBuilder
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        UrlInterface $urlBuilder,
        CollectionFactory $collectionFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->collectionFactory = $collectionFactory;
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
        $seller_items_array = array();
        $invoice_seller_ids = array();   

        $order = $observer->getOrder();
        $lastOrderId = $order->getId();
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $event = $observer->getInvoice();              

        foreach ($event->getAllItems() as $value) {
            $invoiceproduct = $value->getData();
            $pro_seller_id = 0;
            $product_seller = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                ['eq' => $invoiceproduct['product_id']]
            );
            foreach ($product_seller as $sellervalue) {
                if($sellervalue->getSellerId()){
                    //array_push($invoice_seller_ids, $sellervalue->getSellerId());
                    $invoice_seller_ids[$sellervalue->getSellerId()] = $sellervalue->getSellerId();
                    $pro_seller_id = $sellervalue->getSellerId();         
                }
            }
            // $order_collection=$this->_objectManager->create('Webkul\Marketplace\Model\Orders')
            // ->getCollection()
            // ->addFieldToFilter(
            //     'order_id',
            //     ['eq' => $event->getOrderId()]
            // )->addFieldToFilter(
            //     'item_ids',
            //     ['in' => $invoiceproduct['product_id']]
            // );
            // foreach ($order_collection as $order_coll) {
            //     $order_coll->setInvoiceId($event->getId());
            //     $order_coll->save();
            // }
            if($pro_seller_id){
                $seller_items_array[$pro_seller_id][] = $invoiceproduct;
            }
        }

        /*send placed order mail notification to seller*/

        $sales_order = $this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('sales_order');
        $sales_order_item = $this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('sales_order_item');

        $paymentCode = '';
        if($order->getPayment()){
            $paymentCode = $order->getPayment()->getMethod();
        }

        $shipping_info = '';
        $shipping_des = '';

        $billingId = $order->getBillingAddress()->getId();

        $billaddress = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($billingId);
        $billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.$this->_objectManager->create('Magento\Directory\Model\Country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];  
                
        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        if($order->getShippingAddress()){
            $shippingId = $order->getShippingAddress()->getId();
            $address = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($shippingId);                
            $shipping_info = $address['firstname'].'<br/>'.$address['street'].'<br/>'.$address['city'].' '.$address['region'].' '.$address['postcode'].'<br/>'.$this->_objectManager->create('Magento\Directory\Model\Country')->load($address['country_id'])->getName().'<br/>T:'.$address['telephone'];    
            $shipping_des = $order->getShippingDescription();
        }

        $admin_storemail = $helper->getAdminEmailId();
        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
        $adminUsername = 'Admin';
                
        $seller_order = $this->_objectManager->create('Webkul\Marketplace\Model\Orders')
                            ->getCollection()
                            ->addFieldToFilter('seller_id',array('in'=>$invoice_seller_ids))
                            ->addFieldToFilter('order_id',$lastOrderId);
        foreach($seller_order as $info){
            if($info['seller_id']!=0){
                $userdata = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($info['seller_id']);              
                $username =  $userdata['firstname'];
                $useremail = $userdata['email'];

                $senderInfo = array();
                $receiverInfo = array();
                
                $receiverInfo = [
                    'name' => $username,
                    'email' => $useremail,
                ];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $totalprice = 0;
                $totaltax_amount= 0;
                $cod_charges= 0;
                $shipping_charges= 0;
                $orderinfo = '';

                $saleslist_ids = array();
                $collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection();
                $collection1->addFieldToFilter('order_id',array('eq'=>$lastOrderId));
                $collection1->addFieldToFilter('seller_id',array('eq'=>$info['seller_id']));
                $collection1->addFieldToFilter('parent_item_id',array('null' => 'true' ));
                $collection1->addFieldToFilter('magerealorder_id',array('neq'=>'0'));
                foreach ($collection1 as $value) {
                  array_push($saleslist_ids, $value['entity_id']);
                }

                $fetchsale = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                ->getCollection()
                                ->addFieldToFilter('entity_id',array('in'=>$saleslist_ids));
                $fetchsale->getSelect()->join($sales_order.' as so','main_table.order_id = so.entity_id', array("status" => "status"));

                $fetchsale->getSelect()
                ->join($sales_order_item.' as soi','main_table.order_item_id = soi.item_id AND main_table.order_id = soi.order_id',array("item_id" => "item_id","qty_canceled"=>"qty_canceled","qty_invoiced"=>"qty_invoiced","qty_ordered"=>"qty_ordered","qty_refunded"=>"qty_refunded","qty_shipped"=>"qty_shipped","product_options"=>"product_options","mage_parent_item_id"=>"parent_item_id"));
                foreach ($fetchsale as $res) {  
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($res['mageproduct_id']);

                    /* product name */
                    $product_name = $res->getMageproName();
                    $result = array();
					$output = array();
					$string = trim(preg_replace('/\s\s+/', ' ',$res->getProductOptions()));
					$string = preg_replace_callback('!s:(\d+):"(.*?)";!', function($m) { return 's:'.strlen($m[2]).':"'.$m[2].'";'; }, utf8_encode( trim(preg_replace('/\s\s+/', ' ',$string)) ));
					
                    if ($options = json_decode($string, true)) {
                        if (isset($options['options'])) {
                            $result = array_merge($result, $options['options']);
                        }
                        if (isset($options['additional_options'])) {
                            $result = array_merge($result, $options['additional_options']);
                        }
                        if (isset($options['attributes_info'])) {
                            $result = array_merge($result, $options['attributes_info']);
                        }
                    }
                    if($_options = $result){        
                        $pro_option_data = '<dl class="item-options">';
                        foreach ($_options as $_option) {
                            $pro_option_data .= '<dt>'.$_option['label'].'</dt>';
                            
                            $pro_option_data .= '<dd>'.$_option['value'];
                            $pro_option_data .= '</dd>';
                        }
                        $pro_option_data .= "</dl>";
                        $product_name = $product_name."<br/>".$pro_option_data;
                    }else{
                        $product_name = $product_name."<br/>";
                    }
                    /* end */

                    $sku = $product->getSku();      
                    $orderinfo = $orderinfo."<tbody><tr>
                                    <td class='item-info'>".$product_name."</td>
                                    <td class='item-info'>".$sku."</td>
                                    <td class='item-qty'>".($res['magequantity']*1)."</td>
                                    <td class='item-price'>".$order->formatPrice($res['magepro_price']*$res['magequantity'])."</td>
                                 </tr></tbody>";
                    $totaltax_amount=$totaltax_amount + $res['total_tax'];
					//echo '<pre>'; echo $totalprice.'==='.$res['magequantity'].'==='; print_r($res['magepro_price']); die("testt");
                    $totalprice = $totalprice+($res['magepro_price']*$res['magequantity']);
                }
                $shipping_charges = $info->getShippingCharges();
                $total_cod = 0;                

                if($paymentCode == 'mpcashondelivery'){
                    $total_cod = $info->getCodCharges();
                    $cod_row = "<tr class='subtotal'>
                                <th colspan='3'>".__('Cash On Delivery Charges')."</th>
                                <td colspan='3'><span>".$order->formatPrice($total_cod)."</span></td>
                                </tr>";
                }else{
                    $cod_row = '';
                }

                $orderinfo = $orderinfo."<tfoot class='order-totals'>
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Shipping & Handling Charges')."</th>
                                        <td colspan='3'><span>".$order->formatPrice($shipping_charges)."</span></td>
                                    </tr>
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Tax Amount')."</th>
                                        <td colspan='3'><span>".$order->formatPrice($totaltax_amount)."</span></td>
                                    </tr>".$cod_row."
                                    <tr class='subtotal'>
                                        <th colspan='3'>".__('Grandtotal')."</th>
                                        <td colspan='3'><span>".$order->formatPrice($totalprice+$totaltax_amount+$shipping_charges+$total_cod)."</span></td>
                                    </tr></tfoot>";

                $emailTemplateVariables = array();
                if($shipping_info!=''){
                    $isNotVirtual = 1;
                }else{
                    $isNotVirtual = 0;
                }
                $emailTempVariables['myvar1'] = $order->getRealOrderId();
                $emailTempVariables['myvar2'] = $order['created_at'];
                $emailTempVariables['myvar4'] = $billinginfo;
                $emailTempVariables['myvar5'] = $payment;
                $emailTempVariables['myvar6'] = $shipping_info;
                $emailTempVariables['isNotVirtual'] = $isNotVirtual;
                $emailTempVariables['myvar9'] = $shipping_des;
                $emailTempVariables['myvar8'] = $orderinfo;
                $emailTempVariables['myvar3'] = $username;
                /* $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendInvoicedOrderEmail(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo
                    ); */
            }   
        }
        /*
        * Marketplace Order product sold Observer
        */
        $this->_eventManager->dispatch(
            'mp_product_sold',
            ['itemwithseller' => $seller_items_array]
        );
    }
}
