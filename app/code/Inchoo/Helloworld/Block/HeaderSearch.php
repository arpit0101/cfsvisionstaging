<?php
namespace Inchoo\Helloworld\Block;
//use Inchoo\HelloWorld\Model\Data;
class HeaderSearch extends \Magento\Framework\View\Element\Template
{
	protected $_productCollectionFactory;
	protected $_objectManager;
	protected $_categoryCollectionFactory;
	public $imageHelperFactory;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
		\Magento\Customer\Model\Session $customerSession,
		array $data = []
	) {
		$this->_productCollectionFactory 	= 	$productCollectionFactory;
		$this->_objectManager 				= 	$objectManager;
		$this->_categoryCollectionFactory 	= 	$categoryCollectionFactory;
		$this->imageHelperFactory 			= 	$imageHelperFactory;
		$this->_customerSession 			= 	$customerSession;
		parent::__construct($context, $data);
	
	}
	
	/**
	* @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
	*/
	public function getSearchData()
	{
		$error=0;	
		
		
		if($this->_customerSession->getCityId()){
			$city_id 	= 	$this->_customerSession->getCityId();
		}
		/* else
		{
			$city_id = 791;
		} */
		if($this->_customerSession->getAreaId()){
			$area_id 	= 	$this->_customerSession->getAreaId();
		}
		/* else
		{
			$area_id = 157;
		} */
		
		$seller_arr = array();
		$search_term 	= 	$this->getRequest()->getParam('value', false);
		
		$products = $this->_productCollectionFactory->create()->addAttributeToSelect(
			'*'
		)->addFieldToFilter(
			'name', array('like' => '%'.$search_term.'%')
		)-> addAttributeToFilter('visibility', array('in' => array(4) ))
		->addCategoryIds();
		
		$products->getSelect()
				->joinLeft(array("marketplace_product" => 'marketplace_product'),"`marketplace_product`.`mageproduct_id` = `e`.`entity_id`",array("seller_id" => "seller_id"))
				->joinLeft(array("marketplace_userdata" => 'marketplace_userdata'),"`marketplace_userdata`.`seller_id` = `marketplace_product`.`seller_id` AND (FIND_IN_SET('".$area_id."', `area_id`)) AND (`region_id` = '".$city_id."') ",array("shop_url", "logo_pic"));
		//echo $products->getSelect()->__toString(); exit;
		$all_response 	=	array();
		$all_response['data'] 	=	array();
		$categories 	=	array();
		$seller_data 	=	array();
		$priceHelper 	= 	$this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
		$StockState 	= 	$this->_objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
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
			
			$all_response['data'][$product_data->getSellerId()]['products'][]	=	array(
												'id'=>$product_data->getId(),
												'name'=>$product_data->getName(),
												'price'=>$formattedPrice,
												'image'=>$product_data->getImageUrl(),
												'quantity'=>$product_quantity,
												'product_data'=>$product_data,
												'categories'=>$collectioncat,
											);
			
			$all_response['data'][$product_data->getSellerId()]['categories'][]	=	$collectioncat;
		}
		return $all_response;
	}
}