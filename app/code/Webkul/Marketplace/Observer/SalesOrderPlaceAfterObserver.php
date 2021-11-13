<?php
/**
 * Webkul Marketplace SalesOrderPlaceAfterObserver Observer Model
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
use Zend\Log\Writer\Stream;
use Zend\Log\Logger;

class SalesOrderPlaceAfterObserver implements ObserverInterface
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
	
	public function send_push_notifications($deviceToken="",$deviceType="",$notification_type="",$merchant_id="",$order_id="",$store_id=""){
		
		$server_key			=	'AAAA4L5f9TI:APA91bGMqnokL19jIlMbzTFLTqNqC3rBeciPEMuN6ko9F0d8Wt2eQzW6l3d570Bw4zZCKujDU63tF4rDm4oj-vOM-nxv2u9BK1WXQP_qtfaVYuEeY5BJwIX_V9_PFo7yfy06QUmbKYpt';
		
		$msg =  '';
		if($store_id == 1)
		{
			$msg = "Your Order has been Successfully placed";
		}
		else
		{
			$msg = "تم تقديم طلبك بنجاح";
		}
		
		
		if(strtolower($deviceType) == 'ios')
		{
			$ch 			= 	curl_init("https://fcm.googleapis.com/fcm/send");
			$title 			= 	'ELOCAL';
			$notification 	= 	array(
										'title' 			=>	$title,
										'text' 				=> 	$msg,
										'message' 			=> 	$msg,
										'body' 				=> 	$msg,
										'vibrate'			=> 	1,
										'sound'				=> 	1, 
										'merchant_id'		=> 	$merchant_id, 
										'order_id'			=> 	$order_id, 
										//'response_data'		=> 	$data, 
										'notification_type'	=> 	$notification_type
									);

			//This array contains, the token and the notification. The 'to' attribute stores the token.
			$arrayToSend 	= 	array('to' => $deviceToken, 'notification' => $notification,'priority'=>'high');

			//Generating JSON encoded string form the above array.
			$json 		= 	json_encode($arrayToSend);
			
			//Setup headers:
			$headers 	= 	array();
			$headers[] 	= 	'Content-Type: application/json';
			$headers[] 	= 	"Authorization: key= $server_key"; // key here

			//Setup curl, add headers and post parameters.
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");                                                                     
			curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);       

			//Send the request
			$response = curl_exec($ch);
			//prd($response);	
			//Close request
			curl_close($ch);
			return true;
			
		}
		else
		{
			$registrationIds 	= 	array($deviceToken);
			$msg 				= 	array (
										'message'			=> $msg,
										'title'				=> "ELOCAL", 
										'vibrate'			=> 1,
										'sound'				=> 1, 
										'merchant_id'		=> $merchant_id, 
										'order_id'			=> $order_id, 
										//'response_data'		=> $data, 
										'notification_type'	=> $notification_type, 
									);						
			$fields 			= 	array (
										'registration_ids' 	=> $registrationIds,
										'data'				=> $msg
									);
									
			$headers 			= 	array (
										'Authorization: key=' . $server_key,
										'Content-Type: application/json'
									);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt($ch,CURLOPT_POST, true );
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt($ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( $fields) );
			$result = curl_exec($ch);
			curl_close( $ch );
			return true;
		}
	}
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test_order.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('start------');

        $order = $observer->getOrder();
		$lastOrderId = $observer->getOrder()->getId();
		$customer_id = $order->getCustomerId();
		$store_id = $order->getStoreId();
		file_put_contents(__DIR__."/user_data.txt", print_r($store_id, true));
		//echo $store_id; exit;
		$loaded_customer = $this->_objectManager->create('\Magento\Customer\Model\Customer')->load($customer_id);
		$device_token = $loaded_customer->getDeviceToken();
		$device_type = $loaded_customer->getDeviceType();
		if($device_token != ''){
		    $notification_type="ordercreated"; 
		    $this->send_push_notifications($device_token,$device_type,$notification_type,$customer_id,$lastOrderId,$store_id);
		}
		//$loaded_customer->setDeviceType('bhbhbhbhbh')->save();
        /** @var $orderInstance Order */
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
        $this->getProductSalesCalculation($order);
        $this->saveCustomerLocationSessionData($order);
        $this->save_delivery_date_option($observer);

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
        // $shippingamount = 0;
        // $discountamount = 0;
        $shippingamount = $order->getShippingAmount();
        $discountamount = $order->getDiscountAmount();

        $billaddress = $this->_objectManager->create('Magento\Sales\Model\Order\Address')->load($billingId);
        $billinginfo = $billaddress['firstname'].'<br/>'.$billaddress['street'].'<br/>'.$billaddress['city'].' '.$billaddress['region'].' '.$billaddress['postcode'].'<br/>'.$this->_objectManager->create('Magento\Directory\Model\Country')->load($billaddress['country_id'])->getName().'<br/>T:'.$billaddress['telephone'];  
                
        $payment = $order->getPayment()->getMethodInstance()->getTitle();
		$path 	=	__DIR__."/logs";
		// file_put_contents($path."/order_get".time().".txt", print_r($lastOrderId, true));
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

                            $logger->info('lastOrderId === ' . $lastOrderId);

		$manager_sent_email 	=	false;
        foreach($seller_order as $info){
			$logger->info('saler details------');
            $logger->info(print_r($info->debug(),true));

			// file_put_contents($path."/order_info_seller".time().".txt", print_r($info['seller_id'], true));
            if($info->getSellerId()){
                $userdata = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($info->getSellerId());
                
                $logger->info("====userdata====");
                $logger->info(print_r($userdata->debug(),true));

                $username =  $userdata->getFirstname();
                $useremail = $userdata->getEmail();

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

                $logger->info("====info mail====");
                $logger->info(print_r($receiverInfo,true));
                $logger->info(print_r($senderInfo,true));
                $logger->info(print_r($admininfo,true));

                $logger->info("====end info mail====");

                $totalprice =0;
                $totaltax_amount= 0;
                $cod_charges= 0;
                $shipping_charges= 0;
                $orderinfo = '';

                $saleslist_ids = array();
                $collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                                    ->getCollection();
                $collection1->addFieldToFilter('order_id',array('eq'=>$lastOrderId));
                $collection1->addFieldToFilter('seller_id',array('eq'=>$info->getSellerId()));
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
                    $totalprice = $totalprice + $res['magepro_price']*$res['magequantity'];

                    /*
                    * Low Stock Notification mail to seller
                    */
                    // if($helper->getlowStockNotification()){                 
                        $stock_item_qty = $product['quantity_and_stock_status']['qty'];
                        if($stock_item_qty<=1){
                            $order_product_info = "<tbody><tr>
                                    <td class='item-info'>".$product_name."</td>
                                    <td class='item-info'>".$sku. "<br /> Barcode:". $barcode ."</td>
                                    <td class='item-qty'>".($res['magequantity']*1)."</td>
                                 </tr></tbody>";

                            $emailTemplateVariables = array();
                            $emailTemplateVariables['myvar1'] = $order_product_info;
                            $emailTemplateVariables['myvar2'] = $username;

                            $logger->info('1111111111111111');
                            $logger->info('email var...');
                            $logger->info(print_r($emailTemplateVariables,true));
                            $logger->info('sender==...');
                            $logger->info(print_r($senderInfo,true));

                            $logger->info('receiverInfo==...');
                            $logger->info(print_r($receiverInfo,true));

                            $logger->info('admininfo==...');
                            $logger->info(print_r($admininfo,true));

                            $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendLowStockNotificationMail(
                                    $emailTemplateVariables,
                                    $senderInfo,
                                    $receiverInfo
                                );

                            $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendLowStockNotificationMail(
                                    $emailTemplateVariables,
                                    $senderInfo,
                                    $admininfo
                                );
                        }
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

                $logger->info('222222222==...');
                $logger->info(print_r($emailTempVariables,true));
                $logger->info(print_r($senderInfo,true));
                $logger->info(print_r($receiverInfo,true));
				
                $this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendPlacedOrderEmail(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo
                    );
				
				// email for location manager
				$location_managers 	=	$this->getLocationManagerByOrderId($lastOrderId, $observer);
				$logger->info("=====location_managers=====");
                $logger->info(print_r($location_managers, true));
				
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

                        $logger->info('===sellerId==');
                        $logger->info($sellerId);

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
                        //echo $order->formatPrice($res['magepro_price']*$res['magequantity']); 
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
						$totalprice = $totalprice + ($res['magepro_price']*$res['magequantity']);//$totalprice+($res['magepro_price']*$res['magequantity']);
					}
					
					$shipping_charges = $shippingamount + $info->getShippingCharges();
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
											<th colspan='3'>".__('Discount Amount')."</th>
											<td colspan='3'><span>".$order->formatPrice($discountamount)."</span></td>
										</tr>
										<tr class='subtotal'>
											<th colspan='3'>".__('Tax Amount')."</th>
											<td colspan='3'><span>".$order->formatPrice($totaltax_amount)."</span></td>
										</tr>".$cod_row."
										<tr class='subtotal'>
											<th colspan='3'>".__('Grandtotal')."</th>
											<td colspan='3'><span>".$order->formatPrice($totalprice+$totaltax_amount+$shipping_charges+$total_cod+$discountamount)."</span></td>
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

                            $logger->info('333333333333==...');
                            $logger->info(print_r($emailTempVariables,true));
                            $logger->info(print_r($senderInfo,true));
                            $logger->info(print_r($receiverInfo,true));
							
							$this->_objectManager->get('Webkul\Marketplace\Helper\Email')->sendPlacedOrderEmailToManager(
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
	
	
	
    public function getProductSalesCalculation($order)
    {
        /*
        * Marketplace Order details save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_before',
            ['order' => $order]
        );
		
        /*
        * Get Global Commission Rate for Admin
        */

        $helper 	= 	$this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        $percent 	= 	$helper->getConfigCommissionRate();

        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyCode = $helper->getCurrentCurrencyCode();
        $baseCurrencyCode = $helper->getBaseCurrencyCode();     
        $allowedCurrencies = $helper->getConfigAllowCurrencies(); 
        $rates = $helper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
        if(empty($rates[$currentCurrencyCode])){
            $rates[$currentCurrencyCode] = 1;
        }

        $lastOrderId=$order->getId();

        /*
        * Marketplace Credit Management module Observer
        */
        $this->_eventManager->dispatch(
            'mp_discount_manager',
            ['order' => $order]
        );
        /*
        * Marketplace Credit discount data
        */
        $discountDetails = array();
        $discountDetails = $this->_customerSession->getData('salelistdata');

        $this->_eventManager->dispatch(
            'mp_advance_commission_rule',
            ['order' => $order]
        );

        $advanceCommissionRule = $this->_customerSession->getData('advancecommissionrule');

        $seller_pro_arr=array();
        foreach ($order->getAllItems() as $item){
            $item_data = $item->getData();
            $attrselection = unserialize(serialize($item_data['product_options']));
            $bundle_selection_attributes = array();
            if(isset($attrselection['bundle_selection_attributes'])){
                $bundle_selection_attributes = unserialize(serialize($attrselection['bundle_selection_attributes']));
            }else{
                $bundle_selection_attributes['option_id']=0;
            }
            if(!$bundle_selection_attributes['option_id']){         
                $temp=$item->getProductOptions();
                if (array_key_exists('seller_id', $temp['info_buyRequest'])) {
                    $seller_id= $temp['info_buyRequest']['seller_id'];
                }
                else {
                    $seller_id='';
                }               
                if($discountDetails[$item->getProductId()]) 
                    $price = $discountDetails[$item->getProductId()]['price']/$rates[$currentCurrencyCode];
                else
                    $price = $item->getPrice()/$rates[$currentCurrencyCode];
                if($seller_id==''){
                    $collection_product=$this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'mageproduct_id',
                                            ['eq' => $item->getProductId()]
                                        );
					$product_tax	=	0;
                    foreach($collection_product as $value){
						$seller_id				=	$value->getSellerId();
                    }
					$product_commission		=	$item->getProduct()->getData('commission');
					$product_tax			=	$item->getProduct()->getData('tax');
                }
                if($seller_id==''){
                    $seller_id=0;
                }
                $collection1 = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $seller_id]
                );    
                $taxamount=$item_data['tax_amount'];
                $qty=$item->getQtyOrdered();

                $totalamount	=	$qty * $price;
               
				// commented by bharat start
                /* if(count($collection1)!=0){
                    foreach($collection1 as $rowdatasale) {
                        $commission=($totalamount*$rowdatasale->getCommissionRate())/100;
                    }
                } */
				// commented by bharat end
				
				if($product_tax!="" && $product_commission!=""){
					// Old method
					// $commission=(($totalamount-$product_tax)*$product_commission)/100;
					
					// New Method
					// (Product price / (1+tax%) ) * commission rate
					// Example tax is 10%
					// 164/1.1=149.09
					// 149.09*20%= 20 commission		=	29.818
					
					$commission		=	(($totalamount/(1 + ($product_tax/100))) * ($product_commission)/100);
					
				} else{
                    $commission		=	($totalamount*$percent)/100;
					
                }   
                
                if(!$helper->getUseCommissionRule()) {      
                    $wholedata['id'] = $item->getProductId();
                    $this->_eventManager->dispatch(
                        'mp_advance_commission',
                        [$wholedata]
                    );

                    $advancecommission = $this->_customerSession->getData('commission');
                    if($advancecommission!=''){
                        $percent=$advancecommission;
                        $commType = $helper->getCommissionType();
                        if($commType=='fixed')
                        {
                            $commission=$percent;
                        }
                        else
                        {
                            $commission=($totalamount*$advancecommission)/100;
                        }     
                        if($commission>$totalamount){ $commission= $totalamount*$helper->getConfigCommissionRate()/100; }
                    }   
                } else {
                    if(count($advanceCommissionRule)) {
                        if($advanceCommissionRule[$item->getId()]['type'] == 'fixed') {
                            $commission = $advanceCommissionRule[$item->getId()]['amount'];
                        } else {
                            $commission = ($totalamount * $advanceCommissionRule[$item->getId()]['amount']) / 100;
                        }   
                    }
                }       
                $actparterprocost=$totalamount-$commission;

                $collectionsave = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist'); 
                $collectionsave->setMageproductId($item->getProductId());
                $collectionsave->setOrderItemId($item->getItemId());
                $collectionsave->setParentItemId($item->getParentItemId());
                $collectionsave->setOrderId($lastOrderId);
                $collectionsave->setMagerealorderId($order->getIncrementId());
                $collectionsave->setMagequantity($qty);
                $collectionsave->setSellerId($seller_id);
                $collectionsave->setCpprostatus(0);
                $collectionsave->setMagebuyerId($this->_customerSession->getCustomerId());
                $collectionsave->setMageproPrice($price);
                $collectionsave->setMageproName($item->getName());
                if($totalamount!=0){
                    $collectionsave->setTotalAmount($totalamount);
                }
                else{
                    $collectionsave->setTotalAmount($price);
                }
                $collectionsave->setTotalTax($taxamount);
                $collectionsave->setTotalCommission($commission);
                $collectionsave->setActualSellerAmount($actparterprocost);
                $collectionsave->setCreatedAt($this->_date->gmtDate());
                $collectionsave->setUpdatedAt($this->_date->gmtDate());
                $collectionsave->save();
                $qty='';
                if($price!=0.0000){
                    if(!isset($seller_pro_arr[$seller_id])){
                        $seller_pro_arr[$seller_id]=array();
                    }
                    array_push($seller_pro_arr[$seller_id], $item->getProductId());
                }
            }
        }
        foreach($seller_pro_arr as $key=>$value){
            $product_ids=implode(',',$value);           
            $data=array(
                        'order_id'=>$lastOrderId,
                        'product_ids'=>$product_ids,
                        'seller_id'=>$key
                    );
            $shippingAll=$this->_customerSession->getData('shippinginfo');
			//if(is_array($shippingAll) && !sizeof($shippingAll) && $key==0){
			if($key==0){
                $ShippingCharges = $order->getShippingAmount();
                $data=array(
                        'order_id'=>$lastOrderId,
                        'product_ids'=>$product_ids,
                        'seller_id'=>$key,
                        'shipping_charges'=>$ShippingCharges
                    );
            }
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Orders'); 
            $collection->setData($data);            
            $collection->setCreatedAt($this->_date->gmtDate());
            $collection->setUpdatedAt($this->_date->gmtDate());
            $collection->save();
        }    
        /*
        * Marketplace Order details save after Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_after',
            ['order' => $order]
        );
    }
	
	// only from admin side
	function save_delivery_date_option($observer){
		
		if($this->_state->getAreaCode()=="adminhtml"){
			
			$order 				= 	$observer->getOrder();
			$parent_order_id 	=	$order->getRelationParentId();
			if($parent_order_id !=""){
				$parent_order 	= 	$this->_objectManager->create('Magento\Sales\Model\Order')->load($parent_order_id);
				$current_order 	= 	$this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getId());
				
				$current_order->setDeliveryDate( $parent_order->getDeliveryDate());
				$current_order->setDeliveryComment( $parent_order->getDeliveryComment());
				$current_order->setReplacements( $parent_order->getReplacements());
				$current_order->save();
				return $current_order;
			}
		}
	}
	
	public function saveCustomerLocationSessionData($order)
    {
		
		if($this->_state->getAreaCode()!="adminhtml"){
			
			$search_data 		=	json_decode($this->_request->getContent(),true);
			// check for API data
			if(!empty($search_data) && isset($search_data['cityId']) && isset($search_data['areaId'])){
				$city_id 	= 	$search_data['cityId'];
				$area_id 	= 	$search_data['areaId'];
				$this->saveLocationDataByOrderId($order->getId(), ['region_id'=>$city_id, 'area_id'=>$area_id]);
			}else{
				$area_id 	= 	$this->_customerSession->getAreaId();
				$city_id 	= 	$this->_customerSession->getCityId();
			}
			$customerId		=	$order->getCustomerId();
			
			$customer = $this->customerFactory->create()->load($customerId);
			$customerData = $customer->getDataModel();
			
			$customerData->setCustomAttribute('session_area_id', $area_id);
			$customerData->setCustomAttribute('session_region_id', $city_id);
			$customer->updateData($customerData);
			$customer->save();
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
		
		$location_data = $this->getLocationDataByOrderId($orderID);
		if($this->_state->getAreaCode()=="adminhtml"){
			$location_data 			=	$this->getCustomersLastSessionLocation($observer);	
			$this->saveLocationDataByOrderId($orderID, $location_data);
		}
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('customer_entity');
		$customerid 	= 	$this->_customerSession->getCustomerId();
		if($location_data['area_id'] == ''){
			$area_id = 157;
		}else{
			$area_id = $location_data['area_id'];
		}
		$country_id = $location_data['country_id'];
		$region_id = $location_data['region_id'];
		$sql = 	"Select entity_id  FROM " . $tableName ." WHERE FIND_IN_SET(".$area_id.", area_id) AND region_id='".$location_data['region_id']."' AND country_id='".$location_data['country_id']."';";

		$result = $connection->fetchAll($sql);
		$locationmanagers	=	array();
		if(!empty($result)){
			foreach($result as $index=>$row){
				$userdata = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($row['entity_id']);              
				$locationmanagers[]	= array(
					'firstname'=> $userdata->getFirstname(),
					'lastname'=> $userdata->getLastname(),
					'name'=> $userdata->getFirstname() ." " . $userdata->getFirstname(),
					'email'=> $userdata->getEmail(),
					'id'=>$row['entity_id'],
				);
			}
		}
		return $locationmanagers;
	}
	
	function saveLocationDataByOrderId($orderID, $locationdata){
		
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		
		$sql 			= 	"UPDATE " . $tableName ." SET region_id='".$locationdata['region_id']."', area_id ='".$locationdata['area_id']."' WHERE sales_order_address.parent_id=".$orderID."";
		
		return $connection->query($sql);
	}
	
	function getLocationDataByOrderId($orderID){
		
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		$sql 			= 	"SELECT region_id, area_id, country_id FROM " . $tableName ." WHERE sales_order_address.parent_id=".$orderID."";
		$result 		= 	$connection->fetchAll($sql);
		$orders 		=	array(); 		
		$region_id		=	'';
		$area_id		=	'';
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
	
	public function getSellerDetailsById($seller_id){
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