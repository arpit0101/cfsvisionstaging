<?php
namespace Webkul\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\RequestInterface;

class Startchat extends Action
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
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory, 
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime
    ) {

        $this->_date = $date;
        $this->dateTime = $dateTime;
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
    public function execute()
    {        
        /** @var \Magento\Framework\View\Result\Page $resultPage */

        $seller_id = $this->getRequest()->getParam('seller');

        $customer_id  =   $this->_customerSession->getCustomerId();

        $collection     =   $this->_objectManager->create('Webkul\Marketplace\Model\Room')->getCollection();

                $name='';
                $collection->getSelect()
                            ->where('seller_id ='.$seller_id.' AND customer_id ='.$customer_id)
                           
                            ->order('created_at');
                if(count($collection)){
                    //$this->messageManager->addSuccess(__('Chat started with '));
                    foreach ($collection as $key => $value) {
                        break;
                    }
                    
                    return $this->resultRedirectFactory->create()->setPath('marketplace/account/chatroom/room/'.$value->getId(), 
                        ['_secure'=>$this->getRequest()->isSecure()]);
                }else{
                    $collection     =   $this->_objectManager->create('Webkul\Marketplace\Model\Room');

                    $wholedata = ["seller_id"=>$seller_id, "customer_id"=>$customer_id];
                    $wholedata['created_at'] = $this->_date->gmtDate();
                    $wholedata['updated_at'] = $this->_date->gmtDate();
                    $collection->setData($wholedata);

                    $collection->save();

                    return $this->resultRedirectFactory->create()->setPath('marketplace/account/chatroom/room/'.$collection->getId(), 
                        ['_secure'=>$this->getRequest()->isSecure()]);

                }
           

    }
}
