<?php
namespace Webkul\Marketplace\Block\Product;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Catalog\Block\Product\AbstractProduct;
/**
 * Home Product Collection
 */
class Homerandomitems extends AbstractProduct
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
    public function _getProductCollection()
    {
        $page = $this->getRequest()->getParam('page');
        if (!$this->productlists) {
            if(array_key_exists('c', $_GET)){   
                $cate = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($_GET["c"]);
            }   
            //$partner=$this->getProfileDetail();
            /*$productname=$this->getRequest()->getParam('name');
            $querydata = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
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
                            ->setOrder('mageproduct_id');*/
            $collection = $this->_productCollectionFactory->create()->addAttributeToSelect(
                '*'
            );
            // echo "<pre/>";
            // var_dump(get_class_methods(get_class($collection)));
            if(array_key_exists('c', $_GET)){
                $collection->addCategoryFilter($cate);
            }
            
            //$collection->addAttributeToFilter('entity_id', array('in' => $querydata->getData()));
            //$collection-> addAttributeToFilter('visibility', array('in' => array(4) ))->addFinalPrice()->getSelect()->order('RAND()');
            $collection-> addAttributeToFilter('visibility', array('in' => array(4) ))->addFinalPrice()->getSelect();
            //$numProducts = $this->getNumProducts() ? $this->getNumProducts() : 3;
            $numProducts = 12;
            $collection->setPage($page, $numProducts)->load();
            $this->productlists = $collection;
        }

        return $this->productlists;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        //$toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getProductCollection();
        $this->setSortBy("RAND('entity_id')");
        
        return parent::_beforeToHtml();
    }
    
    public function getDefaultDirection()
    {
        return 'asc';
    }

    public function getSortBy()
    {
        return 'entity_id';
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->getChildBlock('toolbar')->getCurrentMode();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
        $blockName = $this->getToolbarBlockName();
        if ($blockName) {
            $block = $this->getLayout()->getBlock($blockName);
            if ($block) {
                return $block;
            }
        }
        $block = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $block;
    }

    public function getToolbarHtml()   
    {
        return $this->getChildHtml('toolbar');
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getChildHtml('additional');
    }

    /**
     * Get post parameters
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPrice(\Magento\Catalog\Model\Product $product)
    {
        $priceRender = $this->getPriceRender();

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'include_container' => true,
                    'display_minimal_price' => true,
                    'zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }

    /**
     * @return \Magento\Framework\Pricing\Render
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
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

    public function getFollowCounts()
    {
        $collection = array();
        $partner = $this->getProfileDetail();
        if(count($partner)){
            $collectionSize = $this->_objectManager->create('Webkul\Marketplace\Model\Follow')
                        ->getCollection()->addFieldToFilter('seller_id',array('eq'=>$partner->getId()))->getSize();
        }
        // echo $collection->getSize();
        // print_r(get_class_methods(get_class($collection)));
        return $collectionSize;
    }

    public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
    {
        $renderer = $this->getDetailsRenderer($product->getTypeId());
        if ($renderer) {
            $renderer->setProduct($product);
            return $renderer->toHtml();
        }
        return '';
    }

    public function getDetailsRenderer($type = null)
    {
        if ($type === null) {
            $type = 'default';
        }
        $rendererList = $this->getDetailsRendererList();
        if ($rendererList) {
            return $rendererList->getRenderer($type, 'default');
        }
        return null;
    }

    protected function getDetailsRendererList()
    {
        return $this->getDetailsRendererListName() ? $this->getLayout()->getBlock(
            $this->getDetailsRendererListName()
        ) : $this->getChildBlock(
            'homepage.toprenderers'
        );
    }
    
}
