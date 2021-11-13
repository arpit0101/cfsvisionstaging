<?php
/**
 * Marketplace Seller Feedback controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Follow extends Action
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
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->context = $context;
        parent::__construct($context);
    }    

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $flag = 0;
        $shop_url = $this->getRequest()->getParam('shop');         

        $om = \Magento\Framework\App\ObjectManager::getInstance();

        $customerSession = $om->get('Magento\Customer\Model\Session');
        $LoggedInCustomerId = $customerSession->getCustomerId();
        
        $result = $this->resultJsonFactory->create();

        if(!$customerSession->isLoggedIn()){
            return $result->setData(['success' => false, "message"=> "Not Logged In, Please login to follow."]);
        }
        
        $seller = null;

        if($shop_url){

            $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('is_seller',1)
                    ->addFieldToFilter('shop_url',array('eq'=>$shop_url));
                    
                    

            if(count($data)){

                $flag = 1;
                foreach ($data as $key => $value) {
                    $seller = $value;    
                }

                if($seller->getId() == $LoggedInCustomerId){
                    return $result->setData(['success' => false, "message"=> "Sorry, You can't follow yourself."]);
                }

                $connection = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
                      $conn = $connection->getConnection();

                $select = $conn->select()
                          ->from(
                              ['o' => 'marketplace_followers']
                          )
                          ->where('o.seller_id=?', $seller->getId())->where('o.user_id=?', $LoggedInCustomerId);
                $data = $conn->fetchAll($select);
                //print_r($data); 
                if(count($data)){
                    $follow = $om->create('Webkul\Marketplace\Model\Follow');   
                    foreach ($data as $key => $value) {
                         $conn->delete("marketplace_followers","entity_id=".$value["entity_id"]);
                    }
                }else{
                
                $dateTime = $om->get('Magento\Framework\Stdlib\DateTime\DateTime'); 

                $conn->insert("marketplace_followers",array("seller_id"=>$seller->getId(), "user_id"=>$LoggedInCustomerId, "created_at"=>$dateTime->date(), "updated_at"=>$dateTime->date()));
                }

            }
        }

        if($flag == 1){
            return $result->setData(['success' => true]);
        }else{
            return $result->setData(['success' => false, "message"=> "Error! Not a valid store."]);
        }

    }
}

