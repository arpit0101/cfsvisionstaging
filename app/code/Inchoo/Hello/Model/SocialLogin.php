<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\SocialLoginInterface;
use Magento\Backend\App\Action;
use Magento\Webapi\Model\Authorization\TokenUserContext;
use Mageplaza\SocialLogin\Model\Social;
use Mageplaza\SocialLogin\Helper\Social as SocialHelper;
use Mageplaza\SocialLogin\Controller\Social\Login;

class SocialLogin implements SocialLoginInterface
{
	protected $_context;
	protected $_quoteFactory;
	
	/**
     * @type \Mageplaza\SocialLogin\Model\Social
     */
    protected $apiObject;
    protected $apiHelper;
    protected $socialLogin;
    protected $_tokenModelFactory;
    protected $request;
	protected $_customerRepositoryInterface;
	/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		TokenUserContext $context,
		Social $apiObject,
		SocialHelper $apiHelper,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		\Mageplaza\SocialLogin\Controller\Social\Login $socialLogin,
		\Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
		\Magento\Framework\App\Request\Http $request,
		array $data = []
	) {
		$this->_context						=	$context;
		$this->apiObject					=	$apiObject;
		$this->apiHelper        			= 	$apiHelper;
		$this->socialLogin        			= 	$socialLogin;
		$this->_tokenModelFactory        	= 	$tokenModelFactory;
		$this->request						=	$request;
		$this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_customerFactory = $customerFactory;
		
		
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	 
    public function login() {
		
		$type			=	$this->request->getParam('type', null);
		$type 			= 	$this->apiHelper->setType($type);
		$social_id		=	$this->request->getParam('social_id', null);
		$customerToken 	= 	$this->_tokenModelFactory->create();
		$customer 		=	$this->apiObject->getCustomerBySocial($social_id, $type);
	    if($customer->getId()){
			
			return $customerToken->createCustomerToken($customer->getCustomerId())->getToken();;
		}
		return false;
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	 
    public function register() {
		
		$user_data 		=	json_decode($this->request->getContent());
		if(empty($user_data)){
			$user_data	=	json_decode(json_encode($_POST));
		}
		
		$customerToken 	= 	$this->_tokenModelFactory->create();
		$type			=	$user_data->customer->type;
		$type 			= 	$this->apiHelper->setType($type);
		$social_id		=	$user_data->customer->social_id;
		$customer 		=	$this->apiObject->getCustomerBySocial($social_id, $type);
		// echo json_encode([$customer]);
		if($customer->getId()){
			file_put_contents(__DIR__."/customer_Id.txt", $customer->getId());
			
			$user_token_data	=	$customerToken->createCustomerToken($customer->getCustomerId())->getToken();
			// file_put_contents(__DIR__."/login_user_token_data.txt", $user_token_data);
			if($customer->getCustomerId()){
				if(isset($user_data->customer->device_token)){
					$device_token =	$user_data->customer->device_token;
					$device_type  =	$user_data->customer->device_type;
					$customer_id = $customer->getCustomerId();
					$loaded_customer = $this->_customerFactory->create()->load($customer_id)->getDataModel();
					$loaded_customer->setCustomAttribute('device_token', $device_token);
					$loaded_customer->setCustomAttribute('device_type', $device_type);
					$this->_customerRepositoryInterface->save($loaded_customer);
				}
			}
			/* file_put_contents(__DIR__."/user_data_device.txt", print_r($user_data->customer->device_token, true));
			file_put_contents(__DIR__."/user_data_customer_id.txt", print_r($customer->getCustomerId(), true)); */
			
			return $user_token_data;
		}
		
		$name = explode(' ',$user_data->customer->displayName ?: __('New User'));
		 file_put_contents(__DIR__."/user_data.txt", print_r($name, true));
		if($user_data->customer->email)
		{
			$email  = $user_data->customer->email;
		}
		else
		{
			return json_encode(['error'=>true, 'msg'=>'Email Address not available.']);
		}
		$user = [
			'email'      => $user_data->customer->email ?: $social_id . '@' . strtolower($type) . '.com',
			'firstname'  => $user_data->customer->firstname ?: (array_shift($name) ?: $social_id),
			'lastname'   => $user_data->customer->lastname ?: (array_shift($name) ?: $social_id),
			'identifier' => $social_id,
			'type'       => $type
		];

		$customer = $this->socialLogin->createCustomer($user, $type); 
		/* file_put_contents(__DIR__."/user_data_device.txt", print_r($user_data->customer->device_token, true));
		file_put_contents(__DIR__."/user_data_customer_id.txt", print_r($customer->getId(), true)); */
		
		
		file_put_contents(__DIR__."/customer_Id.txt", $customer->getId());
		if($customer->getId()){
			$user_token_data	=	$customerToken->createCustomerToken($customer->getId())->getToken();
			// file_put_contents(__DIR__."/register_user_token_data.txt", $user_token_data);
			
			//$user_data = $this->request->getParams();
			if(isset($user_data->customer->device_token)){
			    $device_token =	$user_data->customer->device_token;
				$device_type  =	$user_data->customer->device_type;
			    $customer_id = $customer->getId();
			    $loaded_customer = $this->_customerFactory->create()->load($customer_id)->getDataModel();
                $loaded_customer->setCustomAttribute('device_token', $device_token);
                $loaded_customer->setCustomAttribute('device_type', $device_type);
                $this->_customerRepositoryInterface->save($loaded_customer);
			}
			
			return $user_token_data;
		}
		return false;
    }
}