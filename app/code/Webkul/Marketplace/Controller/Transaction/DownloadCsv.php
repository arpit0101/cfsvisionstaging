<?php
namespace Webkul\Marketplace\Controller\Transaction;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Sales\Model\Order;

use Magento\Framework\App\RequestInterface;

class DownloadCsv extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;

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
        Order $order,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->Order = $order;
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
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
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {        
        $params = $this->getRequest()->getParams();
        try {
            if (!($customerId = $this->getCustomerId())) {
                return false;
            }

            $ids = array();
            $orderids = array();

            $tr_id = '';
            $filter_data_to = '';
            $filter_data_frm = '';
            $from = null;
            $to = null;
            if(isset($params['tr_id'])){
                $tr_id = $params['tr_id'] != ""?$params['tr_id']:"";
            }
            if (isset($params['from_date'])) {
                $filter_data_frm = $params['from_date'] != ""?$params['from_date']:"";
            }
            if (isset($params['to_date'])) {
                $filter_data_to = $params['to_date'] != ""?$params['to_date']:"";
            }

            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Sellertransaction')
                                ->getCollection()
                                ->addFieldToFilter(
                                    'seller_id',
                                    ['eq' => $customerId]
                                );        

            if($filter_data_to){
                $todate = date_create($filter_data_to);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if($filter_data_frm){
                $fromdate = date_create($filter_data_frm);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if($tr_id){
                $collection->addFieldToFilter(
                    'transaction_id',
                    ['eq' => $tr_id]
                );
            }

            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true,'from' => $from,'to' =>  $to]
            ); 

            $collection->setOrder(
                'created_at',
                'desc'
            );

            $data = array();
            foreach ($collection as $transactioncoll) {
                $data1 =array();
                //$data1['Date'] = $this->formatDate($transactioncoll->getCreatedAt());
                $data1['Date'] = $transactioncoll->getCreatedAt();
                $data1['Transaction Id'] = $transactioncoll->getTransactionId();
                if($transactioncoll->getCustomNote()) {
                    $data1['Comment Message'] = $transactioncoll->getCustomNote(); 
                }else {
                    $data1['Comment Message'] = __('None');
                }
                //$data1['Transaction Amount'] = $this->Order->formatPrice($transactioncoll->getTransactionAmount());
                $data1['Transaction Amount'] = $transactioncoll->getTransactionAmount();
                $data[] = $data1;
            }

            if(isset($data[0])){
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename=transactionlist.csv');
                header('Pragma: no-cache');
                header("Expires: 0");

                $outstream = fopen("php://output", "w");    
                fputcsv($outstream, array_keys($data[0]));

                foreach($data as $result)
                {
                    fputcsv($outstream, $result);
                }

                fclose($outstream);
            }else{
                return $this->resultRedirectFactory->create()->setPath('marketplace/transaction/history', ['_secure'=>$this->getRequest()->isSecure()]);
            }
            // $this->getResponse()->representJson(
            //     $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(count($model))
            // );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->resultRedirectFactory->create()->setPath('marketplace/transaction/history', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}
