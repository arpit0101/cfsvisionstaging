<?php
/**
 * Marketplace Product GetAttributes controller.
 *
 */
namespace Webkul\Marketplace\Controller\Product\Attribute;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\ConfigurableProduct\Model\AttributesList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GetAttributes extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_attributesList;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param AttributesListInterface $attributesList
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        AttributesList $attributesList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
        $this->_attributesList = $attributesList;
        parent::__construct($context);
    }
    

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $this->storeManager->setCurrentStore(\Magento\Store\Model\Store::ADMIN_CODE);
        $attributes = $this->_attributesList->getAttributes($this->getRequest()->getParam('attributes'));
        $this->getResponse()->representJson($this->jsonHelper->jsonEncode($attributes));
    }
}