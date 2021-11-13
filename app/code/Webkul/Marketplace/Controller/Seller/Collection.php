<?php
/**
 * Marketplace Seller Collection controller.
 *
 */
namespace Webkul\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends Action
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
        $shop_url = $this->_objectManager->create('Webkul\Marketplace\Helper\Data')->getCollectionUrl();
        if(!$shop_url){
            $shop_url = $this->getRequest()->getParam('shop');            
        }
		
		$offsellers	=	$this->getOffSellerCollection();
        if($shop_url){
            $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('is_seller',1)
					->addFieldToFilter(
						'seller_id',
						['nin' => $offsellers]
					)
                    ->addFieldToFilter('shop_url',array('eq'=>$shop_url));
			//echo $data->getSelect(); exit; SELECT `main_table`.* FROM `marketplace_userdata` AS `main_table` WHERE (`is_seller` = '1') AND (`seller_id` NOT IN(0)) AND (`shop_url` = 'ctownsupermarket')
            if(count($data)){
                $flag = 1;
            }
        }
        if($flag == 1){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('ELOCAL - Your local stores, delivered to your doors'));
            return $resultPage; 
        }else{
            return $this->resultRedirectFactory->create()->setPath('region', ['_secure'=>$this->getRequest()->isSecure()]);
        }
    }
	
	public function getOffSellerCollection()
	{
		$currentweekday 	=	strtolower(date('l'));
		$currentmonth 		=	strtolower(date('F'));
		$currentdate 		=	date('d-m-Y');
		
		
		$collection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
		->getCollection()
		->addFieldToFilter(
			'is_seller',
			['eq' => 1]
		)
		->addFieldToFilter(
			['off_week_days', 'off_months', 'off_dates'],
			[
				['like' => "%".$currentweekday."%"],
				['like' => "%".$currentmonth."%"],
				['like' => "%".$currentdate."%"]
			]
		)
		->setOrder(
			'entity_id',
			'desc'
		);
		$offsellers 	=	array();
		
		foreach($collection as $seller){
			$offsellers[]	=	$seller->getSellerId();
		}
		if(!empty($offsellers)){
			return $offsellers;
		}else{
			return array(0);
		}
	}
}