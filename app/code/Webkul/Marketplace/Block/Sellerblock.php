<?php
namespace Webkul\Marketplace\Block;
/**
 * Webkul Marketplace Sellerblock Block
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software Private Limited
 *
 */

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;

/**
 * Sellerblock
 */
class Sellerblock extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $registry
     * @param Customer $customer
     * @param array $data
    */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->Customer = $customer;
        $this->Session = $session;
        $this->_coreRegistry = $registry;
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
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product;
    }

    /**
     * @return array
     */
    public function getProfileDetail($shop_url='')
    {
        
        if($shop_url){
            $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url',array('eq'=>$shop_url));
            foreach($data as $seller){ return $seller;}
        }
    } 

    public function getFollowCollection($shop_url)
    {
        $collection = array();
        $partner = $this->getProfileDetail($shop_url);
        if(count($partner)){
            // echo $this->Session->getCustomerId();
            // echo $partner->getSellerId();
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Follow')
                        ->getCollection()
                        ->addFieldToFilter('user_id',array('eq'=>$this->Session->getCustomerId()))
                        ->addFieldToFilter('seller_id',array('eq'=>$partner->getId()))
                        ->setOrder('entity_id','DESC')
                        ->setPageSize(2)
                        ->setCurPage(1);
        }
        // echo $collection->getSize();
        // print_r(get_class_methods(get_class($collection)));
        return $collection;
    }
          
}
