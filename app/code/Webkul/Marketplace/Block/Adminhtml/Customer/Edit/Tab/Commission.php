<?php
namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Commission extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

	 const COMM_TEMPLATE = 'customer/commission.phtml';

     /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
            \Magento\Framework\Registry $registry,
            \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }
     
	 /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::COMM_TEMPLATE);
        }
        return $this;
    }

    public function getCommision(){
        $collection = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getSalesPartnerCollection();
        if(count($collection)) {
            foreach ($collection as $value) {
                $rowcom = $value->getCommissionRate();
            }   
        }
        else
        {
          $rowcom = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getConfigCommissionRate(); 
        }
        $tsale=0;
        $tcomm=0;
        $tact=0;
        $collection1 = $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getSalesListCollection();    
        foreach ($collection1 as $key) {
                $tsale+=$key->getTotalAmount();
                $tcomm+=$key->getTotalCommision();
                $tact+=$key->getActualSellerAmount();
            }       

        return ['total_sale'=>$tsale,'total_comm'=>$tcomm,'actual_seller_amt'=>$tact,'current_val'=>$rowcom];
    }

    public function getCurrencySymbol(){
        $currencySymbol =  $this->_objectManager->create('Webkul\Marketplace\Block\Adminhtml\Customer\Edit')->getCurrencySymbol();
        return $currencySymbol;
    }        

}