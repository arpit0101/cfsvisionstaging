<?php
namespace Inchoo\Helloworld\Block\Order\Email\Items;


class DefaultItems extends \Magento\Sales\Block\Order\Email\Items\DefaultItems
{
    /**
     * @param mixed $item
     * @return mixed
     */
    public function getVendorName($productid)
    {
		//Get Object Manager Instance
		$objectManager 		= 	\Magento\Framework\App\ObjectManager::getInstance();
		$collection_product	=	$objectManager->create('Webkul\Marketplace\Model\Product')
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'mageproduct_id',
                                            ['eq' => $productid]
                                        );
		
		foreach($collection_product as $value){
			
			$seller_id			=	$value->getSellerId();
			
		}
		$sellerData 			= 	$this->getSellerDetailsById($seller_id);
		if($sellerData){
			if($sellerData->getShopUrl() != ''){
		    	return $seller_name 	=	ucfirst($sellerData->getShopUrl());
			}else{
				return $seller_name = 'not seller';
			}
		}else{
			return $seller_name = 'not seller';
		}
    }
	
	function getSellerDetailsById($seller_id){
		
		//Get Object Manager Instance
		$objectManager 	= 	\Magento\Framework\App\ObjectManager::getInstance();
		$data			=	$objectManager->create('Webkul\Marketplace\Model\Seller')
								->getCollection()
								->addFieldToFilter('seller_id',array('eq'=>$seller_id));
		foreach($data as $seller){ return $seller;}
		
	}
}
