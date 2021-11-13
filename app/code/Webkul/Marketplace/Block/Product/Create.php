<?php
namespace Webkul\Marketplace\Block\Product;
/**
 * Webkul Marketplace Product Create Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

use Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Category;

class Create extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $category;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param Product $product
    * @param Category $category
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        Product $product,
        Category $category,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->Product = $product;
        $this->Category = $category;
        $this->imageHelper = $context->getImageHelper();
        parent::__construct($context, $data);
        $this->_countryCollectionFactory = $countryCollectionFactory;
    }

    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
    }

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }    

    public function imageHelperObj(){
        return $this->imageHelper;
    }

    /**
     * @return \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    public function getCountryCollection()
    {
        $collection = $this->_countryCollectionFactory->create()->loadByStore();
        return $collection;
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->_scopeConfig->getValue(
            'general/country/destinations',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }

    /**
     * Retrieve list of countries option array
     *
     * @return array
     */
    public function getCountryOptionArray()
    {
        return $options = $this->getCountryCollection()
                ->setForegroundCountries($this->getTopDestinations())
                ->toOptionArray();
    }
}
