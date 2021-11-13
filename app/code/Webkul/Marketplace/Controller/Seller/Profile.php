<?php
/**
 * Marketplace Seller List controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Profile extends Action
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
        
        $shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getProfileUrl();
        if(!$shop_url){
            $shop_url = $this->getRequest()->getParam('shop');            
        }
        if($shop_url == "me" && $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->isSeller()){
            $seller_data  = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getSellerData();
            foreach ($seller_data as $key => $value) {
                $seller_data = $value; break;
            }
            //var_dump($seller_data); die;
            $shop_url = $seller_data["shop_url"];            
            if(!empty($seller_data["shop_url"]))
            return $this->resultRedirectFactory->create()->setPath('marketplace/seller/profile/shop/'.$seller_data["shop_url"], ['_secure'=>$this->getRequest()->isSecure()]);
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
            $resultPage->getConfig()->getTitle()->set(__('Marketplace Seller Profile'));
            return $resultPage;
        }else{
            return $this->resultRedirectFactory->create()->setPath('marketplace', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
}