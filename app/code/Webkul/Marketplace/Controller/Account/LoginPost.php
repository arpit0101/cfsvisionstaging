<?php

namespace Webkul\Marketplace\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;


/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost {

    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    public function execute() {

        // if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
        //     * @var \Magento\Framework\Controller\Result\Redirect $resultRedirect 
        //     $resultRedirect = $this->resultRedirectFactory->create();
        //     $resultRedirect->setPath('/');
        //     return $resultRedirect;
        // }

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
					$this->session->setCustomerDataAsLoggedIn($customer);
					if($customer->getId()){
                        $seller   =  $this->_objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()->addFieldToFilter('seller_id',array('eq'=>$customer->getId()));
                    }
					$groupId = $customer->getGroupId();
					if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
					$path = '/';
					if($groupId=='4'){
						$path = 'customer/account/';
					}
					$seller_data = $seller->getData();
					if(count($seller)>0){
						if($seller_data[0]['is_seller']!=0){
							$path = 'customer/account/';
						}
					}
					//$this->getAccountRedirect()->clearRedirectCookie();
					$resultRedirect->setPath($path);
					$this->messageManager->addSuccess(__('Login success.'));	
					return $resultRedirect;
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $message = __(
                            'This account is not confirmed.' .
                            ' <a href="%1">Click here</a> to resend confirmation email.', $value
                    );
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (AuthenticationException $e) {
                    $message = __('Invalid login or password.');
                    $this->messageManager->addError($message);
                    $this->session->setUsername($login['username']);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Invalid login or password next.'));
                }
            } else {
                $this->messageManager->addError(__('A login and a password are required.'));
            }
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
	
	private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
}