<?php
namespace Webkul\Marketplace\Helper;

use Magento\Customer\Model\Session;
/**
 * Marketplace Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_EMAIL_SELLER_APPROVAL = 'marketplace/email/seller_approve_notification_template';
    const XML_PATH_EMAIL_BECOME_SELLER   = 'marketplace/email/becomeseller_request_notification_template';
    const XML_PATH_EMAIL_SELLER_DISAPPROVE  = 'marketplace/email/seller_disapprove_notification_template';
    const XML_PATH_EMAIL_SELLER_DENY     = 'marketplace/email/seller_deny_notification_template';
    const XML_PATH_EMAIL_PRODUCT_DENY    = 'marketplace/email/product_deny_notification_template';
    const XML_PATH_EMAIL_NEW_PRODUCT     = 'marketplace/email/new_product_notification_template';
    const XML_PATH_EMAIL_EDIT_PRODUCT    = 'marketplace/email/edit_product_notification_template';
    const XML_PATH_EMAIL_DENY_PRODUCT    = 'marketplace/email/product_deny_notification_template';
    const XML_PATH_EMAIL_PRODUCT_QUERY   = 'marketplace/email/askproductquery_seller_template';
    const XML_PATH_EMAIL_SELLER_QUERY    = 'marketplace/email/askquery_seller_template';
    const XML_PATH_EMAIL_ADMIN_QUERY     = 'marketplace/email/askquery_admin_template';
    const XML_PATH_EMAIL_APPROVE_PRODUCT = 'marketplace/email/product_approve_notification_template';
    const XML_PATH_EMAIL_ORDER_PLACED    = 'marketplace/email/order_placed_notification_template';
    const XML_PATH_EMAIL_ORDER_PLACED_MANAGER    = 'marketplace/email/order_placed_manager_notification_template';
    const XML_PATH_EMAIL_ORDER_CANCELED_MANAGER    = 'marketplace/email/order_canceled_manager_notification_template';
    const XML_PATH_EMAIL_ORDER_CANCELED_VENDOR    = 'marketplace/email/order_canceled_vendor_notification_template';
    const XML_PATH_EMAIL_ORDER_INVOICED  = 'marketplace/email/order_invoiced_notification_template';
    const XML_PATH_EMAIL_SELLER_TRANSACTION = 'marketplace/email/seller_transaction_notification_template';
    const XML_PATH_EMAIL_LOW_STOCK       = 'marketplace/email/low_stock_template';    

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    protected $temp_id;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
    * @param Magento\Framework\App\Helper\Context $context
    * @param Magento\Framework\ObjectManagerInterface $objectManager
    * @param Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    * @param Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    * @param Magento\Store\Model\StoreManagerInterface $_storeManager
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder; 
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * Return store configuration value
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/testorders.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('ttttttttttttttt');
        $logger->info(print_r($emailTemplateVariables,true));
        $logger->info(print_r($senderInfo,true));
        $logger->info(print_r($receiverInfo,true));

        if(isset($receiverInfo['email']) && $receiverInfo['email']){
            $template =  $this->_transportBuilder->setTemplateIdentifier($this->temp_id)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->_storeManager->getStore()->getId(),
                        ]
                    )
                    ->setTemplateVars($emailTemplateVariables)
                    ->setFrom($senderInfo)
                    ->addTo($receiverInfo['email']);
        }
        return $this;        
    }

    /*transaction email template*/
    /**
     * [sendQuerypartnerEmail description]
     * @param  Mixed $data                   
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendQuerypartnerEmail($data,$emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        if(isset($data['product-id'])){
            $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_QUERY);
        }
        else{
            $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_QUERY);
        }        
        $this->inlineTranslation->suspend();
    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();
        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendPlacedOrderEmail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendPlacedOrderEmail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_PLACED);
        $this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }
	
	/**
     * [sendPlacedOrderEmailToManager description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendPlacedOrderEmailToManager($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_PLACED_MANAGER);
        
		$this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }
	
	/**
     * [sendOrderCancelEmailToManager description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendOrderCancelEmailToManager($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_CANCELED_MANAGER);
        
		$this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }
	
	/**
     * [sendOrderCancelEmailToVendor description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendOrderCancelEmailToVendor($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_CANCELED_VENDOR);
        
		$this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendInvoicedOrderEmail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendInvoicedOrderEmail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {

        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ORDER_INVOICED);
        $this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);    
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendLowStockNotificationMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendLowStockNotificationMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_LOW_STOCK);
        $this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendSellerPaymentEmail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendSellerPaymentEmail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_TRANSACTION);
        $this->inlineTranslation->suspend();    
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendProductStatusMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendProductStatusMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_APPROVE_PRODUCT);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendProductUnapproveMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
     public function sendProductUnapproveMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_APPROVE_PRODUCT);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendNewSellerRequest description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendNewSellerRequest($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_BECOME_SELLER);
        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendSellerApproveMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendSellerApproveMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_APPROVAL);
        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendSellerDisapproveMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendSellerDisapproveMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_DISAPPROVE);
        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendSellerDenyMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendSellerDenyMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_DENY);
        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendProductDenyMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendProductDenyMail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_DENY);
        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendNewProductMail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function sendNewProductMail($emailTemplateVariables,$senderInfo,$receiverInfo,$editFlag)
    {
        if($editFlag == null)
            $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_PRODUCT);
        else
           $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_EDIT_PRODUCT); 

        $this->inlineTranslation->suspend();        
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }

    /**
     * [sendQueryAdminEmail description]                  
     * @param  Mixed $emailTemplateVariables 
     * @param  Mixed $senderInfo             
     * @param  Mixed $receiverInfo           
     * @return void
     */
    public function askQueryAdminEmail($emailTemplateVariables,$senderInfo,$receiverInfo)
    {
        $this->temp_id = $this->getTemplateId(self::XML_PATH_EMAIL_ADMIN_QUERY);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables,$senderInfo,$receiverInfo);
        $transport = $this->_transportBuilder->getTransport();
        $transport->sendMessage();        
        $this->inlineTranslation->resume();
    }
}