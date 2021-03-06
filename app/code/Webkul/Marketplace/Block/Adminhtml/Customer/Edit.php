<?php
namespace Webkul\Marketplace\Block\Adminhtml\Customer;
class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * 
     * @var null
     */
    protected $_objectManager = null;
    /**
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_country;
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Directory\Model\ResourceModel\Country\Collection $country,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_country = $country;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    public function getSellerInfoCollection(){
        $customerId = $this->getRequest()->getParam('id');
        $data = array();
        if ($customerId != '') {
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()
                                            ->addFieldToFilter('seller_id',$customerId);
            $user = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($customerId);
			//print_r($user->getAreaId());die;
            $name=explode(' ',$user->getName());
			$bannerpic="";
			$logopic="";
			$countrylogopic="";
            foreach ($collection as $record) {
                $data = $record->getData();
                $bannerpic=$record->getBannerPic();
                $logopic=$record->getLogoPic();
                $countrylogopic=$record->getCountryPic();
                if(strlen($bannerpic)<=0){$bannerpic='banner-image.png';}
                if(strlen($logopic)<=0){$logopic='noimage.png';}
                if(strlen($countrylogopic)<=0){$countrylogopic='';}
            }
            $data['firstname'] = $name[0];
            $data['lastname'] = $name[1];
            $data['email'] = $user->getEmail();
            $data['banner_pic'] = $bannerpic;
            $data['logo_pic'] = $logopic;
            $data['country_pic'] = $countrylogopic;
            $data['country_id'] = $user->getCountryId();
            $data['region_id'] = $user->getRegionId();
            $data['area_id'] = $user->getAreaId();
		
            return $data;
        }
    }
    /**
     * @return Mixed.
     */
    public function getCountryList(){
        return $this->_country->loadByStore()->toOptionArray(true);
    }
    public function getPaymentMode(){
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()
                                            ->addFieldToFilter('seller_id',$customerId);
        $data = '';
        foreach ($collection as $record) {
            $data=$record->getPaymentSource();
        }
        return $data;
    }

    /**
     * @return Webkul\Marketplace\Model\Saleperpartner
     */
    public function getSalesPartnerCollection(){
        $customerId =$this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleperpartner')->getCollection()->addFieldToFilter('seller_id',$customerId);
        return $collection;
    }
    /**
     * @return Webkul\Marketplace\Model\Saleslist
     */
    public function getSalesListCollection(){
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')->getCollection()->addFieldToFilter('seller_id',$customerId);
        return $collection;
    }
    /**
     * @return string
     */
    public function getConfigCommissionRate(){
        return $this->_helper->getConfigCommissionRate();
    }
    /**
     *
     * @param  Decimal $price 
     * @return [type]        [description]
     */
    public function getCurrencySymbol(){
       return $this->_currency->getCurrencySymbol();
    }
    /**
     * @return Webkul\Marketplace\Model\Product
     */
    public function getProductCollection(){
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')->getCollection()
                    ->addFieldToFilter('seller_id',$customerId)
                    ->addFieldToFilter('adminassign',1);
        return $collection;
    }
     /**
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceUserCollection(){
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()->addFieldToFilter('seller_id',$customerId);
        return $collection;
    }

    public function getAllCustomerCollection(){
        $collection = $this->_objectManager->create('Magento\Customer\Model\Customer')->getCollection();
        return $collection;
    }
}
