<?php
namespace Webkul\Marketplace\Block;
/**
 * Webkul Marketplace Seller Feedbackcollection Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
/**
 * Seller Feedbackcollection
 */
class Feedbackcollection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $session;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory
     */
    protected $_feedbackCollectionFactory;

    /** @var \Webkul\Marketplace\Model\Feedback */
    protected $feedbackList;

    /**
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Customer $customer
     * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        \Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory $feedbackCollectionFactory,
        array $data = []
    ) {
        $this->_feedbackCollectionFactory = $feedbackCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->Customer = $customer;
        $this->Session = $session;
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
    public function getCollection()
    {
        if (!$this->feedbackList) {
            $collection = array();        
            $partner = $this->getProfileDetail();
            if(count($partner)){
                $collection = $this->_feedbackCollectionFactory->create()
                                ->addFieldToFilter(
                                    'status',
                                    ['neq' => 0]
                                )
                                ->addFieldToFilter(
                                    'seller_id',
                                    ['eq' => $partner->getSellerId()]
                                )
                                ->setOrder('entity_id','DESC');
            }
            $this->feedbackList = $collection;
        }
        return $this->feedbackList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.feedback.pager'
            )
            ->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
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

    /**
     * @return array
     */
    public function getProfileDetail($value='')
    {
        $shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getFeedbackUrl();
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
    
    public function getFeed()
    {
        $partner = $this->getProfileDetail();
        if(count($partner)){
            return $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getFeedTotal($partner->getSellerId());
        }else{
            return array();
        }
    }

    public function getFeedCollection()
    {
        $collection = array();
        $partner = $this->getProfileDetail();
        if(count($partner)){
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Feedback')
                        ->getCollection()
                        ->addFieldToFilter('status',array('neq'=>0))
                        ->addFieldToFilter('seller_id',array('eq'=>$partner->getSellerId()))
                        ->setOrder('entity_id','DESC')
                        ->setPageSize(2)
                        ->setCurPage(1);
        }
        return $collection;
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
