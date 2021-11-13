<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\ProductInterface;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Inchoo\Hello\Model\Wishlist;
use Inchoo\Hello\Model\Cart;
 
class Product implements ProductInterface
{
	protected $_objectManager;
	protected $_context;
	protected $request;
	protected $_productCollectionFactory;
	
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\App\Request\Http $request,
		Wishlist $wishlist,
		Cart $cart,
		array $data = []
		
	) {
		$this->_context	=	$context;
		$this->_objectManager 				=   $objectManager;
		$this->request						=	$request;
		$this->_wishlist					=	$wishlist;
		$this->_cart						=	$cart;
		$this->_productCollectionFactory 	= 	$productCollectionFactory;
	}	

   /**
     * Returns product info by barcode
     *
     * @api
     * @param string $barcode .
     * @return string Product data.
     */
    public function getProductByBarcode($barcode){
		
		$collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
                '*'
            );
		$collection->addAttributeToFilter('barcode', array('eq' => $barcode));
		$collection->addAttributeToFilter('visibility', array('in' => array(4)));
		$store 					= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		
		$all_products 			=	array();
		foreach($collection as $product){
			$product_data 		=	$product->getData();
			$productImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
			
			$product_data['productImageUrl']	=	$productImageUrl; 
			$product_data['productImages']		=	$product->getMediaGalleryImages();; 
			$all_products[] 	=	$product_data;
		}
		return $all_products;
	}
	
	/**
     * Returns product info by sku and attribute_name
     *
     * @api
     * @param string $sku .
     * @param string $attribute_name .
     * @return string Product data.
     */
    public function getConfigurableProductChilds($sku, $attribute_id){
		
		$productRepository 	= 	$this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
		$productObj 		= 	$productRepository->get($sku);
		
		$_childrens 		= 	$productObj->getTypeInstance()->getUsedProducts($productObj);
		$all_products		=	[];
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		
		$eavModel 			= 	$this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($attribute_id);
		
		$attribute_name		=	$eavModel->getAttributeCode();
		if(!empty($_childrens)){
			foreach($_childrens as $product){
				$product_data 		=	$product->getData();
				$productImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
				if(isset($product_data[$attribute_name])){
					$product_data['attribute_value']	=	$product_data[$attribute_name];
				}
				$product_data['productImageUrl']	=	$productImageUrl; 
				$product_data['productImages']		=	$product->getMediaGalleryImages();;
				unset($product_data['tier_price']);
				unset($product_data['media_gallery']);
				unset($product_data['productImages']);
				$all_products[] 	=	$product_data;
			}
		}
		return $all_products;
	}
	
	/**
     * Returns product info by sku
     *
     * @api
     * @param string $sku
     * @return mixed.
     */
    public function getProductInfo($sku){
		
		$customerId				=	$this->_context->getUserId();
		$cartitems				=	[];
		$wishlistitems			=	[];
		if($customerId!=""){
			$wishlistitems 		=	$this->_wishlist->getWishlistByUserId($customerId);	
			$cartitems 			=	$this->_cart->getCartItemsByUserId($customerId);		
		}
		if(isset($_SERVER['HTTP_QUOTE_ID']) && $_SERVER['HTTP_QUOTE_ID']!=""){
			$cartdata 			=	$this->_cart->getCartItemsByCartId($_SERVER['HTTP_QUOTE_ID']);
		}
		
		$sku	=	base64_decode($sku);
		$productRepository 	= 	$this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		// return [$store->getId()];
		$_product 		= 	$productRepository->get($sku, false , $store->getId());
		if($_product){
			$ProductInfoData	=	$_product->getData();
			$ProductInfoData['description']	=	$_product->getDescription();
			$extension_attributes			=	[];
			$childrens						=	[];
			if($_product->getTypeId()=="configurable"){
				$productTypeInstance = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
				
				$productAttributeOptions = $productTypeInstance->getConfigurableAttributesAsArray($_product);
				
				if(!empty($productAttributeOptions)){
					$swatchHelper 	= 	$this->_objectManager->get('\Magento\Swatches\Helper\Data');
					
					foreach($productAttributeOptions as $attributeOptions){
						$attributeOptions['product_id']	=	$_product->getID();
						$attributeOptions['label']			=	$attributeOptions['store_label'];
						$attributeOptions['frontend_label']	=	$attributeOptions['store_label'];
							if(!empty($attributeOptions['values'])){
								foreach($attributeOptions['values'] as &$attributeValue){
									if($attributeOptions['attribute_id']==93){
										$hashcodeData 	= 	$swatchHelper->getSwatchesByOptionsId([$attributeValue['value_index']]);
										$attributeValue['hashcode']	=	$hashcodeData[$attributeValue['value_index']]['value'];
									}else{
										$attributeValue['hashcode']	=	"";
									}
								}
							}
						$extension_attributes['configurable_product_options'][]	=	$attributeOptions;
					}
				}
				$_children = $_product->getTypeInstance()->getUsedProducts($_product);
				foreach ($_children as $child){
					
					if($child->getTypeId()!="simple"){
						$special_price	=	$child->getFinalPrice(); 
						$price			=	$child->getPrice(); 
					}else{
						$price			=	$child->getPrice();  
						$special_price	=	null; 
						if($price > $child->getFinalPrice()){
							$special_price	=	$child->getFinalPrice(); 
						}
					}
					$attributes = $child->getCustomAttributes(); 
					$custom	=	[];
					foreach ($attributes as $attribute) {  
					   $custom[] = ['attribute_value'=>$attribute->getValue(), 'attribute_code'=>$attribute->getAttributeCode()]; 
					}
					$media_gallery_entries	=	[];
					foreach($child->getMediaGallery()['images'] as $id=>$media){
						$media['file']				=	ltrim($media['file'], "/"); 
						// $media['small_image']		=	ltrim($media['small_image'], "/"); 
						// $media['thumbnail']			=	ltrim($media['thumbnail'], "/"); 
						$media_gallery_entries[]	=	$media;
					}
					
					if(isset($cartdata[$child->getId()])){			
						$childInfoData['is_in_cart']			=	1;
						$childInfoData['cart_quantity']			=	$cartdata[$child->getId()]['qty']; 
						//$childInfoData['cart_item_id']			=	$cartdata[$child->getId()]['id']; 
					} 
					else
					{
						$childInfoData['is_in_cart']			=	0;
						$childInfoData['cart_quantity']			=	0; 
					}
					$childrens[]	=	[
											'id'=>$child->getID(),
											'sku'=>$child->getSku(),
											'name'=>$child->getName(),
											'image'=>ltrim($child->getImage(),"/"),
											'small_image'=>ltrim($child->getSmallImage(),"/"),
											'is_in_whishlist'=>0,
											'is_in_cart'=>$childInfoData['is_in_cart'],
											'cart_quantity'=>$childInfoData['cart_quantity'],
											'thumbnail'=>ltrim($child->getThumbnail(),"/"),
											'media_gallery'=>$media_gallery_entries,
											'attribute_set_id'=>$child->getAttributeSetId(),
											'special_price'=>$special_price,
											'special_price'=>$special_price,
											'price'=>$price,
											'status'=>$child->getStatus(),
											'type_id'=>$child->getTypeId(),
											'custom_attributes'=>$custom
										];
				}
			}
			$ProductInfoData['childrens']				=	$childrens;
			if(empty($childrens)){
				$ProductInfoData['childrens']	=	null;
			}
			if(empty($extension_attributes)){
				$ProductInfoData['extension_attributes']	=	null;
			}else{
				$ProductInfoData['extension_attributes']	=	$extension_attributes;
			}
			$ProductInfoData['id']	=	$_product->getId();
			if($_product->getTypeId()!="simple"){
				
				$price	=	0;
				$_children = $_product->getTypeInstance()->getUsedProducts($_product);
				foreach ($_children as $child){
					$productPrice = $child->getPrice();
					$price = $price ? min($price, $productPrice) : $productPrice;
				}
				$ProductInfoData['special_price']	=	null; 
				if($price > $_product->getFinalPrice()){
					$ProductInfoData['special_price']	=	$_product->getFinalPrice();
				}
				
				$ProductInfoData['price']			=	$price; 
			}else{
				$ProductInfoData['price']			=	$_product->getPrice();  
				// $ProductInfoData['special_price']	=	$_product->getFinalPrice(); 
				$ProductInfoData['special_price']	=	null; 
				if($ProductInfoData['price'] > $_product->getFinalPrice()){
					$ProductInfoData['special_price']	=	$_product->getFinalPrice();
				}
			}
			$ProductInfoData['media_gallery_entries']	=	[];
			foreach($ProductInfoData['media_gallery']['images'] as $id=>$media){
				$media['file']										=	ltrim($media['file'], "/");
				$ProductInfoData['media_gallery_entries'][]			=	$media; 
			}
			if(empty($ProductInfoData['media_gallery_entries'])){
				$ProductInfoData['media_gallery_entries']			=	[0=>['file'=>'']]; 
			}
			// media_gallery_entries
			
			$ProductInfoData['is_in_whishlist']			=	0; 
			if(in_array($_product->getId(), $wishlistitems)){
				$ProductInfoData['is_in_whishlist']			=	1;
			} 
			$ProductInfoData['is_in_cart']				=	0;
			$ProductInfoData['cart_quantity']			=	0; 
			
			
			$ProductInfoData['cart_item_id']				=	"0";
			if(isset($cartdata[$_product->getId()])){			
				$ProductInfoData['is_in_cart']				=	1;
				$ProductInfoData['cart_quantity']			=	$cartdata[$_product->getId()]['qty']; 
				$ProductInfoData['cart_item_id']			=	$cartdata[$_product->getId()]['id']; 
			} 
			$ProductInfoData['image']				=	ltrim($_product->getImage(), "/"); 
			$ProductInfoData['small_image']			=	ltrim($_product->getSmallImage(), "/"); 
			$ProductInfoData['thumbnail']			=	ltrim($_product->getThumbnail(), "/"); 
			$ProductInfoData['product_url']			=	$_product->getProductUrl(); 
			$ProductInfoData['description_simple']	=	strip_tags($ProductInfoData['description']); 
			return [$ProductInfoData];
		}
	}
	
	/**
     * Returns product attribute info by attribute_id and $product_id
     *
     * @api
     * @param string $attribute_id
     * @param string $product_id
     * @return mixed.
     */
    public function getAttributeInfo($attribute_id, $product_id){
		
		$ch = curl_init("https://elocal.ae/rest/V1/products/attributes/".$attribute_id);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

		$attributeInfoData 	= 	curl_exec($ch);
		$attributeInfo		=	json_decode($attributeInfoData, true);
		// $productRepository 	= 	$this->_objectManager->get('\Magento\Catalog\Model\ProductRepository');
		$_product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
		
		
		$data = $_product->getTypeInstance()->getConfigurableOptions($_product);
		// return [$data];
		$swatchHelper 	= 	$this->_objectManager->get('\Magento\Swatches\Helper\Data');
		if(!empty($attributeInfo['options'])){
			foreach($attributeInfo['options'] as $index=>&$option_data){
				$option_data['hashcode']	=	'';
				if($attribute_id == 93){
					if($option_data['value']!=""){
						$hashcodeData 	= 	$swatchHelper->getSwatchesByOptionsId([$option_data['value']]);
						$option_data['hashcode']	=	$hashcodeData[$option_data['value']]['value'];
					}
					$is_exist 	=	false;
					if(!empty($data) && isset($data[$attribute_id])){
						
						foreach($data[$attribute_id] as $attribute_data){
							
							if($attribute_data['attribute_code']=="color" && $attribute_data['value_index']==$option_data['value']){
								$is_exist 	=	true;
							}
						}
					}
					if(!$is_exist){
						unset($attributeInfo['options'][$index]);
					}
				}
			}
			sort($attributeInfo['options']);
		}
		return [$attributeInfo];
	}
}