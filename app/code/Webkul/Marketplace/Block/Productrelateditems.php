<?php
namespace Webkul\Marketplace\Block;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Block\Product\AbstractProduct;

/**
 * Seller More Product Collection
 */
class Productrelateditems extends AbstractProduct
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

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

    /**
     * seller id.
     *
     * @var int
     */
    protected $_sellerId;

    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_postDataHelper = $postDataHelper;
        $this->_objectManager = $objectManager;
        $this->urlHelper = $urlHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
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
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function _getProductCollection()
    {
        if (!$this->productlists) {
            if(array_key_exists('c', $_GET)){   
                $cate = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($_GET["c"]);
            }   

            $querydata = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['neq' => $this->_sellerId]
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
            // echo "<pre/>";
            // var_dump(get_class_methods(get_class($collection)));
            if(array_key_exists('c', $_GET)){
                $collection->addCategoryFilter($cate);
            }

            //echo "<pre>"; print_r($querydata->getData()); exit;
            
            $collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
            $collection-> addAttributeToFilter('visibility', array('in' => array(4) ))->addFinalPrice()->getSelect()->order('RAND()');
            $this->productlists = $collection;
        }

        return $this->productlists;
    }

    /**
     * set category Id and set template.
     *
     * @param int $sellerId
     */
    public function setSellerId($sellerId)
    {
        $this->_sellerId = $sellerId;
    }
}
