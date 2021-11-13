<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\ScheduleInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Schedule implements ScheduleInterface
{
	/**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
	
/**
	* @param \Magento\Framework\ObjectManagerInterface $objectManager
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
    public function getSchedule() {
		$scopeConfig 	= 	$this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
		$storeScope 	= 	$scopeConfig::SCOPE_TYPE_DEFAULT;
		
		$disableddays 	= 	$scopeConfig->getValue('sr_deliverydate/general/disabled', $storeScope);
        $timeslots[0] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_sunday', $storeScope);
        $timeslots[1] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_monday', $storeScope);
        $timeslots[2] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_tuesday', $storeScope);
        $timeslots[3] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_wednesday', $storeScope);
        $timeslots[4] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_thursday', $storeScope);
        $timeslots[5] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_friday', $storeScope);
        $timeslots[6] 	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_saturday', $storeScope);
        $delay		  	= 	$scopeConfig->getValue('sr_deliverydate/general/timeslots_delay', $storeScope);
        $format 	  	= 	$scopeConfig->getValue('sr_deliverydate/general/format', $storeScope);
		
		$disableddays	=	explode(",", $disableddays);
        
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
														'withdate'=>date("Y-d-m h:i A", strtotime(date("Y-m-d", $currentdate). " ".$slotdata[0])) ." - ". date("Y-d-m h:i A", strtotime((date("Y-m-d", $currentdate). " ".$slotdata[1]))),
														'withoutdate'=> date("h:i A", strtotime(date("Y-m-d", $currentdate). " ".$slotdata[0])) ." - ". date("h:i A", strtotime((date("Y-m-d", $currentdate). " ".$slotdata[1])))
													];
						}
					}
				}
			}
			$store 			= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
			$Displaytext	=	date("D", $currentdate). " ".date("d", $currentdate);
			if($i==0){
				$Displaytext	=	"Today";
				if($store->getCode()=="ar"){
					$Displaytext	=	"اليوم";
				}
			}else if($i==1){
				$Displaytext	=	"Tomorrow";
				if($store->getCode()=="ar"){
					$Displaytext	=	"غدا";
				}
			}
			$disabled 			=	0;
			if(count($updatedslots)<1){
				$disabled 			=	1;
			}else if(in_array(date("w", $currentdate), $disableddays)){
				$disabled 			=	1;
				$updatedslots		=	[];
			}
			$seven_days[]	=	[
									'Day'=>date("D", $currentdate),
									'Displaytext'=>$Displaytext,
									'dayint'=>date("w", $currentdate),
									'datestring'=>date("Y-m-d", $currentdate),
									'date'=>date("d", $currentdate),
									'timeslots'=>$updatedslots,
									'disabled'=>$disabled
								];
		}
		
        $noday = 0;
        if($disabled == -1) {
            $noday = 1;
        }

        $config = [
            'shipping' => [
                'delivery_date' => [
                    'format' => $format,
                    'disabled' => $disabled,
                    'delay' => $delay,
                    'noday' => $noday,
                    'timeslots' => $seven_days,
                ]
            ]
        ];
        return $config;
	}
}