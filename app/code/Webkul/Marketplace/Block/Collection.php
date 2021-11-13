<?php
namespace Webkul\Marketplace\Block;
/**
 * Webkul Marketplace Seller Collection Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */


use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Block\Product\AbstractProduct;
/**
 * Seller Product Collection
 */
class Collection extends \Magento\Catalog\Block\Product\ListProduct
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Magento\Catalog\Block\Product\ProductList\Toolbar';

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
	/**
     * @var CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /** 
     * @var \Magento\Catalog\Model\Product 
     */
    protected $productlists;
	protected $_categoryHelper;
	protected $_customerSession;
	protected $_categoryFactory;
    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
	* @param \Magento\Catalog\Model\Layer\Resolver     $layerResolver
	* @param CategoryRepositoryInterface               $categoryRepository
	* @param \Magento\Framework\Stdlib\StringUtils     $stringUtils
    */
    public function __construct(
		Category $category,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
		CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
		
        array $data = []
    ) {
		$this->Category = $category;
        $this->_postDataHelper = $postDataHelper;
        $this->_objectManager = $objectManager;
        $this->urlHelper = $urlHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_regionCollection 			= 	$regionCollection;
        $this->_categoryFactory 			= 	$categoryFactory;
		$this->_categoryRepository 			= $categoryRepository;
		$this->_areaCollection 				= 	$areaCollection;
		$this->stringUtils 					= $stringUtils;
		
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function _getProductCollection()
    {
		$session_object 	= $this->_objectManager->get('Magento\Customer\Model\Session');
		$response_object 	=  $this->_objectManager->get('\Magento\Framework\App\Response\Http');
		$redirect_interface =  $this->_objectManager->get('\Magento\Framework\App\Response\RedirectInterface');
    
		if(isset($_GET['remove_cate'])){
			$categories 			=	$session_object->getSessionCategory();
			
			unset($categories[$_GET['remove_cate']]);
			$session_object->setSessionCategory($categories);
		}
		if(isset($_GET['c'])){   
			$categories 			=	$session_object->getSessionCategory();
			$categories				=	[];
			$categories[$_GET['c']]	=	$_GET['c'];
			$session_object->setSessionCategory($categories);
		}
		
        if (!$this->productlists) {
			
            if(isset($_GET['c'])){     
                $cate = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($_GET["c"]);
            }   
            $partner = $this->getSellerDetail();
			//echo  '<pre>'; print_r($partner); die("test");
			if($partner=='empty'){
				$redirectUrl = $this->getUrl('region/');
				$redirect_interface->redirect($response_object, $redirectUrl);
			}else{
				$seller_id 	= $partner->getSellerId();
			}
			$productname	=	$this->getRequest()->getParam('name');
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
								
			$layer = $this->getLayer();
			
			$collection = $layer->getProductCollection();
            $collection->addAttributeToSelect('*');
			
            //$collection = $this->_productCollectionFactory->create()->addAttributeToSelect('*');
			$catalog_category_entity 	=	$this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('catalog_category_entity');
            if(isset($_GET['c']) || isset($_GET['remove_cate']) ){
				$categories 			=	$session_object->getSessionCategory();
				$all_categories 		=	array();
				if($categories){
					$all_categories		=	$this->getSubcategories($categories, $all_categories);
					$collection->addCategoriesFilter(array('in' => $all_categories));
				}
			}
			if(isset($_GET['search'])){  
				$collection->addAttributeToFilter('name', array('like' => '%'.$_GET['search'].'%'));
            }
			/* $collection->getSelect()->join(
				['saller_product' => $collection->getTable('marketplace_product')],
				'saller_product.mageproduct_id = e.entity_id',
				'saller_product.status = 1',
				'saller_product.seller_id = '.$seller_id
			); */
			// $collection->getSelect()->joinLeft(array('saller_product' => $collection->getTable('marketplace_product')),
                                               // 'saller_product.mageproduct_id = e.entity_id AND saller_product.status = 1 AND saller_product.seller_id = '.$seller_id);
            // $collection->addAttributeToFilter('entity_id', 'saller_product.mageproduct_id');
			// echo $collection->getSelect()->__toString();die;
			$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
            $collection-> addAttributeToFilter('visibility', array('in' => array(4) ));
            $collection-> addAttributeToFilter('status', array('in' => array(1) ));
			// die($collection->getSelect()->__toString());
			
			//$this->prepareSortableFieldsByCategory($layer->getCurrentCategory());
			
			
            $this->productlists = $collection;
        }
        return $this->productlists;
    }
	
	// get all multilevel categories
	
	function getSubcategories($categories, $all_categories) {
		
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
	

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();

        // use sortable parameters
        $orders = $this->getAvailableOrders();
        if ($orders) {
            $toolbar->setAvailableOrders($orders);
        }
        $sort = $this->getSortBy();
        if ($sort) {
            $toolbar->setDefaultOrder($sort);
        }
        $dir = $this->getDefaultDirection();
        if ($dir) {
            $toolbar->setDefaultDirection($dir);
        }
        $modes = $this->getModes();
        if ($modes) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getProductCollection()]
        );

        $this->_getProductCollection()->load();

        $partner=$this->getProfileDetail();     
        if($partner->getShoptitle()!='') {
            $shop_title = $partner->getShopTitle();
        }
        else {
            $shop_title = $partner->getShopUrl();
        }

        return parent::_beforeToHtml();
    }
    
    public function getDefaultDirection()
    {
        return 'asc';
    }

    public function getSortBy()
    {
        return 'entity_id';
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    public function getToolbarHtml()   
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * @return array
     */
    public function getProfileDetail($value='')
    {
        $shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getCollectionUrl();
        if(!$shop_url){
            $shop_url = $this->getRequest()->getParam('shop');            
        }
        if($shop_url){
            $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url',array('eq'=>$shop_url));
				//	echo "<pre>";
            foreach($data as $seller){ 
			//print_r($seller);
			return $seller;}
        }
    }
	
	public function test($searchq)
	{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
             $collection = $productCollection->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter(
									'seller_id',
									['eq' => getSellerId()]			
                              )
            ->load();
			
			
            $querydata 		= 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
								->getCollection()
								->addFieldToFilter(
									'seller_id',
									['eq' => getSellerId()]
								)
								->addFieldToFilter(
									'status',
									['eq' =>1]
								)
								->addFieldToSelect('mageproduct_id')
								->setOrder('mageproduct_id');
						
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
                '*'
            );
            if(array_key_exists('c', $_GET)){
                $collection->addCategoryFilter($cate);
            }
            $collection->addAttributeToFilter('status', array('eq' => 1 ));
            $collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
            $collection-> addAttributeToFilter('visibility', array('in' => array(4) ));
            $this->productlists = $collection;
			return $this->productlists;
	}
	
	 public function getCategoryList(){    	
		$seller_id=$this->getProfileDetail()->getSellerId();
		$querydata = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
							->getCollection()
			                ->addFieldToFilter('seller_id', array('eq' => $seller_id))
			                ->addFieldToFilter('status', array('neq' => 2))
			                ->addFieldToSelect('mageproduct_id')
			                ->setOrder('mageproduct_id');
        $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection();
        $collection->addAttributeToSelect('*');
        
        $collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
        $collection-> addAttributeToFilter('visibility', array('in' => array(4) )); 
        $collection->addStoreFilter();
        
        $collectionConfigurable = $this->_objectManager->create('Magento\Catalog\Model\Product')
    									->getCollection()
		        						->addAttributeToFilter('type_id', array('eq' => 'configurable'))
		        						->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));

		$outOfStockConfis = array();
		foreach ($collectionConfigurable as $_configurableproduct) {
		    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_configurableproduct->getId());
		    if (!$product->getData('is_salable')) {
		       $outOfStockConfis[] = $product->getId();
		    }
		}
		if(count($outOfStockConfis)){
			$collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
		}

		$collectionBundle = $this->_objectManager->create('Magento\Catalog\Model\Product')
								->getCollection()
        						->addAttributeToFilter('type_id', array('eq' => 'bundle'))
        						->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
		$outOfStockConfis = array();
		foreach ($collectionBundle as $_bundleproduct) {
		    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_bundleproduct->getId());
		    if (!$product->getData('is_salable')) {
		       $outOfStockConfis[] = $product->getId();
		    }
		}
		if(count($outOfStockConfis)){
			$collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
		}

		$collectionGrouped = $this->_objectManager->create('Magento\Catalog\Model\Product')
								->getCollection()
        						->addAttributeToFilter('type_id', array('eq' => 'bundle'))
        						->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
		$outOfStockConfis = array();
		foreach ($collectionGrouped as $_groupedproduct) {
		    $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($_groupedproduct->getId());
		    if (!$product->getData('is_salable')) {
		       $outOfStockConfis[] = $product->getId();
		    }
		}
		if(count($outOfStockConfis)){
			$collection->addAttributeToFilter('entity_id',array('nin' => $outOfStockConfis));
		}
		
		$products = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
							->getCollection()
							->addFieldToFilter('mageproduct_id',array('in'=>$collection->getData()))
							->addFieldToSelect('mageproduct_id');

		$eavAttribute = $this->_objectManager->get('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $pro_att_id = $eavAttribute->getIdByCode("catalog_category","name");

        $storeId = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getCurrentStoreId();
        if(!isset($_GET["c"])){
			$_GET["c"] ='';
		}
       	if(!$_GET["c"]){
        	$parentid = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getRootCategoryIdByStoreId($storeId);
        }else{
        	$parentid = $_GET["c"];
        }
        $catalog_category_product = $this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('catalog_category_product');
        $catalog_category_entity =$this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('catalog_category_entity');
        $catalog_category_entity_varchar = $this->_objectManager->create('Webkul\Marketplace\Model\ResourceModel\Seller\Collection')->getTable('catalog_category_entity_varchar');

        $products->getSelect()
        ->join(array("ccp" => $catalog_category_product),"ccp.product_id = main_table.mageproduct_id",array("category_id" => "category_id"))
        ->join(array("cce" => $catalog_category_entity),"cce.entity_id = ccp.category_id",array("parent_id" => "parent_id"))->where("cce.parent_id = '".$parentid."'")
        ->columns('COUNT(*) AS countCategory')
        ->group('category_id')
        ->join(array("ce1" => $catalog_category_entity_varchar),"ce1.entity_id = ccp.category_id",array("catname" => "value"))->where("ce1.attribute_id = ".$pro_att_id)
        ->order('catname');
		$products->getSelect()->__toString();die;
        return $products;
	} 
	public function getAreas(){
		$city_id 	= 	$this->getRequest()->getParam('city_id', false);
		$area_id 	= 	$this->getRequest()->getParam('area_id', false);
		if($city_id==""){
			$city_id	=	575;
		}
		if($area_id==""){
			$area_id	=	3;
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
	
	
	public function getSellerDetail($value='')
    {
		$session_object = $this->_objectManager->get('Magento\Customer\Model\Session');
		$city_id = $session_object->getCityId();
		$area_id = $session_object->getAreaId();
		
		$city_name = $this->getRequest()->getParam('city'); 
		$area_name = $this->getRequest()->getParam('area'); 
		
		// echo '<pre>'; print_r($city_id);die;
		//echo $area_name.'=='.$city_name.'==='.$city_id.'===='.$area_id; die("testt");
		if(($city_id=="" && $city_name!="") || ($area_id=="" && $area_name!="")){
			
			$citycollection = $this->_regionCollection->create()->addFieldToSelect(
				['region_id','default_name']
			)
			->addFieldToFilter(
				'default_name',
				['like' => $city_name]
			);
			if($citycollection){
				$city_detail 	=	$citycollection->getData();
				if(isset($city_detail[0]['region_id'])){
					$session_object->setCityId($city_detail[0]['region_id']);
					$city_id	=	$city_detail[0]['region_id'];	
				}
			}
			$areacollection = $this->_areaCollection->create()->addFieldToSelect(
				['area_id','default_name']
			)
			->addFieldToFilter(
				'default_name',
				['like' => $area_name]
			);
			if($areacollection){
				$area_detail 	=	$areacollection->getData();
				if(isset($area_detail[0]['area_id'])){
					$session_object->setAreaId($area_detail[0]['area_id']);
					$area_id	=	$area_detail[0]['area_id'];	
				}
			}	
		}
		
		$shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getCollectionUrl();
        if(!$shop_url){
            $shop_url = $this->getRequest()->getParam('shop');            
        }
		if($shop_url){
		    $data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
            ->getCollection()
            ->addFieldToFilter('shop_url',array('eq'=>$shop_url))
            ->addFieldToFilter('region_id',array('finset'=>[$city_id]));
			if(count($data)>0){	
				foreach($data as $seller){ 
					return $seller;
				}
			}else{
				return "empty";
			}
		}
	}
}