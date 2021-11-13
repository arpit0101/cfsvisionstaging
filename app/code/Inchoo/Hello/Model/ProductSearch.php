<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\ProductSearchInterface;

use Magento\Framework\HTTP\PhpEnvironment\Request;
 
class ProductSearch implements ProductSearchInterface
{
	protected $request;
	protected $_productCollectionFactory;
	protected $_objectManager;
	protected $_customerSession;
	protected $_categoryCollectionFactory;
	public $imageHelperFactory;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		
		\Magento\Framework\App\Request\Http $request,
       \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
		\Magento\Customer\Model\Session $customerSession,
		array $data = []
	) {
		$this->request						=	$request;
		
       $this->_productCollectionFactory 	= 	$productCollectionFactory;
		$this->_objectManager 				= 	$objectManager;
		$this->_categoryCollectionFactory 	= 	$categoryCollectionFactory;
		$this->imageHelperFactory 			= 	$imageHelperFactory;
		$this->_customerSession 			= 	$customerSession;
		$this->productStatus 				= 	$productStatus;
	}
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function ProductSearch() {
		
		$search_data 		=	json_decode($this->request->getContent(),true);
		$search_term		=	$search_data['search_term'];
		$seller_id			=	$search_data['seller_id'];
		
		$querydata 		= 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
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
			//return 	$querydata->getData();				
			$collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
                '*'
            );
		$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
		$collection->addAttributeToFilter('name', array('like' => '%'.$search_term.'%'));
			
		return $collection->getData();
			
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
	public function getSearchData()
	{
		$search_data 		=	json_decode($this->request->getContent(),true);
		$search_term		=	$search_data['search_term'];
		$area_id			=	$search_data['area_id'];
		$city_id			=	$search_data['city_id'];
		$page_number		=	$search_data['page_number'];
		
		$products = $this->_productCollectionFactory->create()->addAttributeToSelect(
			'*'
		)->addFieldToFilter(
			'name',
			['like' => '%'.$search_term.'%']
		)-> addAttributeToFilter('visibility', array('in' => array(4) )
		)-> addAttributeToFilter('status', array('in' => array(1) ))
		->addCategoryIds()->setPageSize(10)
        ->setCurPage($page_number);
		
		
		$products->getSelect()
				->join(array("marketplace_product" => 'marketplace_product'),"`marketplace_product`.`mageproduct_id` = `e`.`entity_id`",array("seller_id" => "seller_id"))
				->join(array("marketplace_userdata" => 'marketplace_userdata'),"`marketplace_userdata`.`seller_id` = `marketplace_product`.`seller_id` AND (FIND_IN_SET('".$area_id."', `area_id`)) AND (FIND_IN_SET('".$city_id."', `region_id`)) ",array("shop_url", "logo_pic")); 
		
		
		$all_response 	=	array();
		$all_response['data'] 	=	array();
		$categories 	=	array();
		$seller_data 	=	array();
		$priceHelper 	= 	$this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
		$StockState 	= 	$this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		foreach($products as $product_data){
			if(!isset($all_response['data'][$product_data->getSellerId()])){			
				$all_response['data'][$product_data->getSellerId()]['seller']	=	array(
																				'name'=> ucfirst($product_data->getShopUrl()),
																				'shop_url'=> ($product_data->getShopUrl()),
																				'logo'=> $product_data->getLogoPic(),
																			);					
			}
			$categories			=	$product_data->getCategoryIds();
			$formattedPrice 	= 	$priceHelper->currency($product_data->getPrice(), true, false);
			
			$product_quantity	=	$StockState->getStockQty($product_data->getId(), $product_data->getStore()->getWebsiteId());
			$collectioncat 		= 	$this->_categoryCollectionFactory->create();
			$collectioncat->addAttributeToSelect(array('name'), 'inner');
			$collectioncat->addAttributeToFilter('entity_id', array('in' => $categories));
			$collectioncat->addAttributeToFilter('level', array('gt' => 1));
			$collectioncat->addIsActiveFilter();
			$productImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product/';
			
			$shortdescription	=	$product_data->getShortDescription();
			if($shortdescription==null){
				$shortdescription	=	"";
			}
			if($product_data->getTypeId()!="simple"){
				$price			=	0;
				$product_price	=	0;
				$product_special_price	=	0;
				$_children = $product_data->getTypeInstance()->getUsedProducts($product_data);
				foreach ($_children as $child){
					$productPrice = $child->getPrice();
					$price = $price ? min($price, $productPrice) : $productPrice;
				}
				$special_price	=	$product_data->getFinalPrice(); 
				if($price <= $product_data->getFinalPrice()){
					$special_price	=	null; 
				}
				$formattedPrice			=	$price; 
				$product_price			=	$product_data->getProductPrice();
				$product_special_price	=	$product_data->getProductSpecialPrice();
			}else{
				$formattedPrice			=	$product_data->getPrice();  
				$special_price			=	$product_data->getFinalPrice(); 
				$product_price			=	0;
				$product_special_price	=	0;
			}
			if($formattedPrice == $special_price ){
				$special_price			=	null; 
			}
			$formattedPrice				=	number_format($formattedPrice, 2);
			if($special_price !=null){
				$special_price				=	number_format($special_price, 2);
			}
			$all_response['data'][$product_data->getSellerId()]['products'][]	=	array(
												'id'=>$product_data->getId(),
												'name'=>$product_data->getName(),
												'sku'=>$product_data->getSku(),
												'short_description'=>$shortdescription,
												'price'=>$formattedPrice,
												'product_price'=>$product_price,
												'product_special_price'=>$product_special_price,
												'special_price'=>$special_price,
												'image'=>ltrim($product_data->getImage(), "/"),
												'small_image'=>ltrim($product_data->getSmallImage(), "/"),
												'thumbnail'=>ltrim($product_data->getThumbnail(), "/"),
												'quantity'=>$product_quantity,
												'product_data'=>$product_data->getData(),
												'productImageUrl'=>$productImageUrl,
												'categories'=>$collectioncat->getData(),
											);
			$all_response['data'][$product_data->getSellerId()]['categories']	=	$collectioncat->getData();
		}
		
		
		$search_data 	=	[['page_number'=>$page_number,'total_count'=>$products->getSize()]];
		if(!empty($all_response['data'])){
			foreach($all_response['data'] as $serller_id=>$seller_data){
				$customer_data 					= 	$this->_objectManager->create('Magento\Customer\Model\Customer')->load($serller_id);
				$seller_data['seller']['name']	=	trim($customer_data->getData('firstname')." ".$customer_data->getData('lastname'));
				$search_data[]	=	[
										'seller_data'=>$seller_data['seller'],
										'products'=>$seller_data['products'],
										'categories'=>$seller_data['categories']
									];
			}
		}
		return $search_data;
	}
}