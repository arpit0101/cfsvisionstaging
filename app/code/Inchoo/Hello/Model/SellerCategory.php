<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\SellerCategoryInterface;
use Magento\Catalog\Model\Category;

use Magento\Catalog\Model\Product; 
class SellerCategory implements SellerCategoryInterface
{
	/**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $category;
	protected $_productCollectionFactory;
    protected $_categoryTree;
	protected $_helper;
    protected $_categoryCollectionFactory;
    protected $_categoryFactory;
	protected $_categoryHelper;
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		Category $category,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Block\Adminhtml\Category\Tree $categoryTree,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Webkul\Marketplace\Helper\Data $helper,
        array $data = []
		) {
		
		$this->Category = $category;
        $this->_objectManager = $objectManager;
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_categoryTree = $categoryTree;
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_categoryFactory = $categoryFactory;
		$this->_helper = $helper;
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	
	public function getStoreCategories($seller_id)
	{
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$cate_file_path 	=	__DIR__."/json/categories".$seller_id."_".$store->getCode().".json";
		
		// if(!file_exists($cate_file_path)){
			$querydata 			= 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
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
							
			$collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
				'entity_id'
			);
			$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
			$collection-> addAttributeToFilter('visibility', array('in' => array(4) ));
			
			$sellercategories 		= 	[];
			$products 	=	$collection; 
			foreach ($products as $product){
				$sellercategories = array_merge($sellercategories, $product->getCategoryIds());
			}
			$sellercategories	=	array_unique($sellercategories);
			
			$collectioncat 		= $this->_categoryCollectionFactory->create();
			$collectioncat->addAttributeToSelect('*'); 
			$collectioncat->addAttributeToFilter('entity_id', array('in' => $sellercategories));
			$collectioncat->addIsActiveFilter();
			
			$categories_info		=	array();
			$categories_tree		=	array();
			$category_tree_path		=	array();
			foreach($collectioncat as $category_data){
				$category_path 		=	explode("/", $category_data->getData('path'));
				
				// remove first and second default category
				unset($category_path[0]);
				unset($category_path[1]);
				array_reverse($category_path);
				$newtree	=	array();
				
				$categories_info[$category_data['entity_id']]					=	$category_data->getData();
				$categories_info[$category_data['entity_id']]['category_path']	=	$category_path;
				
				if(count($category_path)>0){
					$this->create_array_tree($category_tree_path, $category_path,  0);
				}
			}
			$categoriesdata 			=	[];
			$categories 				=	$this->generateSubCategoryTreeHtml($category_tree_path, $categories_info, $seller_id, $categoriesdata, $products);
			// @file_put_contents($cate_file_path, json_encode($categories));
		// }else{
			// $categories	=	json_decode(file_get_contents($cate_file_path), true);
		// }
		return $categories;
	}
/**
 * Returns greeting message to user
 *
 * @api
 * @param string $name Users name.
 * @param string $area area
 * @return string Greeting message with users name.
 */	
function create_array_tree(&$a, array $path = [], $values )
{
$path = "[ '" . join("' ][ '", $path) . "' ]";
$code =<<<CODE

return \$a{$path}[0]  = \$values;
CODE;
return eval($code);
}
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	function generateSubCategoryTreeHtml($category_tree_path, $categories_info, $seller_id, $categoriesdata = [], $products){
	
		$current_category	=	false;
		
		$cat_products		=	$products;
		
		
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance(); //instance of\Magento\Framework\App\ObjectManager
		$storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
		$currentStore = $storeManager->getStore();
		$mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$mediaUrl = str_replace("/media/","",$mediaUrl);;
		
		foreach($category_tree_path as $category_id=>$category_val){
				
			if($category_id!=0){
				
				if(!isset($categories_info[$category_id])){
					$collectioncat 		= $this->_categoryCollectionFactory->create();
					$collectioncat->addAttributeToSelect('*'); 
					$collectioncat->addAttributeToFilter('entity_id', ['eq' =>$category_id]);
					$collectioncat->addIsActiveFilter();
					$categories_info[$category_id] 		    =	$collectioncat->getData();

					$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$category = $_objectManager->create('Magento\Catalog\Model\Category')
					->load($category_id);
					
					if(isset($categories_info[$category_id][0])){
						$categories_info[$category_id]				=	$categories_info[$category_id][0];
						$categories_info[$category_id]['name'] 		=	$category->getName();
						$categories_info[$category_id]['image'] 	=	$category->getImageUrl();
						$categories_info[$category_id]['imageUrl'] 	=	$mediaUrl;
					}
					
				}
				$category_info						=	[];
				if(count($category_val)>0){
					
					$category_info					=	$categories_info[$category_id];
										
					$childdata 						=	$this->generateSubCategoryTreeHtml($category_val, $categories_info, $seller_id, [], $products);
					if($childdata){		
						$category_info['child']		=	$childdata;						
					}	
					$categoriesdata[]				=	$category_info;
				}else{
					$categoriesdata[]				=	$categories_info[$category_id];
				}
			}
		}
	
		return $categoriesdata;
	}
}