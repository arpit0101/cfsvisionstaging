<?php
namespace Inchoo\Hello\Model;

use Inchoo\Hello\Api\SellerProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
use Inchoo\Hello\Model\Wishlist;
use Inchoo\Hello\Model\Cart;
use Magento\Webapi\Model\Authorization\TokenUserContext;

class SellerProduct implements SellerProductInterface
{
	/**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
	/**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;
	
	
	protected $request;
    /** 
     * @var \Magento\Catalog\Model\Product 
     */
    protected $productlists;
	
	protected $_categoryFactory;
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver
	* @param CategoryRepositoryInterface               $categoryRepository
	* @param \Magento\Framework\Stdlib\StringUtils     $stringUtils
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Layer\Resolver $layerResolver,
		CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
		TokenUserContext $context,
		Wishlist $wishlist,
		\Magento\Framework\App\Request\Http $request,
		Cart $cart,
		array $data = []
	) {
		
		$this->_objectManager 				=   $objectManager;
		$this->_categoryFactory 			= 	$categoryFactory;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_context						=	$context;
		$this->_wishlist					=	$wishlist;
		$this->_cart						=	$cart;
		$this->request 						= 	$request;
		$this->_categoryRepository 			= $categoryRepository;
		$this->stringUtils 					= $stringUtils;
		$this->layerResolver 				= $layerResolver;
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function getProduct($seller_id, $cat_id) {
		
		// $store 			= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		// $product_file	=	__DIR__."/json/products".$seller_id."_".$cat_id."_".$store->getCode().".json";
		$customerId				=	$this->_context->getUserId();
		//$search_data 		=	json_decode();
		$page_number			=	$this->request->getParam('page_number');
		$search_term			=	$this->request->getParam('search_term');
		//echo "<pre>"; print_r($this->request->getParams()); exit;
		$wishlistitems			=	[];
		$cartitems				=	[];
		if($customerId!=""){
			$wishlistitems 		=	$this->_wishlist->getWishlistByUserId($customerId);	
			$cartitems 			=	$this->_cart->getCartItemsByUserId($customerId);
		}
		
		if(isset($_SERVER['HTTP_QUOTE_ID']) && $_SERVER['HTTP_QUOTE_ID']!=""){
			// file_put_contents(__DIR__."/logs/server_".time().".txt", print_r($_SERVER, true));
			$cartdata 			=	$this->_cart->getCartItemsByCartId($_SERVER['HTTP_QUOTE_ID']);
		}
		
		$StockState 			= 	$this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		
		
		$querydata = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
						->getCollection()
						->addFieldToFilter(
							'seller_id',
							['eq' => $seller_id]
						)
						->addFieldToFilter(
							'status',
							['eq' =>1]
						)
						->addFieldToSelect('mageproduct_id')
						->setOrder('mageproduct_id');
						
		$layer = $this->layerResolver->get();
			
		$collection = $layer->getProductCollection();
		$collection->addAttributeToSelect('*');			
		/* $collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
			'*'
		) */		$collection-> addAttributeToFilter('visibility', array('in' => array(4) ));
		$collection-> addAttributeToFilter('status', array('in' => array(1) ));
		$collection->setOrder('position', 'asc');
		
		if($search_term != 'null')
		{
			$collection->addAttributeToFilter('name',	['like' => '%'.$search_term.'%']);
		}
		$all_categories 	=	array();
		if($cat_id!=0){
			$all_categories		=	$this->getSubcategories([$cat_id], $all_categories);
			$collection->addCategoriesFilter(array('in' => $all_categories));
		}
		$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
		$collection-> addAttributeToFilter('visibility', array('in' => array(4) ))->setPageSize(10)
        ->setCurPage($page_number);
		$this->productlists = $collection;
		$all_products 		=	array();
		$all_productss 		=	array();
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$all_products[]['totalcount'] = $collection->getSize();
		foreach($this->productlists as $product){
			
			$stockItem			=	$StockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
			if($stockItem < 1 && $product->getTypeId()=="simple")
				continue;
			
			$product_data 		=	$product->getData();
			$productImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
			
			$product_data['productImageUrl']	=	$productImageUrl; 
			if($product->getTypeId()!="simple" && $product->getTypeId() != "virtual"){
				$price	=	0;
				$product_data['product_price'] = 0;
				$product_data['product_special_price'] = 0;
				$_children = $product->getTypeInstance()->getUsedProducts($product);
				foreach ($_children as $child){
					$productPrice = $child->getPrice();
					$price = $price ? min($price, $productPrice) : $productPrice;
				}
				$product_data['special_price']	=	$product->getFinalPrice(); 
				if($price <= $product->getFinalPrice()){
					$product_data['special_price']	=	null; 
				}
				$product_data['price']			=	$price; 
				$product_data['product_price']	=	$product->getProductPrice();
				$product_data['product_special_price']	=	$product->getProductSpecialPrice();
			}else{
				$product_data['price']			=	$product->getPrice();  
				$product_data['special_price']	=	$product->getFinalPrice(); 
			}
			if($product_data['special_price'] == $product_data['price']){
				$product_data['special_price']	=	null;
			}
			
			$product_data['is_in_whishlist']		=	0; 
			if(in_array($product->getId(), $wishlistitems)){
				$product_data['is_in_whishlist']		=	1; 
			}
			$product_data['is_in_cart']				=	0; 
			$product_data['cart_quantity']			=	0; 
			
			$product_data['cart_item_id']				=	"0";
			if(isset($cartdata[$product->getId()])){
				$product_data['is_in_cart']				=	1;
				$product_data['cart_quantity']			=	$cartdata[$product->getId()]['qty']; 	
				$product_data['cart_item_id']			=	$cartdata[$product->getId()]['id'];
			}  
			$product_data['image']				=	ltrim($product->getImage(), "/"); 
			$product_data['small_image']		=	ltrim($product->getSmallImage(), "/"); 
			
			$product_data['thumbnail']			=	ltrim($product->getThumbnail(), "/"); 
			$product_data['productImages']		=	$product->getMediaGalleryImages();
			//$all_products['product_data'][] 	=	$product_data;
			$all_productss[] 	=	$product_data;
		}
		$all_products[]['products'] = $all_productss;
		return $all_products;
	}
	
	/**
     *get all multilevel categories
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	
	public function getSubcategories($categories, $all_categories) {
		
		foreach($categories as $category_id) {
			$this->_category = $this->_categoryFactory->create();
			$this->_category->load($category_id); 
			$all_categories[]	=	$category_id;
			$childs 	=	$this->_category->getChildren();
			$subcategories =	array_filter(explode(",", $childs));
			
			if(!empty($subcategories)){
				$all_categories	= $this->getSubcategories($subcategories, $all_categories);
			}
		}
		return $all_categories;
	}
	
	
}