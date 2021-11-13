<?php
/**
 * Marketplace Seller Feedback controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Feedback extends Action
{
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
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }    

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $flag = 0;
        $shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getFeedbackUrl();
        if(!$shop_url){
            $shop_url = $this->getRequest()->getParam('shop');            
        }
        if($shop_url){
            $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('is_seller',1)
                    ->addFieldToFilter('shop_url',array('eq'=>$shop_url));
            if(count($data)){
                $flag = 1;
            }
        }
        if($flag == 1){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Seller Feedback'));
            return $resultPage; 
        }else{
            return $this->resultRedirectFactory->create()->setPath('marketplace', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}