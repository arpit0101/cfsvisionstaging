<?php
namespace Webkul\Marketplace\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;

class Createshipment extends Action
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
     * @var \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader
     */
    protected $shipmentLoader;
	
	/**
     * @var \Magento\Sales\Model\Order\Email\Sender\ShipmentSender
     */
    protected $shipmentSender;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     */
    private $shipmentValidator;

    protected $resultFactory;
	
	/**
     * @var Registry
     */
    protected $registry;
	
	/**
     * @var \Magento\Shipping\Model\Shipping\LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
	 * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Shipping\Model\Shipping\LabelGenerator $labelGenerator,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        Registry $registry
    ) {
        $this->_customerSession 	= $customerSession;
        $this->resultPageFactory 	= $resultPageFactory;
		$this->shipmentLoader = $shipmentLoader;
        $this->labelGenerator = $labelGenerator;
        $this->shipmentSender = $shipmentSender;
		$this->registry = $registry;
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
        $loginUrl = $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
	public function send_push_notifications($deviceToken="",$deviceType="",$notification_type="",$merchant_id="",$order_id="")
    {
		$server_key			=	'AAAA4L5f9TI:APA91bGMqnokL19jIlMbzTFLTqNqC3rBeciPEMuN6ko9F0d8Wt2eQzW6l3d570Bw4zZCKujDU63tF4rDm4oj-vOM-nxv2u9BK1WXQP_qtfaVYuEeY5BJwIX_V9_PFo7yfy06QUmbKYpt';
		//echo $server_key;die;
		
		if(strtolower($deviceType = 'ios'))
		{
			$ch 			= 	curl_init("https://fcm.googleapis.com/fcm/send");
			$title 			= 	'ELOCAL - Shipment';
			$notification 	= 	array(
										'title' 			=>	$title,
										'text' 				=> 	"Your Shipment has been Created for your Order",
										'message' 			=> 	"Your Shipment has been Created for your Order",
										'body' 				=> 	"Your Shipment has been Created for your Order",
										'vibrate'			=> 	1,
										'sound'				=> 	1, 
										'merchant_id'		=> 	$merchant_id, 
										'order_id'			=> 	$order_id, 
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
										'message'			=> "Your Shipment has been Created for your Order",
										'title'				=> "ELOCAL - Shipment", 
										'vibrate'			=> 1,
										'sound'				=> 1, 
										'merchant_id'		=> $merchant_id, 
										'order_id'			=> $order_id, 
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
    public function execute()
    {
		$orderId 	= $this->getRequest()->getParam('order_id');
		$order 		= $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		//echo '<pre>'; print_r($order->getData()); die("testt");
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerid = $order->getCustomerId();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerid);
		//$customer_email = $order->getCustomerEmail();
        $device_token = $customer->getDeviceToken();
        $device_type  = $customer->getDeviceType();
		$notification_type="ordershipped";
        $this->send_push_notifications($device_token,$device_type,$notification_type,$customerid,$orderId);
		
		$orderItems = $order->getAllItems();
		
		if(!empty($orderItems)){
			foreach($orderItems as $orderItem){
				$data['shipment']['items'][$orderItem->getItemId()]	=	$orderItem->getQtyOrdered();
			}
		}

		$data['create_shipping_label']	=	false;
		$data['comment_text']			=	'';
		$data['send_email']	=	1;
		$data['comment_customer_notify']	=	1;
		
        if (!empty($data['comment_text'])) {
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setCommentText($data['comment_text']);
        }
			
        $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

        $responseAjax = new \Magento\Framework\DataObject();

        try {
            $this->shipmentLoader->setOrderId($orderId);
            $this->shipmentLoader->setShipmentId($this->getRequest()->getParam('shipment_id'));
            $this->shipmentLoader->setShipment($data);
            $this->shipmentLoader->setTracking($this->getRequest()->getParam('tracking'));
            $shipment = $this->shipmentLoader->load();
            if (!$shipment) {
                $this->_forward('noroute');
                return;
            }

            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );

                $shipment->setCustomerNote($data['comment_text']);
                $shipment->setCustomerNoteNotify(isset($data['comment_customer_notify']));
            }
            $validationResult = $this->getShipmentValidator()
                ->validate($shipment, [QuantityValidator::class]);

            if ($validationResult->hasMessages()) {
                $this->messageManager->addError(
                    __("Shipment Document Validation Error(s):\n" . implode("\n", $validationResult->getMessages()))
                );
                return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
                return;
            }
            $shipment->register();
			// get data of location for order to save againg
			$location_data 	=	$this->getLocationDataByOrderId($orderId);
			
            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

            if ($isNeedCreateLabel) {
                $this->labelGenerator->create($shipment, $this->_request);
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            if (!empty($data['send_email'])) {
                $this->shipmentSender->send($shipment);
            }

            $shipmentCreatedMessage = __('The shipment has been created.');
            $labelCreatedMessage = __('You created the shipping label.');
			
			// get data of location for order to save againg
			$this->saveLocationDataByOrderId($orderId, $location_data);
			
            $this->messageManager->addSuccess(
                $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage
            );
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
			return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        } catch (\Exception $e) {
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $this->messageManager->addError(__('Cannot save shipment.'));
			return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
    }
	
	function getLocationDataByOrderId($orderID){
		
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		
		$sql 			= 	"SELECT region_id, area_id, country_id FROM " . $tableName ." WHERE sales_order_address.parent_id=".$orderID."";
		
		$result 		= 	$connection->fetchAll($sql);
		$orders 		=	array(); 		
		
		$region_id		=	'';
		$area_id		=	'';
		$country_id		=	'';
		
		if(!empty($result)){
			foreach($result as $row){
					
				$region_id	=	$row['region_id'];
				$area_id	=	$row['area_id'];
				$country_id	=	$row['country_id'];
				break;
			}
		}
		return array('region_id'=>$region_id, 'area_id'=>$area_id, 'country_id'=>$country_id);
	}
	
	function saveLocationDataByOrderId($orderID, $locationdata){
		
		$resource 		= 	$this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection 	= 	$resource->getConnection();
		$tableName 		= 	$resource->getTableName('sales_order_address');
		
		$sql 			= 	"UPDATE " . $tableName ." SET region_id='".$locationdata['region_id']."', area_id ='".$locationdata['area_id']."' WHERE sales_order_address.parent_id=".$orderID."";
		
		return $connection->query($sql);
	}
	
	/**
     * @return \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface
     * @deprecated 100.1.1
     */
    private function getShipmentValidator()
    {
        if ($this->shipmentValidator === null) {
            $this->shipmentValidator = $this->_objectManager->get(
                \Magento\Sales\Model\Order\Shipment\ShipmentValidatorInterface::class
            );
        }

        return $this->shipmentValidator;
    }
	
	/**
     * Save shipment and order in one transaction
     *
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @return $this
     */
    protected function _saveShipment($shipment)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transaction = $this->_objectManager->create(
            \Magento\Framework\DB\Transaction::class
        );
        $transaction->addObject(
            $shipment
        )->addObject(
            $shipment->getOrder()
        )->save();

        return $this;
    }
}
