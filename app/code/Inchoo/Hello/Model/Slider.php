<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\SliderInterface;

use Mageplaza\BannerSlider\Helper\Data as bannerHelper;
 
class Slider implements SliderInterface
{
	protected $_objectManager;
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		bannerHelper $bannerHelper,
		array $data = []
	) {
		
		$this->_objectManager 				=   $objectManager;
		$this->_bannerHelper 				=   $bannerHelper;
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function getStoreSlider() {
		
        $collection = $this->_bannerHelper->getBannerCollection(2)->addFieldToFilter('status', 1);
		
		$slider_data 	=	[];
		$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
		$ImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'mageplaza/bannerslider/banner/image/';
		if($collection){
			foreach($collection as $slider){
				$url 	=	($slider->getData('url_banner')!= null)?$slider->getData('url_banner'):"";
				$slider_data[]	=	['image'=>$ImageUrl.$slider->getData('image'), 'link'=>$url];
			}
		}
		return $slider_data;
    }
}