<?php
namespace Webkul\Marketplace\Block\Account;
/**
 * Webkul Marketplace Product List Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

class Messages extends \Magento\Framework\View\Element\Template
{
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
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    protected $productlists;

    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Product List'));
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }

        if (!$this->productlists) {
            $filter = '';
            $filter_status = '';
            $filter_date_frm = '';
            $filter_date_to = '';
            $from = null;
            $to = null;

            $sellercollection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                ->getCollection()
                                ->addFieldToFilter(
                                    'seller_id',
                                    ['eq' => $customerId]
                                );
            $products=array();
            foreach($sellercollection as $data){
                array_push($products,$data->getMageproductId());
            }

            if(isset($_GET['s'])){
                $filter = $_GET['s'] != ""?$_GET['s']:"";
            }
            if(isset($_GET['status'])){
                $filter_status = $_GET['status'] != ""?$_GET['status']:"";
            }
            if(isset($_GET['from_date'])){
                $filter_date_frm = $_GET['from_date'] != ""?$_GET['from_date']:"";
            }
            if(isset($_GET['to_date'])){
                $filter_date_to = $_GET['to_date'] != ""?$_GET['to_date']:"";
            }
            if($filter_date_to){
                $todate = date_create($filter_date_to);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if($filter_date_frm){
                $fromdate = date_create($filter_date_frm);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $collection = $this->_productCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'name',
                ['like' => "%".$filter."%"]
            );
            if($filter_status){
                $collection->addFieldToFilter(
                    'status',
                    ['eq' => $filter_status]
                );
            }

            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true,'from' => $from,'to' =>  $to]
            ); 

            $collection->addFieldToFilter(
                'entity_id',
                ['in' => $products]
            )->setOrder(
                'created_at',
                'desc'
            );          
            $this->productlists = $collection;
        }

        return $this->productlists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.product.list.pager'
            )->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }    

    public function imageHelperObj(){
        return $this->imageHelper;
    }

}
