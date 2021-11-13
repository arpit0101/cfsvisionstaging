<?php
namespace Location\Custom\Block;

use Magento\Catalog\Helper\Category;

class Categories extends \Magento\Framework\View\Element\Template
{

    protected $_categoryHelper;

    protected $_categoryFlatConfig;
    protected $_categoryFactory;

    public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,  
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	 ) {
		parent::__construct($context, $data);
		$this->customerSession = $customerSession;
		$this->_objectManager = $objectManager;
	}

    /*
     * Return categories helper
     */
    public function getCategories(
        $sorted = false,
        $asCollection = false,
        $toLoad = true
    ) {
        return $this->_categoryHelper->getStoreCategories($sorted, $asCollection, $toLoad);
    }
    
    // sub category
    public function getChildCategories($category)
    {
        if ($this->_categoryFlatConfig->isFlatEnabled() &&
            $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }
        return $subcategories;
    }

	public function getCategorymodel($id)
    {
        $_category = $this->_categoryFactory->create();
        $_category->load($id);
        return $_category;
    }
}