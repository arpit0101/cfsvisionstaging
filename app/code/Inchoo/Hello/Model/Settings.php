<?php
namespace Inchoo\Hello\Model;

use Inchoo\Hello\Api\SettingsInterface;
 
class Settings implements SettingsInterface
{
	protected $_objectManager;
/**
	* @param \Magento\Framework\View\Element\Template\Context $context
	* @param \Magento\Catalog\Helper\Category $categoryHelper
	* @param array $data
	*/
	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
		array $data = []
	) {
		
		$this->_objectManager 				=   $objectManager;
	}	

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function getInfo() {
		
        return [
			['android_version'=> 5,
			'ios_version'=> 1,
			'show_popup'=> 1,
			'force_update'=> 1,
			'message' => 'Please update app',
			'message_ar' => 'يرجى تحديث التطبيق',
			'message_force' => 'Please update app, without update functionality will not work properly.',
			'message_force_ar' => 'يرجى تحديث التطبيق ، دون وظيفة التحديث لن تعمل بشكل صحيح.',
			]
		];
    }
}