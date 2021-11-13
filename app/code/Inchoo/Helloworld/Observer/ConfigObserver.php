<?php namespace Inchoo\Helloworld\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface as Logger;

class ConfigObserver implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger,
		\Magento\Framework\App\RequestInterface $request
    ) {
        $this->logger = $logger;
		$this->_request = $request;
    }

    public function execute(EventObserver $observer)
    {
		$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
		$params = $this->_request->getParams();
		$notification_type = 'genralmessage';
        $customers_array = array_unique($params['groups']['general_settings']['fields']['customerlist']['value']);
        $message = $params['groups']['general_settings']['fields']['customer_notifi']['value'];
		$CustomerModel = $objectManager->create('\Magento\Customer\Model\Customer');
		foreach($customers_array as $key=>$customeremail){
			$CustomerModel->setWebsiteId(1);
            $CustomerModel->loadByEmail($customeremail);
            $userId = $CustomerModel->getId();
            $device_token = $CustomerModel->getDeviceToken();
            $device_type  = $CustomerModel->getDeviceType();
			if(isset($device_token)){
			//echo $device_token; exit;
				$this->send_push_notifications($device_token,$device_type,$notification_type,$userId,$message);
			}
		}
    }
	
	public function send_push_notifications($deviceToken="",$deviceType="",$notification_type="",$merchant_id="",$message="")
    {
		$server_key			=	'AAAA4L5f9TI:APA91bGMqnokL19jIlMbzTFLTqNqC3rBeciPEMuN6ko9F0d8Wt2eQzW6l3d570Bw4zZCKujDU63tF4rDm4oj-vOM-nxv2u9BK1WXQP_qtfaVYuEeY5BJwIX_V9_PFo7yfy06QUmbKYpt';
		//echo $server_key;die;
		
		if(strtolower($deviceType = 'ios'))
		{
			$ch 			= 	curl_init("https://fcm.googleapis.com/fcm/send");
			$title 			= 	'ELOCAL - Notification';
			$notification 	= 	array(
										'title' 			=>	$title,
										'text' 				=> 	$message,
										'body' 				=> 	$message,
										'message' 			=> 	$message,
										'vibrate'			=> 	1,
										'sound'				=> 	1, 
										'merchant_id'		=> 	$merchant_id, 
										//'order_id'			=> 	$order_id, 
										//'response_data'		=> 	$data, 
										'notification_type'	=> 	$notification_type
									);

			//This array contains, the token and the notification. The 'to' attribute stores the token.
			$arrayToSend 	= 	array('to' => $deviceToken, 'notification' => $notification,'priority'=>'high');

			//Generating JSON encoded string form the above array.
			$json 		= 	json_encode($arrayToSend);
			
			//Setup headers:
			$headers 	= 	array();
			$headers[] 	= 	'Content-Type: application/json';
			$headers[] 	= 	"Authorization: key= $server_key"; // key here

			//Setup curl, add headers and post parameters.
			curl_setopt($ch,CURLOPT_CUSTOMREQUEST,"POST");                                                                     
			curl_setopt($ch,CURLOPT_POSTFIELDS,$json);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);       

			//Send the request
			$response = curl_exec($ch);
			//prd($response);	
			//Close request
			curl_close($ch);
			return true;
			
		}
		else
		{
			$registrationIds 	= 	array($deviceToken);
			$msg 				= 	array (
										'message'			=> $message,
										'title'				=> "ELOCAL - Notification", 
										'vibrate'			=> 1,
										'sound'				=> 1, 
										'merchant_id'		=> $merchant_id, 
										//'order_id'			=> $order_id, 
										//'response_data'		=> $data, 
										'notification_type'	=> $notification_type, 
									);						
			$fields 			= 	array (
										'registration_ids' 	=> $registrationIds,
										'data'				=> $msg
									);
									
			$headers 			= 	array (
										'Authorization: key=' . $server_key,
										'Content-Type: application/json'
									);
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
			curl_setopt($ch,CURLOPT_POST, true );
			curl_setopt($ch,CURLOPT_HTTPHEADER, $headers );
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode( $fields) );
			$result = curl_exec($ch);
			/* echo "<pre>";
			print_r($result);die; */
			curl_close( $ch );
			return true;
		}
	}
}