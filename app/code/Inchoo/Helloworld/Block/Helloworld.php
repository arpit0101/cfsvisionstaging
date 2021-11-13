<?php
namespace Inchoo\Helloworld\Block;
//use Inchoo\HelloWorld\Model\Data;
class Helloworld extends \Magento\Framework\View\Element\Template
{
	protected $_categoryHelper;
	protected $categoryFlatConfig;
	protected $topMenu;
	protected $categoryView;
	protected $_filterProvider;
	protected $_objectManager;
	protected $_sellerlistCollectionFactory;
	protected $sellerList;
	protected $_productCollectionFactory;

	protected $_customerSession;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Helper\Category $categoryHelper,
		\Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
		\Magento\Theme\Block\Html\Topmenu $topMenu,
		\Magento\Catalog\Model\Category $categoryView,
		\Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
		\PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		
		array $data = []
	) {
		$this->_categoryHelper = $categoryHelper;
		$this->categoryFlatConfig = $categoryFlatState;
		$this->topMenu = $topMenu;
		$this->categoryView = $categoryView;
		$this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
		$this->_filterProvider = $filterProvider;
		$this->_objectManager = $objectManager;
		$this->_regionCollection 			= 	$regionCollection;
		$this->_areaCollection 				= 	$areaCollection;
		$this->_customerSession 			= 	$customerSession;
		$this->_categoryFactory 			= 	$categoryFactory;
		$this->_productCollectionFactory 	= 	$productCollectionFactory;
	
		parent::__construct($context, $data);
	
	}
	
	/*  public function getRegionCollection()
	{
		$regionCollection = $this->model->getCollection();
		echo "
		<pre>
		"; 
		print_r($regionCollection);
		return $regionCollection;
	} */
	/**
	* @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
	*/
	public function getSellerCollection()
	{
		$city_id 			= 	$this->getRequest()->getParam('city_id', false);
		$area_id 			= 	$this->getRequest()->getParam('area_id', false);		
		$categoryId 		= 	$this->getRequest()->getParam('category_id', false);
		
		//echo $category_id; exit;		
		$urlInterface		=	$this->_objectManager->get('Magento\Framework\UrlInterface');
		$responseFactory	=	$this->_objectManager->get('Magento\Framework\App\ResponseFactory');
		if($area_id!=""){
			if($this->_customerSession->getAreaId() && $this->_customerSession->getAreaId()!= $area_id){
				$cart = $this->_objectManager->get('\Magento\Checkout\Model\Cart');
				$allItems = $cart->getQuote()->getAllVisibleItems();
				foreach ($allItems as $item) {
					$itemId = $item->getItemId();
					$cart->removeItem($itemId)->save();
				}
			}
		}
		if($city_id!=""){
			$this->_customerSession->setCityId($city_id);
		}else{
			if($this->_customerSession->getCityId()){	
				$city_id 	= 	$this->_customerSession->getCityId();
			}
		}
		if($area_id!=""){
			$this->_customerSession->setAreaId($area_id);
		}else{
			if($this->_customerSession->getAreaId()){
				$area_id 	= 	$this->_customerSession->getAreaId();
			}
		}
		if($city_id=="" || $area_id==""){
			$CustomRedirectionUrl = $urlInterface->getUrl('/');
			$responseFactory->create()->setRedirect($CustomRedirectionUrl)->sendResponse();
		}
		//if($categoryId != ''){
			//echo $category_id; exit;	
			//$seller_arr	=	$this->getCategorySellerCollection();
			//$categoryId 	= 	$this->getRequest()->getParam('category', false);
			//echo $category_id; exit;	
			
			$seller_arr 	= 	array();
			if($categoryId !=''){
				$category 	= 	$this->_categoryFactory->create()->load($categoryId);
				$collection = 	$this->_productCollectionFactory->create()->addAttributeToSelect(
					'entity_id'
				);
				$collection->addCategoryFilter($category);
				// $products 	= 	$collection->getData();
				$products 	= 	$collection;
				$product_ids	=	[];
				foreach ($products as $product) {
					array_push($product_ids, $product->getId());
				}
				$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
				->getCollection()
				->addFieldToFilter(
				'mageproduct_id',
					['in' => $product_ids]
				)
				->addFieldToSelect('seller_id')
				->distinct(true);
				
				foreach ($seller_product_coll as $product) {
					array_push($seller_arr, $product->getSellerId());
				}
				
				//return $seller_arr;
			}
			else{	
				//echo "dfdf".$categoryId; exit;
				$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
				->getCollection()
				->addFieldToFilter(
					'status',
					['eq' => 1]
				)
				->addFieldToSelect('seller_id')
				->distinct(true);
				
				foreach ($seller_product_coll as $product) {
					array_push($seller_arr, $product->getSellerId());
				}
				
				//return $seller_arr;
				 
				//echo "<pre>"; print_r($seller_arr); exit;
			}
		//}
		$offsellers	=	$this->getOffSellerCollection();
		$collection = $this->_sellerlistCollectionFactory->create()->addFieldToSelect('*')
		    ->addFieldToFilter(
		    	'is_seller',
		    	['eq' => 1]
		    )
		    ->addFieldToFilter(
		    	'area_id',
		    	['finset' => [$area_id]]
		    )
		    ->addFieldToFilter(
		    	'region_id',
		    	['finset' => [$city_id]]
		    )
		    ->addFieldToFilter(
		    	'seller_id',
		    	['nin' => $offsellers]
		    )
			->addFieldToFilter(
				'seller_id',
				['in' => $seller_arr]
			)
		    ->setOrder(
		    	'sequence',
		    	'asc'
		    );
		$this->sellerList = $collection;
		return $this->sellerList;
	}
	
	public function getOffSellerCollection()
	{
		$currentweekday =	strtolower(date('l'));
		$currentmonth =	strtolower(date('F'));
		$currentdate =	date('d-m-Y');
		$urlInterface =	$this->_objectManager->get('Magento\Framework\UrlInterface');
		$collection = $this->_sellerlistCollectionFactory->create()->addFieldToSelect('*')
		    ->addFieldToFilter(
		    	'is_seller',
		    	['eq' => 1]
		    )
		    ->addFieldToFilter(
		    	['off_week_days', 'off_months', 'off_dates'],
		    	[
		    		['like' => "%".$currentweekday."%"],
		    		['like' => "%".$currentmonth."%"],
		    		['like' => "%".$currentdate."%"]
		    	]
		    )
		    ->setOrder(
		    	'entity_id',
		    	'desc'
		    );
		$offsellers = array();
		foreach($collection as $seller){
			$offsellers[] =	$seller->getSellerId();
		}
		if(!empty($offsellers)){
			return $offsellers;
		}else{
			return array(0);
		}
	}
	
	public function  getCategorySellerCollection()
	{
		$seller_arr 	= 	array();
		$categoryId 	= 	$this->getRequest()->getParam('category', false);
		echo $category_id; exit;	
		
		if($categoryId !=''){
			$category 	= 	$this->_categoryFactory->create()->load($categoryId);
			$collection = 	$this->_productCollectionFactory->create()->addAttributeToSelect(
				'entity_id'
			);
			$collection->addCategoryFilter($category);
			// $products 	= 	$collection->getData();
			$products 	= 	$collection;
			$product_ids	=	[];
			foreach ($products as $product) {
				array_push($product_ids, $product->getId());
			}
			$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
			->getCollection()
			->addFieldToFilter(
			'mageproduct_id',
				['in' => $product_ids]
			)
			->addFieldToSelect('seller_id')
			->distinct(true);
			
			foreach ($seller_product_coll as $product) {
				array_push($seller_arr, $product->getSellerId());
			}
			
			return $seller_arr;
		}
		else{	
		
			$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
			->getCollection()
			->addFieldToFilter(
				'status',
				['eq' => 1]
			)
			->addFieldToSelect('seller_id')
			->distinct(true);
			
			foreach ($seller_product_coll as $product) {
				array_push($seller_arr, $product->getSellerId());
			}
			
			return $seller_arr;
			
		}
		echo "<pre>"; print_r($seller_arr); exit;
	}
	
	public function getSellersForHome(){
		$seller_arr = array();
		$offsellers	=	$this->getOffSellerCollection();
		$seller_product_coll = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
		->getCollection()
		->addFieldToFilter(
			'status',
			['eq' => 1]
		)
		->addFieldToSelect('seller_id')
		->distinct(true);
		foreach ($seller_product_coll as $product) {
			array_push($seller_arr, $product->getSellerId());
		}
		$collection = $this->_sellerlistCollectionFactory->create()->addFieldToSelect(
			'*'
		)
		->addFieldToFilter(
			'seller_id',
			['in' => $seller_arr]
		)
		->addFieldToFilter(
			'is_seller',
			['eq' => 1]
		)->addFieldToFilter(
			'seller_id',
			['nin' => $offsellers]
		)
		->setOrder(
			'entity_id',
			'desc'
		);
		return $collection;
	}
	
	
	public function getCategoryView() {
		return $this->categoryView;
	}
	/**
	* Return categories helper
	*/   
	public function getCategoryHelper()
	{
		return $this->_categoryHelper;
	}
	/**
	* Return top menu html
	* getHtml($outermostClass = '', $childrenWrapClass = '', $limit = 0)
	* example getHtml('level-top', 'submenu', 0)
	*/   
	public function getHtml()
	{
		return $this->topMenu->getHtml();
	}
	/**
	* Retrieve current store categories
	*
	* @param bool|string $sorted
	* @param bool $asCollection
	* @param bool $toLoad
	* @return \Magento\Framework\Data\Tree\Node\Collection|\Magento\Catalog\Model\Resource\Category\Collection|array
	*/    
	public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
	{
		return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
	}
	
	public function getAreas($c_id=null){
		$city_id 	= 	$this->getRequest()->getParam('city_id', false);
		if(!$city_id){
			$city_id	=	$c_id;
		}
		$area_id 	= 	$this->getRequest()->getParam('area_id', false);
		if($city_id==""){
			$city_id	=	596;
		}
		if($area_id==""){
			$area_id	=	6;
		}
		$collection = $this->_regionCollection->create()->addFieldToSelect(
			['region_id','default_name']
		)
		->addFieldToFilter(
			'region_id',
			['eq' => $city_id]
		)
		->setOrder(
			'default_name',
			'asc'
		);

		
		$collectionarea = $this->_areaCollection->create()->addFieldToSelect(
			['area_id','default_name']
		)
		->addFieldToFilter(
			'region_id',
			['eq' => $city_id]
		)
		->setOrder(
			'default_name',
			'asc'
		);

		$region_data 	=	$collection->getData();
		if(isset($region_data[0])){
			$region_data	=	$region_data[0];
		}
		$area_data 		=	$collectionarea->getData();
		return array('region'=>$region_data,'area'=>$area_data,'current_area_id'=>$area_id);
	}
	public function getCustomerSession() 
    {
        return $this->_customerSession;
    }
}
