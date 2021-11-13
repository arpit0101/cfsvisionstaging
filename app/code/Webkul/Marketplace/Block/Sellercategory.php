<?php
namespace Webkul\Marketplace\Block;
/**
 * Webkul Marketplace Sellercategory Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\AbstractProduct;
class Sellercategory extends \Magento\Framework\View\Element\Template
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $category;
	
	
	/**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    protected $_categoryTree;
    protected $_helper;
    protected $_categoryCollectionFactory;
    protected $_categoryFactory;
	
    /**
    * @param Context $context
    * @param array $data
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
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
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
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
            foreach($data as $seller){ return $seller;}
        }
    }   
	
	public function getSellerCategoryList(){
		
		
		$partner			=	$this->getProfileDetail();
		$customerSession 	= 	$this->_objectManager->get('Magento\Customer\Model\Session');
		
		$current_category	=	$this->getRequest()->getParam('c');
		$remove_category	=	$this->getRequest()->getParam('remove_cate');
		
		if($current_category=="" && $remove_category==""){
			$customerSession->unsSessionCategory();
		}
		$querydata 			= 	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
							->getCollection()
							->addFieldToFilter(
								'seller_id',
								['eq' => $partner->getSellerId()]
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
		
		$html 				 =	"<ul id='categories-list'>";
		
		$html 				.=	$this->generateSubCategoryTreeHtml($category_tree_path, $categories_info, $partner, "", $products);
		$html 			.=	'</ul>';
		return $html;
		// return array('categorytree'=>$categorytree, 'sellercategories'=>$sellercategories);
	}
	
function create_array_tree(&$a, array $path = [], $values )
{


        $path = "[ '" . join("' ][ '", $path) . "' ]";
        $code =<<<CODE
        
        return \$a{$path}[0]  = \$values;
CODE;
        return eval($code);
}
	
	function generateSubCategoryTreeHtml($category_tree_path, $categories_info, $partner, $subhtml = "", $products){
	
		$current_category	=	$this->getRequest()->getParam('c');
		
		$cat_products		=	$products;
		
		
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
					$countCategory = 0;
					if(isset($categories_info[$category_id][0])){
					    $categories_info[$category_id]				=	$categories_info[$category_id][0];
						$countCategory 		=	sprintf("%02d",$categories_info[$category_id]['children_count']);
					}
					$categories_info[$category_id]['name'] 	=	$category->getName();
					
				}
				$category_url		=	$this->_helper->getRewriteUrl('marketplace/seller/collection/shop/'.$partner['shop_url'])."/?c=".$category_id;
				if(count($category_val)>0){
					
					if($current_category){
						$class 				=	($current_category==$category_id)?'active':'';
					}else{
						$class 				= '';
					}
					
					$subhtml 			.=	'<li class="'.$class.'">';
					$subhtml 			.=	'<a href="'.$category_url.'">
												'.__($categories_info[$category_id]['name']).'           		
											</a>';
										
					$ssubhtml			=	'';		
				
					$ssubhtml 			=	$this->generateSubCategoryTreeHtml($category_val, $categories_info, $partner, $ssubhtml, $products);
					if($ssubhtml){		
						$ssubhtml 			=	'<div class="clear"></div><ul class="submenu">'.$ssubhtml.'</ul>';						
					}
					$subhtml 			.=	$ssubhtml;						
				}else{
					$subhtml 			.=	'<li>';
					$subhtml 			.=	'<a href="'.$category_url.'">
												'.__($categories_info[$category_id]['name']).'           		
												<span class="count">
													'.$countCategory.' <span class="filter-count-label">item</span>
												</span>
											</a>';
				}
				$subhtml 				.=	'</li>';
			}
		}
	
		return $subhtml;
	}
}
