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

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Block\Product\AbstractProduct;
/**
 * Seller Product Collection
 */
class Location extends AbstractProduct
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
     * @var \Magento\Catalog\Model\Product 
     */
    protected $productlists;
	protected $_categoryHelper;
	protected $_customerSession;
    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    */
    public function __construct(
		Category $category,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
		
        \PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		
        array $data = []
    ) {
		$this->Category = $category;
        $this->_postDataHelper = $postDataHelper;
        $this->_objectManager = $objectManager;
        $this->urlHelper = $urlHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_regionCollection 			= 	$regionCollection;
		$this->_areaCollection 				= 	$areaCollection;
		
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    
	 
}
