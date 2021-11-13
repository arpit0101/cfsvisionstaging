<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\Marketplace\Controller\Account;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    
  

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
		//echo 123;die;	
        $response = $this->_objectManager->create('Magento\Framework\App\Response\Http');

    //$this->session->regenerateId(); die;        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }

        if (!$this->getRequest()->isPost()) {
            $url = $this->urlModel->getUrl('customer/account/create', ['_secure' => true]);
            $resultRedirect->setUrl($this->_redirect->error($url));
            return $resultRedirect;
        }
        
        $this->session->regenerateId(); 

            
        try {
           // echo "<pre>";print_r($this->getRequest()->getParams());die;
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];

            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);

            $customer->setAddresses($addresses);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->session->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);
            
            $customer->setData("is_active", 0);

            

            
            $customer = $this->accountManagement->createAccount($customer, $password, $redirectUrl);
            
            
            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());

            $otp =  $this->sendOTP($this->getRequest()->getParam('mobilenumber', ""),$this->getRequest()->getParam('country-code', ""));
            $response = $this->_objectManager->create('Magento\Framework\App\Response\Http');

            if($otp){
                $value = $this->_objectManager->create('Webkul\Marketplace\Model\Verifymobile');
                $date = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
                $value->setData('customer_id', $customer->getId());
                $value->setData('mobile_no', $this->getRequest()->getParam('mobilenumber', ""));
                $value->setData('otp', $otp);  
                $value->setData('status', 0);  
                $value->setCreatedAt($date->gmtDate());  
                $value->setUpdatedAt($date->gmtDate());
                $value->save();

                $this->session->setData("current_customer_id", $customer->getId());
                
            }

            /*echo AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED;
            echo $confirmationStatus;*/
        
        


            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                //echo "hello";exit;
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(
                    __(
                        'You must confirm your account. First enter the OTP to verify Mobile Number and Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $email
                    )
                );
                // @codingStandardsIgnoreEnd
                $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                $resultRedirect->setUrl("marketplace/account/verifymobile");
            } else {
                //$this->session->setCustomerDataAsLoggedIn($customer);
                $this->messageManager->addSuccess($this->getSuccessMessage());
                $this->messageManager->addSuccess(__("You will get OTP to verify your mobile number. Please enter here."));
                //$resultRedirect = $this->accountRedirect->getRedirect();
            }
            $response->setRedirect( $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore(0)
            ->getBaseUrl()."marketplace/account/verifymobile"); 
            return $response; 
            //$resultRedirect->setUrl("marketplace/account/verifymobile");
            //print_r($resultRedirect); die;
            //return $resultRedirect; exit;
        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );

            // @codingStandardsIgnoreEnd
            $this->messageManager->addError($message);
            //die($e->getMessage()); 
        } catch (InputException $e) {
            //die($e->getMessage()); 
            $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
            }
        } catch (\Exception $e) {
            //die($e->getMessage()); 
            $this->messageManager->addException($e, __('We can\'t save the customer.'));
        }

        $this->session->setCustomerFormData($this->getRequest()->getPostValue());
        $defaultUrl = $this->urlModel->getUrl('customer/account/create', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
        return $resultRedirect;
    }

    public function sendOTP($mobilenumber,$countrycode){
        //echo "+".$countrycode.$mobilenumber;exit;
        $otp = rand(100000, 999999);
        //$otp = 523458;

        $body   =   'One Time Password for account mobile verification at Daraways is '.$otp;
        //$to     =   '+919214512125';
        //$sid    =   'ACb16fcaba9c51893d61bbdae05516c7ef';
        //$token  =   '2f27ad3ffd0378f4bdb59a845c8a6297';
        $to     =   "+".$countrycode.$mobilenumber;
        $sid    =   'AC5ef8a05c95ccfc7778f794aa579d4ba8';
        $token  =   '4fc0f77e8c5565f7b840e0e041d865d7';
        $from   =   '(520) 217-8854'; //'(201) 383-6661'; 
        $uri = 'https://api.twilio.com/2010-04-01/Accounts/' . $sid . '/SMS/Messages.json';
        $auth = $sid . ':' . $token;
     
        // post string (phone number format= +15554443333 ), case matters
        $fields = 
            '&To=' .  urlencode( $to ) . 
            '&From=' . urlencode( $from ) . 
            '&Body=' . urlencode( $body );
     
        // start cURL
        $res = curl_init();
         
        // set cURL options
        curl_setopt( $res, CURLOPT_URL, $uri );
        curl_setopt( $res, CURLOPT_POST, 3 ); // number of fields
        curl_setopt( $res, CURLOPT_POSTFIELDS, $fields );
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt( $res, CURLOPT_USERPWD, $auth ); // authenticate
         //Array ( [code] => 21608 [message] => The number +9192145121255 is unverified. Trial accounts cannot send messages to unverified numbers; verify +9192145121255 at twilio.com/user/account/phone-numbers/verified, or purchase a Twilio number to send messages to unverified numbers. [more_info] => https://www.twilio.com/docs/errors/21608 [status] => 400 ) 
        // send cURL
        $result = curl_exec( $res );
        $re = json_decode($result, true);
        //print_r( $re);
        if(isset($re["status"])){
            return $otp;
        }else{
            return false;
        }

    }

   
}
