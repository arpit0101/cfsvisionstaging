<?php
namespace Webkul\Marketplace\Block\Transaction;
/**
 * Webkul Marketplace Transaction History Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Magento\Framework\UrlInterface;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

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
    protected $_transactionCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    protected $sellerTransactionLists;

    /**
    * @param Context $context
    * @param array $data
    * @param Customer $customer
    * @param Order $order
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Order $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory $transactionCollectionFactory,
        array $data = []
    ) {
        $this->_transactionCollectionFactory = $transactionCollectionFactory;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }


    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Transactions'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    

    /**
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Sellertransaction\Collection
     */

    public function getAllTransaction()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        if (!$this->sellerTransactionLists) {
            $ids = array();
            $orderids = array();

            $tr_id = '';
            $filter_data_to = '';
            $filter_data_frm = '';
            $from = null;
            $to = null;

            if(isset($_GET['tr_id'])){
                $tr_id = $_GET['tr_id'] != ""?$_GET['tr_id']:"";
            }
            if(isset($_GET['from_date'])){
                $filter_data_frm = $_GET['from_date'] != ""?$_GET['from_date']:"";
            }
            if(isset($_GET['to_date'])){
                $filter_data_to = $_GET['to_date'] != ""?$_GET['to_date']:"";
            }

            $collection = $this->_transactionCollectionFactory->create()->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $customerId]
            );        

            if($filter_data_to){
                $todate = date_create($filter_data_to);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if($filter_data_frm){
                $fromdate = date_create($filter_data_frm);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if($tr_id){
                $collection->addFieldToFilter(
                    'transaction_id',
                    ['eq' => $tr_id]
                );
            }

            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true,'from' => $from,'to' =>  $to]
            ); 

            $collection->setOrder(
                'created_at',
                'desc'
            );
            $this->sellerTransactionLists = $collection;
        }
        return $this->sellerTransactionLists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllTransaction()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.transaction.pager'
            )->setCollection(
                $this->getAllTransaction()
            );
            $this->setChild('pager', $pager);
            $this->getAllTransaction()->load();
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

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }
}
