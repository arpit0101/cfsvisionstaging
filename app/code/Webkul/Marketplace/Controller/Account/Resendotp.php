<?php
namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\RequestInterface;

class Resendotp extends Action
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
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        return parent::dispatch($request);
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $response = $this->_objectManager->create('Magento\Framework\App\Response\Http');
        $session = $this->_objectManager->create('Magento\Customer\Model\Session');

    //$this->session->regenerateId(); die;        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($session->isLoggedIn()) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
     
        try {
            //$current_customer_id = $session->setData("current_customer_id", 100);
            $current_customer_id = $session->getData("current_customer_id");

            $verifymobiles = $this->_objectManager->create('Webkul\Marketplace\Model\Verifymobile')->getCollection();
                            
                            //Echo "<pre/>";
            foreach ($verifymobiles as $key => $verifymobile) {
                //echo "asas";
                if($verifymobile->getCustomerId() == $current_customer_id){
                    $mobilenumber =    $verifymobile->getMobileNo();
                }
                    //break;
                }
            //var_dump(get_class_methods(get_class($verifymobiles)));
              //  print_r($mobilenumber); die;
           // $mobilenumber = $verifymobile->getData("mobile_no");
           
            $otp =  $this->sendOTP($mobilenumber);
            $response = $this->_objectManager->create('Magento\Framework\App\Response\Http');
           
            $this->messageManager->addSuccess(__("Resent! You will get OTP to verify your mobile number. Please enter here."));
                
        }  catch (\Exception $e) {
            die($e->getMessage()); 
            $this->messageManager->addException($e, __("Can't resend! Please try again."));
        }

       $response->setRedirect( $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore(0)
            ->getBaseUrl()."marketplace/account/verifymobile"); 
            return $response; 
    }

    public function sendOTP($mobilenumber){

        $otp = rand(100000, 999999);
        $otp = 123456;
        $body   =   'One Time Password for account mobile verification at Daraways is '.$otp;
        $to     =   '+919214512125';
        $sid    =   'ACb16fcaba9c51893d61bbdae05516c7ef';
        $token  =   '2f27ad3ffd0378f4bdb59a845c8a6297';
        $from   =   '(201) 383-6661';
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
