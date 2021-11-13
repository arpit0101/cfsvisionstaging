<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\CategoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
 
class Category implements CategoryInterface
{
	
	/**
     * @var CategoryRepositoryInterface $categoryRepository
     */
    protected $categoryRepository;
	
	
	protected $_categoryHelper;
	/**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterProvider $filterProvider
     * @param PageFactory $pageFactory
     * @param Page $pageResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param State $appState
     * @param Emulation $emulation
     */
    public function __construct(
        \Magento\Catalog\Helper\Category $categoryHelper,
		CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_categoryHelper = $categoryHelper;
		$this->categoryRepository = $categoryRepository;
    }
    /**
	* Retrieve current store categories
	*
	* @return array
	*/    
	public function getCategories()
	{
		// return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
		$categories 	=	$this->_categoryHelper->getStoreCategories(false , false, true);
		
		$categorys_data 	=	[];
		foreach($categories as $category){
			$categoryObj           					= 	$this->categoryRepository->get($category->getData('entity_id'));
			$category_data							=	$category->getData();
			$category_image							=	$categoryObj->getImageUrl();
			if($category_image){
				$category_data['image']				=	$category_image;
				$category_data['category_image']	=	$category_image;
			}else{
				$category_data['image']				=	"";
				$category_data['category_image']	=	"";
			}
			
			$category_data['name']					=	$category_data['name'];
			$category_data['children_data']			=	[];
			$categorys_data[]						=	$category_data;
		}
		return $categorys_data;
	}
}