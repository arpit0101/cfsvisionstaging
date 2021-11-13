<?php
/**
 * Marketplace askquestion controller.
 *
 */
namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Customer\Model\Session;

class Askquestion extends \Magento\Customer\Controller\AbstractAccount
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
        PageFactory $resultPageFactory
    ) {
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

        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        $seller_name = $this->_customerSession->getCustomer()->getName();
        $seller_email = $this->_customerSession->getCustomer()->getEmail();

        $admin_storemail = $helper->getAdminEmailId();
        $adminEmail=$admin_storemail? $admin_storemail:$helper->getDefaultTransEmailId();
        $adminUsername = 'eLOCAL';

        $emailTemplateVariables = array();
        $senderInfo = array();
        $receiverInfo = array();
        $emailTemplateVariables['myvar1'] =$adminUsername;
        $emailTemplateVariables['myvar2'] =$seller_name;
        $emailTemplateVariables['subject']=$data['subject'];
        $emailTemplateVariables['myvar3'] =$data['ask'];
        $senderInfo = [
            'name' => $seller_name,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $adminUsername,
            'email' => "noreply@elocal.ae",
        ];
        $this->_objectManager->create('Webkul\Marketplace\Helper\Email')->askQueryAdminEmail($emailTemplateVariables,$senderInfo,$receiverInfo);
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode('true')
        );
    }
}