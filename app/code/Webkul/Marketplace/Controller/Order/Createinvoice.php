<?php
namespace Webkul\Marketplace\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\Controller\ResultFactory; 
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

class Createinvoice extends Action
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
     * @var InvoiceService
     */
    private $invoiceService;
    protected $resultFactory;
	
	/**
     * @var Registry
     */
    protected $registry;
	
	/**
     * @var InvoiceSender
     */
    protected $invoiceSender;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
	 * @param InvoiceService $invoiceService
	 * @param InvoiceSender $invoiceSender
	 * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession,
        InvoiceService $invoiceService,
		InvoiceSender $invoiceSender,
        // ResultFactory $resultFactory,
        Registry $registry
    ) {
        $this->_customerSession 	= $customerSession;
        $this->resultPageFactory 	= $resultPageFactory;
		$this->invoiceService 		= $invoiceService;
		// $this->resultFactory 		= $resultFactory;
		$this->invoiceSender 		= $invoiceSender;
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
			$title 			= 	'ELOCAL - Invoice';
			$notification 	= 	array(
										'title' 			=>	$title,
										'text' 				=> 	"Your Invoice has been Created for your Order",
										'message' 			=> 	"Your Invoice has been Created for your Order",
										'body' 				=> 	"Your Invoice has been Created for your Order",
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
			curl_setopt($ch,CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false );
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
										'message'			=> "Your Invoice has been Created for your Order",
										'title'				=> "ELOCAL - Invoice", 
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
		$order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
		//echo '<pre>'; print_r($order->getData()); die("testt");
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$customerid = $order->getCustomerId();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($customerid);
		//$customer_email = $order->getCustomerEmail();
        $device_token = $customer->getDeviceToken();
        $device_type  = $customer->getDeviceType();
		$notification_type="orderprocess";
		
        $this->send_push_notifications($device_token,$device_type,$notification_type,$customerid,$orderId);
		$orderItems = $order->getAllItems();
		
		
		if(!empty($orderItems)){
			foreach($orderItems as $orderItem){
				$data['invoice']['items'][$orderItem->getItemId()]	=	$orderItem->getQtyOrdered();
			}
		}
        $data['invoice']['comment_text']	=	'';
        $data['invoice']['comment_customer_notify']	=	1;
        $data['invoice']['send_email']	=	1;
		
        try {
            $invoiceData = $data['invoice'];
        
            $invoiceItems = isset($invoiceData['items']) ? $invoiceData['items'] : [];
            /** @var \Magento\Sales\Model\Order $order */
			
            if (!$order->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The order no longer exists.'));
            }

            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }
			
            $invoice = $this->invoiceService->prepareInvoice($order, $invoiceItems);
			
            if (!$invoice) {
                throw new LocalizedException(__('We can\'t save the invoice right now.'));
            } 
			
			
            if (!$invoice->getTotalQty()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('You can\'t create an invoice without products.')
                );
            }
			
            $this->registry->register('current_invoice', $invoice);
            if (!empty($data['capture_case'])) {
                $invoice->setRequestedCaptureCase($data['capture_case']);
            }
			
			
            if (!empty($data['invoice']['comment_text'])) {
                $invoice->addComment(
                    $data['invoice']['comment_text'],
                    isset($data['invoice']['comment_customer_notify']),
                    isset($data['invoice']['is_visible_on_front'])
                );
				
                $invoice->setCustomerNote($data['invoice']['comment_text']);
                $invoice->setCustomerNoteNotify(isset($data['invoice']['comment_customer_notify']));
            }
			
			
			
            $invoice->register();

            $invoice->getOrder()->setCustomerNoteNotify(!empty($data['invoice']['send_email']));
            $invoice->getOrder()->setIsInProcess(true);
			
			// get data of location for order to save againg
			$location_data 	=	$this->getLocationDataByOrderId($orderId);
			
            $transactionSave = $this->_objectManager->create(
                \Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
				
            $shipment = false;
			
            if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                $shipment = $this->_prepareShipment($invoice);
                if ($shipment) {
                    $transactionSave->addObject($shipment);
                }
            }
            $transactionSave->save();
			
			// get data of location for order to save againg
			$this->saveLocationDataByOrderId($orderId, $location_data);
			
            if (isset($shippingResponse) && $shippingResponse->hasErrors()) {
                $this->messageManager->addError(
                    __(
                        'The invoice and the shipment  have been created. ' .
                        'The shipping label cannot be created now.'
                    )
                );
            } elseif (!empty($data['invoice']['do_shipment'])) {
                $this->messageManager->addSuccess(__('You created the invoice and shipment.'));
            } else {
                $this->messageManager->addSuccess(__('The invoice has been created.'));
            }
			
            // send invoice/shipment emails
            try {
                if (!empty($data['invoice']['send_email'])) {
                    $this->invoiceSender->send($invoice);
                }
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->messageManager->addError(__('We can\'t send the invoice email right now.'));
            }
            if ($shipment) {
                try {
                    if (!empty($data['invoice']['send_email'])) {
                        $this->shipmentSender->send($shipment);
                    }
                } catch (\Exception $e) {
                    $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                    $this->messageManager->addError(__('We can\'t send the shipment right now.'));
                }
            }
            $this->_objectManager->get(\Magento\Backend\Model\Session::class)->getCommentText(true);
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        } catch (LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t save the invoice right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
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
}
