<?php
namespace Inchoo\Helloworld\Block\Adminhtml\Items\Column;

/**
 * Sales Order items name column renderer
 *
 * @api
 * @since 100.0.2
 */
 
class AdminVendorColumn extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
	/**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
	
	
    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getVendorName($productID)
    {
		$this->_objectManager 	= \Magento\Framework\App\ObjectManager::getInstance();
		
         $collection_product	=	$this->_objectManager->create('Webkul\Marketplace\Model\Product')
                                        ->getCollection()
                                        ->addFieldToFilter(
                                            'mageproduct_id',
                                            ['eq' => $productID]
                                        );
        $seller_id = null;
		foreach($collection_product as $value){
			$seller_id				=	$value->getSellerId();
			break;
		}
		$sellerData 	= 	$this->getSellerDetailsById($seller_id);
		//echo "<pre>"; print_r($seller_id); exit;
		if($sellerData){
			return $seller_name 	=	ucfirst($sellerData->getShopUrl());
		}
		else
		{
			return;
		}
	}
	
	function getSellerDetailsById($seller_id){
		 $data=$this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id',array('eq'=>$seller_id));
            foreach($data as $seller){ return $seller;}
	}
	
}
