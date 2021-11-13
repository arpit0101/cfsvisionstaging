<?php
/**
 * Marketplace Sendmail controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;

class Sendmail extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;
    
    protected $product;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Customer $customer,        
        Product $product,
        PageFactory $resultPageFactory
    ) {
        $this->Customer = $customer;
        $this->Product = $product;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if($data['seller-id']){
            $this->_eventManager->dispatch(
                'mp_send_querymail',
                [$data]
            );            
            if($this->_customerSession->isLoggedIn()){
                $buyer_name = $this->_customerSession->getCustomer()->getName();
                $buyer_email = $this->_customerSession->getCustomer()->getEmail();
            }else{              
                $buyer_email = $data['email'];
                $buyer_name = $data['name'];
                if(strlen($buyer_name)<2){
                    $buyer_name="Guest";
                }
            }
            $emailTemplateVariables = array();
            $senderInfo = array();
            $receiverInfo = array();
            $seller=$this->Customer->load($data['seller-id']);
            $emailTemplateVariables['myvar1'] =$seller->getName();
            $sellerEmail = $seller->getEmail();
            if(!isset($data['product-id'])){
                $data['product-id'] = 0 ;
            }else{
                $emailTemplateVariables['myvar3'] =$this->Product->load($data['product-id'])->getName();
            }            
            $emailTemplateVariables['myvar4'] =$data['ask'];
            $emailTemplateVariables['myvar6'] =$data['subject'];
            $emailTemplateVariables['myvar5'] =$buyer_email;
            $senderInfo = [
                'name' => $buyer_name,
                'email' => $buyer_email,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $sellerEmail,
            ];
            $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->sendQuerypartnerEmail($data,$emailTemplateVariables,$senderInfo,$receiverInfo);
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode('true')
        );
    }
}