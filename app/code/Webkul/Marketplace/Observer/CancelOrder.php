<?php
/**
 * Webkul Marketplace OrderCancelAfterObserver Observer Model
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
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Data\Customer as CustomerData;

class CancelOrder implements ObserverInterface
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
	
	protected $_request;
	protected $_state;
	
	protected $_ordercollectionfactory;
	
	protected $customerFactory;
    protected $customerData;

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
        UrlInterface $urlBuilder,
		\Magento\Framework\App\RequestInterface $request,
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory ,
		\Magento\Framework\App\State $state,
		CustomerFactory $customerFactory,
		CustomerData $customerData
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_date = $date;
        $this->dateTime = $dateTime;
        $this->urlBuilder = $urlBuilder;
		
		$this->_request = $request;
		$this->_ordercollectionfactory = $orderCollectionFactory;
		$this->_state = $state;
		$this->customerFactory = $customerFactory;
		$this->customerData = $customerData;
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
                            ->addFieldToFilter('order_id',$lastOrderId);  
							
		$manager_sent_email	=	false;				
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
				$admininfo = [
                    'name' => $adminUsername,
                    'email' => "admin@scappery.com",
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
					$options	=	@unserialize($res->getProductOptions());
                    if ($options !== false) {
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

                    $sku 	= $product->getSku();      
                    $barcode = $product->getData('barcode');      
                    $orderinfo = $orderinfo."<tbody><tr>
                                    <td class='item-info'>".$product_name."</td>
                                    <td class='item-info'>".$sku. "<br /> Barcode:". $barcode ."</td>
                                    <td class='item-qty'>".($res['magequantity']*1)."</td>
                                    <td class='item-price'>".$order->formatPrice($res['magepro_price']*$res['magequantity'])."</td>
                                 </tr></tbody>";
                    $totaltax_amount=$totaltax_amount + $res['total_tax'];
                    $totalprice = 0;//$totalprice+($res['magepro_price']*$res['magequantity']);

                    /*
                    * Low Stock Notification mail to seller
                    */
                    // if($helper->getlowStockNotification()){                 
                        $stock_item_qty = $product['quantity_and_stock_status']['qty'];
                    // }
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
				$emailTempVariables['myvar10'] = $this->getOrderDeliveryDateHistory($lastOrderId);
				
				
				
                $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendOrderCancelEmailToVendor(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo
                    );
				
				// email for location manager
				$location_managers 	=	$this->getLocationManagerByOrderId($lastOrderId, $observer);
				
				if(!empty($location_managers)){
					$saleslist_ids = array();
					$orderinfo = '';
					$totalprice = 0;
					$totaltax_amount= 0;
					$cod_charges= 0;
					$shipping_charges= 0;
					$orderinfo = '';
					$collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
										->getCollection();
					$collection1->addFieldToFilter('order_id',array('eq'=>$lastOrderId));
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
						$sellerId = $res->getSellerId();
						$sellerData 	= 	$this->getSellerDetailsById($sellerId);
						$seller_name 	=	$sellerData->getShopUrl();
						$result = array();
						$options	=	@unserialize($res->getProductOptions());
						if ($options !== false) {
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
						$barcode = $product->getData('barcode');      
						$orderinfo = $orderinfo."<tbody><tr>
										<td class='item-info'>".$product_name."</td>
										<td class='item-info'>".ucfirst($seller_name)."</td>
										<td class='item-info'>".$sku. "<br /> Barcode:". $barcode ."</td>
										<td class='item-qty'>".($res['magequantity']*1)."</td>
										<td class='item-price'>".$order->formatPrice($res['magepro_price']*$res['magequantity'])."</td>
									 </tr></tbody>";
						$totaltax_amount=$totaltax_amount + $res['total_tax'];
						$totalprice = 0;//$totalprice+($res['magepro_price']*$res['magequantity']);
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
					$emailTempVariables['myvar10'] = $this->getOrderDeliveryDateHistory($lastOrderId);
					
					if(!$manager_sent_email){
						$manager_sent_email	=	true;
						foreach($location_managers as $location_manager){
							
							$emailTempVariables['myvar3'] = $location_manager['name'];
							
							$receiverInfo = [
								'name' => $location_manager['name'],
								'email' => $location_manager['email'],
							];
							$this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendOrderCancelEmailToManager(
									$emailTempVariables,
									$senderInfo,
									$receiverInfo
								);
						}
					}
				}   
            }   
        }
    }
	
	
	public function getCustomersLastSessionLocation($observer)
    {
		
		
		$post_data 			=	$this->_request->getPost();
		
		$customer_email 	=	$post_data->order['account']['email'];
		
		$userdata 			= 	$this->_objectManager->create('Magento\Customer\Model\Customer')->setWebsiteId(1)->loadByEmail($customer_email);   
	
		$customer_id 		=	$userdata->getId();
		
		return ['area_id'=>$userdata->getData('session_area_id'),'region_id'=>$userdata->getData('session_region_id'),'country_id'=>'AE'];
	}
	
	function getLocationManagerByOrderId($orderID, $observer){
		
		
		$location_data 			=	$this->getLocationDataByOrderId($orderID);
		
		// print_r($sql);
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('customer_entity');
		
		if($location_data['area_id']==""){
			$location_data['area_id']	=	0;
		}
		$sql 			= 	"Select entity_id  FROM " . $tableName ." WHERE FIND_IN_SET(".$location_data['area_id'].", area_id) AND region_id='".$location_data['region_id']."' AND country_id='".$location_data['country_id']."'";
		
		$result 		= 	$connection->fetchAll($sql);
		
		$locationmanagers	=	array();
		if(!empty($result)){
			foreach($result as $index=>$row){
				
				$userdata = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($row['entity_id']);              
				$locationmanagers[]	=	array(
											'firstname'=>$userdata['firstname'],
											'lastname'=>$userdata['lastname'],
											'name'=> $userdata['firstname'] ." " .$userdata['lastname'],
											'email'=>$userdata['email'],
											'id'=>$row['entity_id'],
										);
			}
		}
		return $locationmanagers;
	}
	
	
	function getLocationDataByOrderId($orderID){
		
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		
		$sql 			= 	"SELECT region_id, area_id, country_id FROM " . $tableName ." WHERE sales_order_address.parent_id=".$orderID."";
		$result 		= 	$connection->fetchAll($sql);
		$orders 		=	array(); 		
		
		$region_id		=	0;
		$area_id		=	0;
		$country_id		=	'';
		
		if(!empty($result)){
			foreach($result as $row){
					
				$region_id	=	$row['region_id'];
				$area_id	=	$row['area_id'];
				$country_id	=	$row['country_id'];
				break;
			}
		}
		return ['region_id'=>$region_id,'area_id'=>$area_id,'country_id'=>$country_id];
	}
	
	function getSellerDetailsById($seller_id){
		 $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            foreach($data as $seller){ return $seller;}
	}
	
	 public function getOrderDeliveryDateHistory($order_id){
		
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('sales_order'); //gives table name with prefix
		$sql = "Select `delivery_date` FROM " . $tableName . " where `entity_id`= ".$order_id;
		
		$result = $connection->fetchAll($sql); // gives associated array, table fields as key in array.
		if(isset($result[0]['delivery_date'])){
			return $result[0]['delivery_date'];
		}else{
			return false;
		}
	}
}