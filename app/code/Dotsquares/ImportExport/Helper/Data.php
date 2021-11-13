<?php
namespace Dotsquares\ImportExport\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_categoryCollection;
	//protected $categoryFactory;
	protected $catModel;
	protected $catMode;
	protected $catSortby;
	protected $catLayout;
	protected $cmsBlockRepository;
	
	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
		//\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Catalog\Model\Category $catModel,
		\Magento\Catalog\Model\Category\Attribute\Source\Mode $catMode,
		\Magento\Catalog\Model\Category\Attribute\Source\Sortby $catSortby,
		\Magento\Catalog\Model\Category\Attribute\Source\Layout $catLayout,
		\Magento\Cms\Model\Block $cmsBlockRepository
    ) {
		$this->_categoryCollection = $categoryCollection;
		//$this->categoryFactory = $categoryFactory;
		$this->catModel = $catModel;
		$this->catMode = $catMode;
		$this->catSortby = $catSortby;
		$this->catLayout = $catLayout;
		$this->cmsBlockRepository = $cmsBlockRepository;
        parent::__construct($context);
    }
	
	public function getAttributes($includeId) {
		$data = array();
		if($includeId == "1"){
		$data[] = array ('field' => 'entity_id', 'label' => 'ID','required' => 'yes');
		}
		/*$data[] = array ('field' => 'entity_id', 'label' => 'ID','required' => 'yes');*/
		$data[] = array ('field' => 'name', 'label' => 'Name','required' => 'yes');
		$data[] = array ('field' => 'parent_id', 'label' => 'Parent ID');
		$data[] = array ('field' => 'path', 'label' => 'Path', 'function' => 'getFullPath','required' => 'yes' );
		$data[] = array ('field' => 'position', 'label' => 'Position');
		$data[] = array ('field' => 'is_active', 'label' => 'Is Active', 'function' => 'getYesNo');
		$data[] = array ('field' => 'url_key', 'label' => 'Url Key');
		$data[] = array ('field' => 'description', 'label' => 'Description');
		$data[] = array ('field' => 'image', 'label' => 'Image');
		$data[] = array ('field' => 'meta_title', 'label' => 'Page Title');
		$data[] = array ('field' => 'meta_keywords', 'label' => 'Meta Keywords');
		$data[] = array ('field' => 'meta_description', 'label' => 'Meta Description');
		$data[] = array ('field' => 'include_in_menu', 'label' => 'Include In Menu', 'function' => 'getYesNo');
		$data[] = array ('field' => 'display_mode', 'label' => 'Display Mode', 'function' => 'getDisplayMode');
		$data[] = array ('field' => 'landing_page', 'label' => 'CMS Block', 'function' => 'getStaticBlock');
		$data[] = array ('field' => 'is_anchor', 'label' => 'Is Anchor', 'function' => 'getYesNo');
		$data[] = array ('field' => 'available_sort_by', 'label' => 'Availabe Sort By', 'function' => 'getProductSortBy');
		$data[] = array ('field' => 'default_sort_by', 'label' => 'Default Sort By', 'function' => 'getProductSortBy');
		$data[] = array ('field' => 'page_layout', 'label' => 'Page Layout', 'function' => 'getPageLayout');
		$data[] = array ('field' => 'custom_layout_update', 'label' => 'Custom Layout Update');
		return $data;
	}
	
	protected $_pathids = array(1);
	public function getPathId($level, $catname) {
		$pathid = false;
		$collection = $this->_categoryCollection->create()->addAttributeToSelect('name')->addFieldToFilter('level', $level);
		$collection->getSelect()->where("path like '".implode('/', $this->_pathids)."/%'");
		foreach($collection as $cat) {
			$catid = $cat->getId();
			$category = $this->catModel->load($catid);
			//echo '<pre>'; print_r($category->getData()); exit;
			if ($category->getName() == $catname) {
				$this->_pathids[] = $category->getId();
				$pathid = true;
				break;
			}
		}
		return $pathid;
	}

	public function getCategoryIdFromPath($path, $catname) {
		$paths = explode('/', $path);
		for ($i=0; $i<count($paths); $i++) {
			$pathid = $this->getPathId($i+1, $paths[$i]);
			if (!$pathid) break;
		}
		if (count($this->_pathids) == count($paths)+1) {
			$pathid = $this->getPathId($i+1, $catname);
			if (!$pathid) return 0;
			else return end($this->_pathids);
		} else return array();
	}
	
	public function getYesNo($val) {
		if (strtolower($val) == 'yes') return 1;
		else return 0;
	}
	
	public function getFullPath($val) {
		$paths = explode('/', $val);
		/*echo "<pre>";
		print_r($paths);
		print_r($this->_pathids);
		print_r(implode('/', $this->_pathids));
		die;*/
		if (count($this->_pathids) >= count($paths)) return implode('/', $this->_pathids);
		else return false;
	}

	protected $_displayModes;
	public function getDisplayMode($val) {
		if (is_null($this->_displayModes)) {
			foreach ($this->catMode->getAllOptions() as $mode) {
				$this->_displayModes['value'][] = $mode['value'];
				$this->_displayModes['label'][] = $mode['label'];
			}
		}
		return str_replace($this->_displayModes['label'], $this->_displayModes['value'], $val);
	}

	protected $_staticBlocks = array();
	public function getStaticBlock($val) {
		if (!$val) return '';

		if (!isset($this->_staticBlocks[$val])) {
			$model = $this->cmsBlockRepository->getById($val, 'identifier');
			$this->_staticBlocks[$val] = array ('id' => $model->getId());
		}
		return $this->_staticBlocks[$val]['id'];
	}

	protected $_productSortBy;
	public function getProductSortBy($val) {
		if (!$val) return '';

		if (is_null($this->_productSortBy)) {
			$sortby = $this->catSortby->getAllOptions();
			foreach ($sortby as $sort) {
				$this->_productSortBy['value'][] = $sort['value'];
				$this->_productSortBy['label'][] = $sort['label'];
			}
		}
		return str_replace($this->_productSortBy['label'], $this->_productSortBy['value'], $val);
	}

	protected $_pageLayout;
	public function getPageLayout($val) {
		if (is_null($this->_pageLayout)) {
			$layouts = $this->catLayout->getAllOptions();
			foreach ($layouts as $layout) {
				$layoutlabel = (string)$layout['label'];
				$this->_pageLayout[$layoutlabel] = $layout['value'];
			}
		}
		return $this->_pageLayout[$val];
	}

			
}