<?php
/**
 * Webkul Marketplace CustomerRegisterSuccessObserver Observer
 *
 * @category    Webkul
 * @package     Webkul_Marketplace
 * @author      Webkul Software
 *
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager; 

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->_date = $date;
        $this->dateTime = $dateTime;
    }

    /**
     * customer register event handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$data = $observer['account_controller'];
        try{
			if(!empty($data->getRequest()->getParam('is_seller')) && $data->getRequest()->getParam('is_seller')==1){
				$param_data = $data->getRequest()->getParams();
				$customer = $observer->getCustomer();

                $profileurlcount =$this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection();
                $profileurlcount->addFieldToFilter('shop_url',$param_data['profileurl']);
				if(!count($profileurlcount)){
    				$status=$this->_objectManager->get('Webkul\Marketplace\Helper\Data')->getIsPartnerApproval()? 0:1;
    				$customerid=$customer->getId();
    				$model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
    				$model->setData('is_seller', $status);
    				$model->setData('shop_url', $param_data['profileurl']);
    				$model->setData('seller_id', $customerid);	
    				$model->setCreatedAt($this->_date->gmtDate());		
    				$model->setUpdatedAt($this->_date->gmtDate());		
    				$model->save();

    				$helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');
                    $admin_storemail = $helper->getAdminEmailId();
                    $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
                    $adminUsername = 'Admin';
                    $senderInfo = [
                        'name' => $customer->getFirstName().' '.$customer->getLastName(),
                        'email' => $customer->getEmail(),
                    ];
                    $receiverInfo = [
                        'name' => $adminUsername,
                        'email' => $adminEmail,
                    ];
                    $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.$customer->getLastName();
                    $emailTemplateVariables['myvar2'] = $this->storeManager->getStore()->getUrl('admin/customer/index/edit', array('id' => $customer->getId()));
                    $emailTemplateVariables['myvar3'] = 'Admin';

                    $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendNewSellerRequest($emailTemplateVariables,$senderInfo,$receiverInfo);
                }else{
                    $this->messageManager->addError(__('This Shop URL already Exists.'));
                }
			}
		}catch(Exception $e){
			$this->messageManager->addError($e->getMessage());
		}
    }
}
