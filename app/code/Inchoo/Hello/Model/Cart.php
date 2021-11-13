<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\CartInterface;
use Magento\Backend\App\Action;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Magento\Checkout\Model\Cart as CustomerCart;
use \Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteRepository\SaveHandler;

class Cart implements CartInterface
{
	protected $_store;
	protected $_context;
	protected $_objectManager;
	protected $_quoteFactory;
	protected $cart;
	
	/**
	* @var \Magento\Quote\Model\QuoteIdMaskFactory
	*/
	protected $quoteIdMaskFactory;
	protected $quoteRepository;
	protected $saveHandler;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		\Magento\Quote\Model\QuoteFactory $quoteFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		CustomerCart $cart,
		\Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
		CartRepositoryInterface $quoteRepository,
		SaveHandler $saveHandler,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Catalog\Helper\Product\Configuration $configurationHelper,
		array $data = []
	) {
		$this->_context	=	$context;
		$this->_quoteFactory 				=   $quoteFactory;
		$this->cart = $cart;
		$this->_objectManager 				=   $objectManager;
		$this->customerRepository = $customerRepository;
		$this->quoteIdMaskFactory 			=	$quoteIdMaskFactory;
		$this->quoteRepository 				= 	$quoteRepository;
		$this->saveHandler 					= 	$saveHandler;
		$this->_store 						= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$this->request						=	$request;
		$this->configurationHelper = $configurationHelper;
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function getCart() {
		
		$customerId		=	$this->_context->getUserId(); /*get Login user detail*/
		
		
		$quote 										= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
		//$cart 										= 	$objectManager->get('\Magento\Checkout\Model\Cart');
		$cart_data['cartInfo']	 					=	$quote->getData();
		$cart_data['cartInfo']['shipping_data']		=	$quote->getShippingAddress()->collectShippingRates()->getData();
		// $cart_data['cartInfo']['payment']			=	$quote->getPayment();
		$shipping_data								=	$quote->getShippingAddress()->collectShippingRates()->getData();
		$discount_amount						 	=	0;
		$cart_items 	=	[];
		if($customerId && $quote){
			
			$allItems = $quote->getAllItems(); 
			
			//count($allItems); exit;
			
			$objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
			$date = $objDate->gmtDate();
			$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('eav_attribute_option_value');
			$eavtableName = $resource->getTableName('eav_attribute');
			/* $abc['selection_option']['value'] = array();
			foreach($allItems as $item) {
				if($item->getProductType() == 'simple'){
				    foreach ($item->getOptions() as $option) {
						 $itemOptions = json_decode($option['value'], true);
					   //$itemopt = json_decode($itemOptions, true);
					    if(isset($itemOptions['super_attribute'])){
							//echo '<pre>'; print_r($itemOptions['super_attribute']);
							foreach($itemOptions['super_attribute'] as $key => $superattribute) {
								echo $key; 
								$option_id = $superattribute;
								$sql = "select * FROM " . $tableName . " where option_id=".$option_id;
								$result = $connection->fetchAll($sql);
								$option_label = $result[0]['value'];
								$attributelabel = "select * FROM " . $eavtableName . " where attribute_id=".$key;
								$attrresult = $connection->fetchAll($attributelabel);
								$attr_label = $attrresult[0]['frontend_label'];
								$abc['selection_option']['value'][] = $attr_label.":-".$option_label;
								echo '<pre>'; print_r($abc);
							}
					    }
                     //itemOptions contain all the custom option of an item
                    }
				}
			}
			die("testttt"); */
			foreach($allItems as $item) {
				$product_color = ''; 
				$product_size =  '';
				$product_weight =  '';
				//$qty			=  0;
				
				if($item->getProductType() == 'configurable'){
					$qty 		= $item->getQty();
				}
				if($item->getProductType() == 'simple'){
					$product_id 		=	$item->getSku();
					$productImageUrl 	= 	$this->_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
					$product 			= 	$this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_id);
					
					$productinfo		=	$product->getData();
					
					//echo $item->getProductType(); 
					//echo "<hr/>";
					//if($item->getProductType() == 'simple'){
						foreach ($item->getOptions() as $option) {
							 $itemOptions = json_decode($option['value'], true);
						   //$itemopt = json_decode($itemOptions, true);
							if(isset($itemOptions['super_attribute'])){
								//echo '<pre>'; print_r($itemOptions['super_attribute']);
								foreach($itemOptions['super_attribute'] as $key =>  $superattribute) {
									//echo $superattribute; 
									$option_id = $superattribute;
									$sql = "select * FROM " . $tableName . " where option_id=".$option_id;
									$result = $connection->fetchAll($sql);
									$option_label = $result[0]['value'];
									$attributelabel = "select * FROM " . $eavtableName . " where attribute_id=".$key;
									$attrresult = $connection->fetchAll($attributelabel);
									$attr_label = $attrresult[0]['frontend_label'];
									$productinfo['selection_option']['value'][] = $attr_label.":-".$option_label;
									//echo '<pre>'; print_r($abc);
								}
							}
						 //itemOptions contain all the custom option of an item
						}
						//$productinfo['selected_attribute']
					if(isset($productinfo['selection_option']))
					{
						$item->setQty($qty);
					}
					//echo "<pre>"; print_r($product->getExtensionAttributes()); 
					/* if(isset($productinfo['product_weight']) && $productinfo['product_weight'] != '')
					{
						$product_weight = $product->getAttributeText('product_weight');
						$productinfo['product_weight'] =  $product_weight; //$product->setProductWeight($product_weight);
					}
					if(isset($productinfo['size']) && $productinfo['size']!= '')
					{
						$product_size = $product->getAttributeText('size');
						$productinfo['size'] = $product_size; //$product->setSize($product_size);
					}
					if(isset($productinfo['color']) &&  $productinfo['color'] != '') 
					{
						$product_color = $product->getAttributeText('color');
						$productinfo['color'] = $product_color;
						//$product->setColor($product_color);
					} */
					
					/* if(($productinfo['special_price']) && ($product->getSpecialToDate() < $date){
						$item->setPrice($product->getFinalPrice()); 
					} */
					if($product->getTypeId()!="simple"){
						
						$productinfo['special_price']	=	$product->getSpecialPrice(); 
						$productinfo['price']			=	$product->getFinalPrice(); 
						//$item->setPrice($product->getFinalPrice()); 
					}else{
						$productinfo['price']			=	$product->getPrice();  
						$productinfo['special_price']	=	$product->getFinalPrice(); 
					}
					if($productinfo['price']==$productinfo['special_price']){
						$productinfo['special_price']	=	null;
					}
					
					$productinfo['image']				=	ltrim($product->getImage(), "/"); 
					$productinfo['small_image']			=	ltrim($product->getSmallImage(), "/"); 
					$productinfo['thumbnail']			=	ltrim($product->getThumbnail(), "/"); 
					$cart_items[]		=	[
												'productinfo'=>$productinfo,
												'productImageUrl'=>$productImageUrl, 
												'cartiteminfo'=>$item->getData()
											]; 
					//$cart_items['cartiteminfo']['qty']  =   $qty;
				}
			}
		} //exit;
		
		$vendor_data 				=	[];
		$vendorwise_data 			=	[];
		if(!empty($cart_items)){
			foreach($cart_items as $cart_item){
				
				$shop_url			=	trim($cart_item['productinfo']['vendor']);
				
				if(!isset($vendor_data[$shop_url])){
					$data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
						->getCollection()
						->addFieldToFilter('shop_url',array('eq'=>$shop_url));
					foreach($data as $seller){ 
						$shop_url				=	$seller['shop_title'];
						if(!$seller['shop_title']){
							$customer_data 			= 	$this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller->getSellerId());
							$shop_url				=	trim($customer_data->getData('firstname')." ".$customer_data->getData('lastname'));
						}
						$vendor_data[$shop_url]	=	$seller->getData();	
					}
				}
				$vendorwise_data[$shop_url][]	=	$cart_item;	
			//$i++;
			}
		}
		
		$cartitems 							=	[];
		if(!empty($vendorwise_data)){
			foreach($vendorwise_data as $vendorname=>$vendor_data){
				$cartitems[]				=	[
													'vanderName'=> ucfirst($vendorname),
													'items'=> $vendor_data,
												];
			}
		}
		$cart_data['CartItem']	 								=	$cartitems;
		$cart_data['cartInfo']['discount_amount']	 			=	isset($shipping_data['discount_amount'])?$shipping_data['discount_amount']:0;
		$cart_data['cartInfo']['shipping_amount']	 			=	isset($shipping_data['shipping_amount'])?$shipping_data['shipping_amount']:0;
		if($cart_data['cartInfo']['discount_amount'] < 0 && $cart_data['cartInfo']['coupon_code']==null){
			$cart_data['cartInfo']['coupon_code']	 			=	"Discount";
		}
		//echo "<pre>"; print_r($cart_data['CartItem']); exit;
		
		return [$cart_data];
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function getCartForGuest($cartId){
		
		$quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
		
		$quote 						= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->load($quoteIdMask->getQuoteId());
		$cart_data['cartInfo']	 	=	$quote->getData();
		$shipping_data				=	$quote->getShippingAddress()->collectShippingRates()->getData();
		// $cart_data['cartInfo']['shipping_data']		=	$quote->getShippingAddress()->collectShippingRates()->getData();
		// $cart_data['cartInfo']['payment']			=	$quote->getPayment();
		$discount_amount			=	0;
		$cart_items 	=	[];
		if($quote){
			
			$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('eav_attribute_option_value');
			$eavtableName = $resource->getTableName('eav_attribute');
			
			$allItems = $quote->getAllItems();
			foreach($allItems as $item) {
				// $cart_items[] 	=	$item->getData();
				
				//echo $item->getProductType();
				if($item->getProductType() == 'configurable'){
					$qty 		= $item->getQty();
				}
				if($item->getProductType() == 'simple'){ 
				
				
					$product_id 		=	$item->getProductId();
					$productImageUrl 	= 	$this->_store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
					$product 			= 	$this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
					$productinfo		=	$product->getData();
					
					foreach ($item->getOptions() as $option) {
							 $itemOptions = json_decode($option['value'], true);
						   //$itemopt = json_decode($itemOptions, true);
							if(isset($itemOptions['super_attribute'])){
								//echo '<pre>'; print_r($itemOptions['super_attribute']);
								foreach($itemOptions['super_attribute'] as $key =>  $superattribute) {
									//echo $superattribute; 
									$option_id = $superattribute;
									$sql = "select * FROM " . $tableName . " where option_id=".$option_id;
									$result = $connection->fetchAll($sql);
									$option_label = $result[0]['value'];
									$attributelabel = "select * FROM " . $eavtableName . " where attribute_id=".$key;
									$attrresult = $connection->fetchAll($attributelabel);
									$attr_label = $attrresult[0]['frontend_label'];
									$productinfo['selection_option']['value'][] = $attr_label.":-".$option_label;
									//echo '<pre>'; print_r($abc);
								}
							}
						 //itemOptions contain all the custom option of an item
						}
						//$productinfo['selected_attribute']
					if(isset($productinfo['selection_option']))
					{
						$item->setQty($qty);
					}
					
					
					if($product->getTypeId()!="simple"){
						$productinfo['special_price']	=	$product->getSpecialPrice(); 
						$productinfo['price']			=	$product->getFinalPrice(); 
						//$item->setPrice($product->getFinalPrice()); 
					}else{
						$productinfo['price']			=	$product->getPrice();  
						$productinfo['special_price']	=	$product->getFinalPrice(); 
						//$item->setPrice($product->getFinalPrice());
					}
					if($productinfo['price']==$productinfo['special_price']){
						$productinfo['special_price']	=	null;
					}
					$productinfo['image']				=	ltrim($product->getImage(), "/"); 
					$productinfo['small_image']			=	ltrim($product->getSmallImage(), "/"); 
					$productinfo['thumbnail']			=	ltrim($product->getThumbnail(), "/"); 
					$cart_items[]		=	[
												'productinfo'=>$productinfo,
												'productImageUrl'=>$productImageUrl, 
												'cartiteminfo'=>$item->getData()
											];
				}
			} //exit;
		}
		
		
		$vendor_data 				=	[];
		$vendorwise_data 			=	[];
		if(!empty($cart_items)){
			foreach($cart_items as $cart_item){
				
				$shop_url			=	trim($cart_item['productinfo']['vendor']);
				if(!isset($vendor_data[$shop_url])){
					$data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
						->getCollection()
						->addFieldToFilter('shop_url',array('eq'=>$shop_url));
					foreach($data as $seller){ 
						$shop_url				=	$seller['shop_title'];
						if(!$seller['shop_title']){
							$customer_data 			= 	$this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller->getSellerId());
							$shop_url				=	trim($customer_data->getData('firstname')." ".$customer_data->getData('lastname'));
						}
						$vendor_data[$shop_url]	=	$seller->getData();	
					}
				}
				$vendorwise_data[$shop_url][]	=	$cart_item;	
			}
		}
		
		$cartitems 							=	[];
		if(!empty($vendorwise_data)){
			foreach($vendorwise_data as $vendorname=>$vendor_data){
				$cartitems[]				=	[
													'vanderName'=> ucfirst($vendorname),
													'items'=> $vendor_data,
												];
			}
		}
		$cart_data['CartItem']	 			=	$cartitems;
		$cart_data['cartInfo']['discount_amount']	 			=	isset($shipping_data['discount_amount'])?$shipping_data['discount_amount']:0;
		$cart_data['cartInfo']['shipping_amount']	 			=	isset($shipping_data['shipping_amount'])?$shipping_data['shipping_amount']:0;
		if($cart_data['cartInfo']['discount_amount'] < 0 && $cart_data['cartInfo']['coupon_code']==null){
			$cart_data['cartInfo']['coupon_code']	 			=	"Discount";
		}
		return [$cart_data];
    }
	
	/**
     * Returns greeting id to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting id with users name.
     */
    public function cartsid(){
		$customerId	=	$this->_context->getUserId();
		$cus_quote = $this->_quoteFactory->create()->loadByCustomer($customerId);
		//file_put_contents(__DIR__."/cart_data.txt", print_r($cus_quote->getId(), true));
		if($cus_quote->getId() != ''){
			$store = $this->_store;
			//file_put_contents(__DIR__."/cart_data2.txt", print_r($store->getId(), true));
		    $quote = $this->_objectManager->create('Magento\Quote\Model\Quote')->load($cus_quote->getId());
            $quote->setStoreId($store->getId());
			$quote->save(); 
		    return $cus_quote->getId();
		}else{
			
			$store = $this->_store;
			$store_id = $store->getId();
            //$cus_quote = $this->_quoteFactory->create()->loadByCustomer($customerId);
			//file_put_contents(__DIR__."/cart_data2.txt", print_r($cus_quote->getId(), true));
			/* $quote = $this->_objectManager->create('Magento\Quote\Model\Quote');
		    //$quote = $this->quoteRepository->get($cus_quote->getId());
            $quote->setStore();
			$quote->save(); */
			if($store_id == 1)
			{
				$url = 'https://elocal.ae/rest/V1/carts/mine' ;
			}
			else
			{
				$url = 'https://elocal.ae/rest/ar/V1/carts/mine' ;
			}
			
			
			$Authorization 		=	$this->request->getHeader('Authorization');
			$ch = curl_init($url);
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    	'Content-Type: application/json',
		    	'Authorization: '.$Authorization,
		    ));
		    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		    
		    //execute post
		    $result = curl_exec($ch);
		    
		    //close connection
		    curl_close($ch);
		    
		    $json = json_decode($result, true);
			return $json;
		}
		/* echo '<pre>';
		echo $customerId; 
		print_r($cus_quote->getId());
		die("test"); */
	}
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    
    public function mergeGuestToCustomerCart($guestQuoteId){
		$customerId	=	$this->_context->getUserId();
		$quoteIdMask = 	$this->quoteIdMaskFactory->create()->load($guestQuoteId, 'masked_id');
		$guestQuote = 	$this->quoteRepository->get($quoteIdMask->getQuoteId());
		$cus_quote = $this->_quoteFactory->create()->loadByCustomer($customerId);
		if(empty($cus_quote->getData())){
			$store = $this->_store;
            $cartId = $quoteIdMask->getQuoteId();
		    $quote = $this->quoteRepository->get($cartId);
            $quote->setStore($store);
            $customer= $this->customerRepository->getById($customerId);
            $quote->setCurrency();
            try{
		        $quote->assignCustomer($customer);
                $quote->save();
				return true;
		    }catch(\Exception $e){
		    	return $e->getMessage();
		    }
		}else{
			if($cus_quote->merge($guestQuote)){
		    	try{
		    		$this->saveHandler->save($cus_quote);
		    		$cus_quote->collectTotals();
		    		return true;
		    	}catch(\Exception $e){
		    		return $e->getMessage();
		    	}
		    }else{
		    	return false;
		    }
		}
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function setShippingInformation(){
		
		$Authorization 		=	$this->request->getHeader('Authorization');
		$customerId			=	$this->_context->getUserId(); /*get Login user detail*/
		
		$user_data 			=	json_decode($this->request->getContent(), true);
		$user_data['addressInformation']['shippingAddress']['street']	=	explode("|", $user_data['addressInformation']['shippingAddress']['street']);
		$user_data['addressInformation']['billingAddress']['street']	=	explode("|", $user_data['addressInformation']['billingAddress']['street']);
		
		
		$ch = curl_init('https://elocal.ae/rest/default/V1/carts/mine/shipping-information');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($user_data));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Authorization: '.$Authorization,
		));
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);
		
		$json = json_decode($result, true);
		//echo $customerId; //exit;
		//echo "<pre>"; print_r($result); exit;
		
		return [$json];
		
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $customerId Users name.
     * @return string Greeting message with users name.
     */
    public function getCartItemsByUserId($customerId) {
		
		$quote 						= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
		$cart_items 	=	[];
		if($customerId && $quote){	
			$allItems = $quote->getAllVisibleItems();
			foreach($allItems as $item) {
				
				$product_id 				=	$item->getProductId();
				$product_qty 				=	$item->getQty();
				$cart_items[$product_id]	=	$product_qty;
			}
		}
		return $cart_items;
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $cartId Users name.
     * @return string Greeting message with users name.
     */
     public function getCartItemsByCartId($cartId) {
		
		if(!is_numeric($cartId)){
			$quoteIdMask 	= 	$this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
			$cartId 		= 	$quoteIdMask->getQuoteId();
		}
		$quote 						= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->load($cartId);
		$cart_items 	=	[];
		if($cartId && $quote){	
			$allItems = $quote->getAllItems();
			
			foreach($allItems as $item) {
				/* if($item->getProductType() == 'configurable'){
					$qty 		= $item->getQty();
				} */
				$product_id 					=	$item->getProductId();
				$product_qty 					=	$item->getQty();
				$cart_items[$product_id]['qty']	=	$product_qty;
				$cart_items[$product_id]['id']	=	$item->getId();
				
				/* echo $product_id . "==" . $item->getProductType() .  "----" . $product_qty;
				echo "<br/>"; */
			}
			/* echo "<pre>"; print_r($cart_items); 
			exit; */
		}
		return $cart_items;
    }
}