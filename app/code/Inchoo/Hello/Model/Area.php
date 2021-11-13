<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\AreaInterface;
 
class Area implements AreaInterface
{
	protected $_objectManager;
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\View\Element\Template\Context $context,
		\PHPCuong\Region\Model\ResourceModel\Region\CollectionFactory $regionCollection,
		\PHPCuong\Region\Model\ResourceModel\Area\CollectionFactory $areaCollection,
		
		array $data = []
	) {
		
		$this->_objectManager = $objectManager;
		$this->_regionCollection 			= 	$regionCollection;
		$this->_areaCollection 				= 	$areaCollection;
		
		
	
	}	
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function getArea() {
		
		$collectionarea = $this->_regionCollection->create()->addFieldToSelect(
			['region_id','default_name','region_image']
		)
		->addFieldToFilter(
									'country_id',
									['eq' => 'AE']
								)
		->setOrder(
			'default_name',
			'asc'
		);
		// return $this->_storeManager->getStore()->getCode();
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		// return $store->getCode();
		$area_datas 		=	$collectionarea->getData();
		
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$all_areas 			=	array();
		foreach($area_datas as $index=>$area){
			
			$areaImageUrl 			= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
			
			$area['areaImageUrl']	=	$areaImageUrl; 
			$area['comingsoon']		=	0;
			/* if($index > 0){
				$area['comingsoon']	=	1;
			} */
			$all_areas[] 	=	$area;
		}
		// if($store->getCode()=="ar"){
			foreach($all_areas as &$area){
				$area['default_name_ar']	=	$area['default_name'];
				if($area['default_name']=="Amman"){
					$area['default_name_ar']	=	"عمان";
				}else if($area['default_name']=="ZARQA- Coming Soon"){
					$area['default_name_ar']	=	"الزرقاء- قريباً";
				}else if($area['default_name']=="BALQA- COMING SOON"){
					$area['default_name_ar']	=	"البلقاء- قريباً";
				}else if($area['default_name']=="IRBID"){
					$area['default_name_ar']	=	"إرْبِد";
				}else if($area['default_name']=="Abu Dhabi"){
					$area['default_name_ar']	=	"أبو ظبي";
				}
			}
		// }
		return $all_areas;
    }
}