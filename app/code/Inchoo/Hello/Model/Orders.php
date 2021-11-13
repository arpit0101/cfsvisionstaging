<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\OrdersInterface;
 use Magento\Webapi\Model\Authorization\TokenUserContext;
 
class Orders implements OrdersInterface
{
	protected $_objectManager;
	protected $_context;
	protected $request;
	
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\App\Request\Http $request,
		array $data = []
		
	) {
		$this->_context	=	$context;
		$this->_objectManager 				=   $objectManager;
		$this->request						=	$request;
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function MyOrders() {
		
		try{
			$customerId		=	$this->_context->getUserId();
			if(empty($customerId)){
				return ["status"=>false,"msg"=>"Invalid user token."];
			}
			$orders 		= $this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter('customer_id',$customerId);
			$orderData = array();
			if(count($orders)){
				foreach ($orders as $order){
					
					$orderData[] = $order->getData();
				}
				return $orderData;
			}else{
				return [];
			}
		}
		catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			return false;
		}
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function OrderDetails($order_id=null) {
		
		try{
			$customerId		=	$this->_context->getUserId();
			if(empty($customerId)){
				return [["status"=>false,"msg"=>"Invalid user token.","msg_ar"=>"رمز المستخدم غير صالح."]];
			}
			if($order_id ==null){
				return [["status"=>false,"msg"=>"Invalid order ID."]];
			}
			$store 		= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
			//$order 		= 	$this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
			$order 		= 	$this->_objectManager->create('Magento\Sales\Api\OrderRepositoryInterface')->get($order_id);
			if($order->getCustomerId() == $customerId){
				$order_items 	=	[];
				
				
				$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$tableName = $resource->getTableName('eav_attribute_option_value');
				$eavtableName = $resource->getTableName('eav_attribute');
				
				foreach ($order->getAllItems() as $item) {
					//echo $item->getProductType(); 
					//echo "<hr/>";
					
					if($item->getProductType() == 'configurable'){
						$qty 		= 	$item->getQtyOrdered();
						//echo $qty; 
					}
					if($item->getProductType() == 'simple'){
						$product_color = ''; 
						$product_size =  '';
						$product_weight =  '';
						//if($item->getParentItemId() ==null){
							$product_id 		=	$item->getProductId();
							$product_sku 		=	$item->getSku();
							$productImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
							$product 			= 	$this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface')->get($product_sku);
							
							$productinfo		=	$product->getData();
							if(is_array($item->getProductOptions())){
							foreach ($item->getProductOptions() as $option) {
								//echo "<pre>"; print_r($option); 
								//$itemOptions = $option['info_buyRequest'];
							   //$itemopt = json_decode($itemOptions, true);
							   
								if(isset($option['super_attribute'])){
									//echo '<pre>'; print_r($itemOptions['super_attribute']);
									foreach($option['super_attribute'] as $key =>  $superattribute) {
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
							}
							}
							
							if(isset($productinfo['selection_option']))
							{
								$item->setQtyOrdered($qty);
							}
							/* if($product->getProductWeight())
							{
								$product_weight = $product->getAttributeText('product_weight');
								$product->setProductWeight($product_weight);
							}
							if($product->getSize())
							{
								$product_size = $product->getAttributeText('size');
								$product->setSize($product_size);
							}
							if($product->getColor() != '')
							{
								$product_color = $product->getAttributeText('color');
								$product->setColor($product_color);
							} */
							//echo $product->getProductWeight();
							$order_items[]		=	['productinfo'=>$productinfo,'productImageUrl'=>$productImageUrl, 'cartiteminfo'=>$item->getData()];
					}
				} //exit;
				$order_data 			=	$order->getData();
				$order_data['items'] 	=	$order_items;
				return [$order_data];
			}else{
				return [["status"=>false,"msg"=>"Order does not belongs to you."]];
			}
		}
		catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			return false;
		}
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function reOrder($order_id=null) {
		
		try{
			$customerId		=	$this->_context->getUserId();
			if(empty($customerId)){
				return [["status"=>false,"msg"=>"Invalid user token."]];
			}
			if($order_id ==null){
				return [["status"=>false,"msg"=>"Invalid order ID."]];
			}
			$store 		= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
			$order 		= 	$this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
			if($order->getCustomerId() == $customerId){
				
				$quote 			= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
				$cartid 		=	$quote->getId();
				$cart 			= 	$this->_objectManager->create('Magento\Checkout\Model\Cart')->setQuote($quote);
				$order_items 	=	[];
				
				foreach ($order->getAllItems() as $item) {
					
					if($item->getParentItemId() ==null){
						
						try{
							$params = array(
								'product' => $item->getData('product_id'), //product Id
								'qty'   =>$item->getData('qty_ordered') //quantity of product                
							);  
							$productRepository 	= 	$this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
							$productObj 		= 	$productRepository->get($item->getData('sku'));
							       
							$cart->addProduct($productObj, $params);
						}catch (\Magento\Framework\Exception\LocalizedException $e) {
							return [$e->getMessage()];
						}
					}
				}
				$cart->save();
				$quote 			= 	$this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($customerId);
				return [$quote->getData()];
			}else{
				return [["status"=>false,"msg"=>"Order does not belongs to you."]];
			}
		}
		catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			return false;
		}
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function cancelOrder($order_id=null) {
		
		try{
			$customerId		=	$this->_context->getUserId();
			if(empty($customerId)){
				return [["status"=>false,"msg"=>"Invalid user token."]];
			}
			if($order_id ==null){
				return [["status"=>false,"msg"=>"Invalid order ID."]];
			}
			$store 		= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
			$order 		= 	$this->_objectManager->create('Magento\Sales\Model\Order')->load($order_id);
			if($order->getCustomerId() == $customerId){
				if($order->canCancel()){
					$order->cancel();
					$order->save();
					return [["status"=>false,"msg"=>"'Order has been canceled successfully."]];
				} else {
					return [["status"=>false,"msg"=>"'Order cannot be canceled."]];
				}
			}else{
				return [["status"=>false,"msg"=>"Order does not belongs to you."]];
			}
		}
		catch (Exception $e) {
			Zend_Debug::dump($e->getMessage());
			return false;
		}
    }
}