<?php
namespace SR\DeliveryDate\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class DeliveryDateConfigProvider implements ConfigProviderInterface
{
    const XPATH_FORMAT = 'sr_deliverydate/general/format';
    const XPATH_DISABLED = 'sr_deliverydate/general/disabled';
    const XPATH_TIMESLOTS_SUNDAY = 'sr_deliverydate/general/timeslots_sunday';
    const XPATH_TIMESLOTS_MONDAY = 'sr_deliverydate/general/timeslots_monday';
    const XPATH_TIMESLOTS_TUESDAY = 'sr_deliverydate/general/timeslots_tuesday';
    const XPATH_TIMESLOTS_WEDNESDAY = 'sr_deliverydate/general/timeslots_wednesday';
    const XPATH_TIMESLOTS_THURSDAY = 'sr_deliverydate/general/timeslots_thursday';
    const XPATH_TIMESLOTS_FRIDAY = 'sr_deliverydate/general/timeslots_friday';
    const XPATH_TIMESLOTS_SATURDAY = 'sr_deliverydate/general/timeslots_saturday';
    const XPATH_DELAY = 'sr_deliverydate/general/timeslots_delay';
    const XPATH_DISABLEDORDER = 'sr_deliverydate/general/disableorder';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
		
        $store 			= 	$this->getStoreId();
        $disabledorder 	  	= 	$this->scopeConfig->getValue(self::XPATH_DISABLEDORDER, ScopeInterface::SCOPE_STORE, $store);
        $disabled 	  	= 	$this->scopeConfig->getValue(self::XPATH_DISABLED, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[0] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_SUNDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[1] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_MONDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[2] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_TUESDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[3] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_WEDNESDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[4] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_THURSDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[5] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_FRIDAY, ScopeInterface::SCOPE_STORE, $store);
        $timeslots[6] 	= 	$this->scopeConfig->getValue(self::XPATH_TIMESLOTS_SATURDAY, ScopeInterface::SCOPE_STORE, $store);
        $delay		  	= 	$this->scopeConfig->getValue(self::XPATH_DELAY, ScopeInterface::SCOPE_STORE, $store);
        $format 	  	= 	$this->scopeConfig->getValue(self::XPATH_FORMAT, ScopeInterface::SCOPE_STORE, $store);
		
		
        date_default_timezone_set('Asia/Amman');
		
		$seven_days 	=	[];
		for($i=0; $i<=6; $i++){
			$currentdate	=	strtotime("+".$i." day");
			$currentslots	=	explode(PHP_EOL, $timeslots[date("w", $currentdate)]);
			$updatedslots 	=	[];
			if(!empty($currentslots)){
				foreach($currentslots as $timeslot){
					$slotdata 		=	explode("-", $timeslot);
					$dateandtime	=	strtotime(date("Y-m-d", $currentdate). " ".$slotdata[0]);
					
					if(count($slotdata)>1){
						if(time() < ($dateandtime) - (3600 * $delay)){
							$updatedslots[] 	=	[
														'withdate'=>date("Y-m-d h:i A", strtotime(date("Y-m-d", $currentdate). " ".$slotdata[0])) ." - ". date("Y-m-d h:i A", strtotime((date("Y-m-d", $currentdate). " ".$slotdata[1]))),
														'withoutdate'=> date("h:i A", strtotime(date("Y-m-d", $currentdate). " ".$slotdata[0])) ." - ". date("h:i A", strtotime((date("Y-m-d", $currentdate). " ".$slotdata[1])))
													];
						}
					}
				}
			}
			$seven_days[]	=	[
									'Day'=>date("D", $currentdate),
									'dayint'=>date("w", $currentdate),
									'datestring'=>date("Y-m-d", $currentdate),
									'date'=>date("d", $currentdate),
									'timeslots'=>$updatedslots
								];
		}
		
        $noday = 0;
        if($disabled == -1) {
            $noday = 1;
        }
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollection = $objectManager->get('Magento\Sales\Model\ResourceModel\Order\CollectionFactory');
        $current_date = date('Y-m-d');
        $collection = $orderCollection->create()
            ->addAttributeToFilter('status', ['in' => 'pending'])
            ->addAttributeToFilter('created_at', ['gteq'=>$current_date.' 00:00:00'])
            ->addAttributeToFilter('created_at', ['lteq'=>$current_date.' 23:59:59']);
        $todayorder = $collection->getSize();
		//echo $todayorder;
		if($todayorder >= $disabledorder){
			$noday = 1;
			$disabled = '0,1,2,3,4,5,6';
		}
		$config = [
            'shipping' => [
                'delivery_date' => [
                    'format' => $format,
                    'disabled' => $disabled,
                    'delay' => $delay,
                    'noday' => $noday,
                    'timeslots' => $seven_days,
					'disabledorder'=>$disabledorder,
                ]
            ]
        ];
        /* echo '<pre>';
		print_r($config);
		die("test"); */
        return $config;
    }

    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }
}