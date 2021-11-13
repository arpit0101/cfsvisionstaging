<?php

namespace Dotsquares\ImportExport\Controller\Adminhtml\Index;


class Index extends \Magento\Backend\App\Action
{
    protected $_categoryCollection;
    protected $_catFactory;
    protected $_catObj;
	protected $dataHelper;
	protected $catMode;
	protected $catSortby;
	protected $catLayout;
	protected $cmsBlockRepository;
	protected $csvProcessor;

	public function __construct(\Magento\Backend\App\Action\Context $context,
	\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
	\Magento\Catalog\Model\CategoryFactory $catFactory,
	\Magento\Catalog\Model\Category $catObj, 
	\Dotsquares\ImportExport\Helper\Data $dataHelper,
	\Magento\Catalog\Model\Category\Attribute\Source\Mode $catMode,
	\Magento\Catalog\Model\Category\Attribute\Source\Sortby $catSortby,
	\Magento\Catalog\Model\Category\Attribute\Source\Layout $catLayout,
	\Magento\Cms\Model\Block $cmsBlockRepository,
	\Magento\Framework\File\Csv $csvProcessor)
	{ 
		parent::__construct($context);
		$this->_categoryCollection = $categoryCollection;
		$this->_catFactory = $catFactory;
		$this->_catObj = $catObj;
		$this->dataHelper = $dataHelper;
		$this->catMode = $catMode;
		$this->catSortby = $catSortby;
		$this->catLayout = $catLayout;
		$this->cmsBlockRepository = $cmsBlockRepository;
		$this->csvProcessor = $csvProcessor;
    }
	
	public function execute()
    {
      if($this->getRequest()->getParam('export')){
			  $heading = [
          __('ID'),
		  __('Name'),
		  __('Path'),
		  __('Position'),
		  __('Is Active'),
		  __('Url Key'),
		  __('Description'),
		  __('Image'),
		  __('Page Title'),
          __('Meta Keywords'),
          __('Meta Description'),
		  __('Include In Menu'),
		  __('Display Mode'),
		  __('CMS Block'),
		  __('Is Anchor'),
		  __('Availabe Sort By'),
		  __('Default Sort By'),
		  __('Page Layout'),
		  __('Custom Layout Update')
     ];
	 $ID = $this->getRequest()->getParam('cat_id'); 
	 if($ID != 1){
		unset($heading[0]);
	 }
	 if (!file_exists("var/export")) {
		mkdir("var/export", 0777, true);
	 }
     $outputFile = "var/export/ListCategories.csv";
     $file = fopen($outputFile, 'w')or die("Unable to open file!");
     fputcsv($file, $heading);
	 $data = array();
	 $categoryFactory = $this->_categoryCollection->create();
	 $categories = $categoryFactory->addAttributeToSelect('*');
	 $catArray = array();
	 foreach ($categories as $category) {
		/*echo "<pre>";
		print_r($category->getData());*/
		$catArray [$category->getId()] = $category->getName();
	 }
	 foreach ($categories as $category) {
		if ($category->getLevel() > 1) {
		$data[$category->getId()] = array(
		'id'               => $category->getId(),
		'name'             => $category->getName(),
		'path'             => $this->getCatPath($category->getPath(),$category->getId(),$catArray),
		'position'         => $category->getPosition(),
		'is_active'        => $this->getYesNo($category->getIsActive()),
		'urlKey'           => $category->getUrlKey(),
		'description'      => $category->getDescription(),
		'image'      => $category->getImage(),
		'metaTitle'        => $category->getMetaTitle(),
		'metaKeywords'     => $category->getMetaKeywords(),
		'metaDescription'  => $category->getMetaDescription(),
		'include_in_menu'  => $this->getYesNo($category->getIncludeInMenu()),
		'display_mode'     => $this->getDisplayMode($category->getDisplayMode()),
		'landing_page'     => $this->getStaticBlock($category->getLandingPage()),
		'is_anchor'        => $this->getYesNo($category->getIsAnchor()),
		'available_sort_by'=> $this->getProductSortBy(implode($category->getAvailableSortBy(),',')),
		'default_sort_by'  => $this->getProductSortBy($category->getDefaultSortBy()),
		'page_layout'      => $this->getPageLayout($category->getPageLayout()),
		'custom_design'    => $category->getCustomDesign()
		
	);
	if($ID != 1){
		unset($data[$category->getId()]['id']);
	}
	fputcsv($file, $data[$category->getId()]);
     //$this->csvProcessor->saveData($file, $data[$category->getId()]);
    } //endif
	}
	$this->downloadCsv($outputFile);
	fclose($file);
	}
		$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
	}
	
	public function downloadCsv($file)
	{
		if (file_exists($file)) {
         //set appropriate headers
         header('Content-Description: File Transfer');
         header('Content-Type: application/csv');
         header('Content-Disposition: attachment; filename='.basename($file));
         header('Expires: 0');
         header('Cache-Control: must-revalidate');
         header('Pragma: public');
         header('Content-Length: ' . filesize($file));
         ob_clean();flush();
         readfile($file);
		}
	}
	public function getYesNo($val) {
		if ($val == 1) return 'Yes';
		else return 'No';
	}
	public function getCatPath($catpath,$catId,$catArr) {
		$catUrlKey = explode('/',$catpath);
		unset($catUrlKey[0]);
		$catUrlKey = array_diff($catUrlKey, [$catId]);
		$catName = array();
		foreach($catUrlKey as $catId)
		{
			$catName[] = $catArr[$catId];
		}
		$catNameString = implode("/",$catName);
		return $catNameString;
	}
	protected $_displayModes;
	public function getDisplayMode($val) {
		if (is_null($this->_displayModes)) {
			foreach ($this->catMode->getAllOptions() as $mode) {
				$this->_displayModes['value'][] = $mode['value'];
				$this->_displayModes['label'][] = $mode['label'];
			}
		}
		return str_replace($this->_displayModes['value'], $this->_displayModes['label'], $val);
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
		return str_replace($this->_productSortBy['value'], $this->_productSortBy['label'], $val);
	}
	protected $_pageLayout;
	public function getPageLayout($val) {
		if (is_null($this->_pageLayout)) {
			$layouts = $this->catLayout->getAllOptions();
			foreach ($layouts as $layout) {
				$layoutlabel = (string)$layout['label'];
				$layoutval = (string)$layout['value'];
				$this->_pageLayout[$layoutval] = $layoutlabel;
			}
		}
		return $this->_pageLayout[$val];
	}
}

?>