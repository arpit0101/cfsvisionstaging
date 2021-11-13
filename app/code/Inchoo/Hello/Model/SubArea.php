<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\SubAreaInterface;
 
class SubArea implements SubAreaInterface
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
    public function getSubArea($region_id) {
		
	
		if(!empty($region_id)){			
			$collectionarea = $this->_areaCollection->create()->addFieldToSelect(
				['area_id','default_name','area_image']
			)
			->addFieldToFilter(
				'region_id',
				['eq' => $region_id]
			)
			->setOrder(
				'default_name',
				'asc'
			);
			$subarea_data 		=	$collectionarea->getData();
			
			$store 				= 	$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
			$all_subareas 		=	array();
			foreach($subarea_data as $subarea){
				$subareaImageUrl 	= 	$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
				
				$subarea['subareaImageUrl']	=	$subareaImageUrl; 
				$all_subareas[] 	=	$subarea;
			}
			// if($store->getCode()=="ar"){
				foreach($all_subareas as &$area){
					if($area['default_name']=="Abdali"){
						$area['default_name_ar']	=	"العبدلي";
					}elseif($area['default_name']=="Abdoun"){
						$area['default_name_ar']	=	"عبدون";
						
					}elseif($area['default_name']=="Airport Road -Manaseer Gas"){
						$area['default_name_ar']	=	"طريق المطار- المناصير-";
						
					}elseif($area['default_name']=="Airport Road-Nakheel Village"){
						$area['default_name_ar']	=	"طريق المطار- قرية النخيل-";
					}elseif($area['default_name']=="Al Bayader"){
						$area['default_name_ar']	=	"البيادر";
					}elseif($area['default_name']=="Al Gardens"){
						$area['default_name_ar']	=	"الجاردينز";
					}elseif($area['default_name']=="Al Jandaweel"){
						$area['default_name_ar']	=	"الجنداويل";
					}elseif($area['default_name']=="Al Kursi"){
						$area['default_name_ar']	=	"الكرسي";
					}elseif($area['default_name']=="Al Rabiah"){
						$area['default_name_ar']	=	"الرابيه";
					}elseif($area['default_name']=="Al Rawabi"){
						$area['default_name_ar']	=	"الروابي";
					}elseif($area['default_name']=="Al Rawnaq"){
						$area['default_name_ar']	=	"الرونق";
					}elseif($area['default_name']=="Al Sahl"){
						$area['default_name_ar']	=	"السهل";
					}elseif($area['default_name']=="Al Sina'a"){
						$area['default_name_ar']	=	"الصناعة";
					}elseif($area['default_name']=="Dabouq"){
						$area['default_name_ar']	=	"دابوق";
					}elseif($area['default_name']=="Dabouq- Baccalaureate"){
						$area['default_name_ar']	=	"دابوق - البكالوريا";
					}elseif($area['default_name']=="Dabouq-Ferdous"){
						$area['default_name_ar']	=	"دابوق - الفردوس";
					}elseif($area['default_name']=="Daheit Al Rasheed"){
						$area['default_name_ar']	=	"ضاحية الرشيد";
					}elseif($area['default_name']=="Dahiet Al Hussain"){
						$area['default_name_ar']	=	"ضاحية الحسين";
					}elseif($area['default_name']=="Deir Ghbar"){
						$area['default_name_ar']	=	"دير غبار";
					}elseif($area['default_name']=="Hay Al Barakeh"){
						$area['default_name_ar']	=	"حي البركة";
					}elseif($area['default_name']=="Hay Al Khaledeen"){
						$area['default_name_ar']	=	"حي الخالدين";
					}elseif($area['default_name']=="Hay Al Saleheen"){
						$area['default_name_ar']	=	"حي الصالحين";
					}elseif($area['default_name']=="Jabal Al Weibdeh"){
						$area['default_name_ar']	=	"جبل الويبدة";
					}elseif($area['default_name']=="Jabal Amman"){
						$area['default_name_ar']	=	"جبل عمان";
					}elseif($area['default_name']=="Jordan University Street"){
						$area['default_name_ar']	=	"شارع الجامعة الاردنية";
					}elseif($area['default_name']=="Jubaiha"){
						$area['default_name_ar']	=	"الجبيهة";
					}elseif($area['default_name']=="Khalda"){
						$area['default_name_ar']	=	"خلدا";
					}elseif($area['default_name']=="King Hussein Business Park"){
						$area['default_name_ar']	=	"مدينة الحسين للاعمال";
					}elseif($area['default_name']=="Marj El Hamam"){
						$area['default_name_ar']	=	"مرج الحمام";
					}elseif($area['default_name']=="Mecca Street"){
						$area['default_name_ar']	=	"شارع مكة";
					}elseif($area['default_name']=="Medina Street"){
						$area['default_name_ar']	=	"شارع المدينة";
					}elseif($area['default_name']=="Shmaisani"){
						$area['default_name_ar']	=	"شميساني";
					}elseif($area['default_name']=="Sports City"){
						$area['default_name_ar']	=	"المدينة الرياضية";
					}elseif($area['default_name']=="Swefieh"){
						$area['default_name_ar']	=	"الصويفية";
					}elseif($area['default_name']=="Tla' Ali"){
						$area['default_name_ar']	=	"تلاع العلي";
					}elseif($area['default_name']=="Um El Summaq"){
						$area['default_name_ar']	=	"ام السماق";
					}elseif($area['default_name']=="Um Uthaiena"){
						$area['default_name_ar']	=	"ام اثينة";
					}elseif($area['default_name']=="Wadi El Seer"){
						$area['default_name_ar']	=	"وادي السير";
					}elseif($area['default_name']=="Wadi Saqra"){
						$area['default_name_ar']	=	"وادي صقرة";
					}elseif($area['default_name']=="Abu Alanda"){
						$area['default_name_ar']	=	"ابو علندا";
					}elseif($area['default_name']=="Abu Nsair"){
						$area['default_name_ar']	=	"ابو نصير";
					}elseif($area['default_name']=="Airport Road - Dunes Bridge"){
						$area['default_name_ar']	=	"طريق المطار -جسر ديونز";
					}elseif($area['default_name']=="Airport Road - Madaba Bridge"){
						$area['default_name_ar']	=	"طريق المطار جسر مأدبا";
					}elseif($area['default_name']=="Al Ashrafieh"){
						$area['default_name_ar']	=	"الأشرفية";
					}elseif($area['default_name']=="Al Bnayyat"){
						$area['default_name_ar']	=	"البنيات";
					}elseif($area['default_name']=="Al Fuhais"){
						$area['default_name_ar']	=	"الفحيص";
					}elseif($area['default_name']=="Al Hashmi Al Janobi"){
						$area['default_name_ar']	=	"الهاشمي الجنوبي";
					}elseif($area['default_name']=="Al Hashmi Al Shamali"){
						$area['default_name_ar']	=	"الهاشمي الشمالي";
					}elseif($area['default_name']=="Al Hummar"){
						$area['default_name_ar']	=	"الحمر";
					}elseif($area['default_name']=="Al Kamaliya"){
						$area['default_name_ar']	=	"الكمالية";
					}elseif($area['default_name']=="Al Muqabalain"){
						$area['default_name_ar']	=	"المقابلين";
					}elseif($area['default_name']=="Al Qwaismeh"){
						$area['default_name_ar']	=	"القويسمة";
					}elseif($area['default_name']=="Al Rajeeb"){
						$area['default_name_ar']	=	"الرجيب";
					}elseif($area['default_name']=="Al Ridwan"){
						$area['default_name_ar']	=	"الرضوان";
					}elseif($area['default_name']=="Arjan"){
						$area['default_name_ar']	=	"عرجان";
					}elseif($area['default_name']=="Bader Al Jadeda"){
						$area['default_name_ar']	=	"بدر الجديدة";
					}elseif($area['default_name']=="Daheit Al Yasmeen"){
						$area['default_name_ar']	=	"ضاحية الياسمين";
					}elseif($area['default_name']=="Dahiet Al Ameer Rashed"){
						$area['default_name_ar']	=	"ضاحية الامير راشد";
					}elseif($area['default_name']=="Downtown"){
						$area['default_name_ar']	=	"وسط البلد";
					}elseif($area['default_name']=="Hay Al Rahmanieh"){
						$area['default_name_ar']	=	"حي الرحمانية";
					}elseif($area['default_name']=="Iraq Al Ameer"){
						$area['default_name_ar']	=	"عراق الامير";
					}elseif($area['default_name']=="Jabal Al Zohor"){
						$area['default_name_ar']	=	"جبل الزهور";
					}elseif($area['default_name']=="Mahes"){
						$area['default_name_ar']	=	"ماحص";
					}elseif($area['default_name']=="Marka"){
						$area['default_name_ar']	=	"ماركا";
					}elseif($area['default_name']=="Naour"){
						$area['default_name_ar']	=	"ناعور";
					}elseif($area['default_name']=="Ras El Ain"){
						$area['default_name_ar']	=	"رأس العين";
					}elseif($area['default_name']=="Shafa Badran"){
						$area['default_name_ar']	=	"شفا بدران";
					}elseif($area['default_name']=="Swelieh"){
						$area['default_name_ar']	=	"صويلح";
					}elseif($area['default_name']=="Tareq"){
						$area['default_name_ar']	=	"طارق";
					}elseif($area['default_name']=="Daheit Al Ameer Hasan"){
						$area['default_name_ar']	=	"ضاحية الامير حسن";
					}elseif($area['default_name']=="Daheit Al Aqsa"){
						$area['default_name_ar']	=	"ضاحية الاقصى";
					}elseif($area['default_name']=="Jabal Amman"){
						$area['default_name_ar']	=	"جبل عمان";
					}elseif($area['default_name']=="Jabal Al Weibdeh"){
						$area['default_name_ar']	=	"جبل الويبدة";
					}elseif($area['default_name']=="Jabal Al Hussain"){
						$area['default_name_ar']	=	"جبل الحسين";
					}else if($area['default_name']=="IRBID"){
						$area['default_name_ar']	=	"إرْبِد";
					}else if($area['default_name']=="Dubai"){
						$area['default_name_ar']	=	"دبي";
					}
				}
			// }
			return $all_subareas;
		}else{
			return array('message'=>'Region id can not be null');
		}
    }
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
	
	function getdata($csvFile){
		$file_handle = fopen($csvFile, 'r');
		while (!feof($file_handle) ) {
			$line_of_text[] = fgetcsv($file_handle, 1024);
		}
		fclose($file_handle);
		return $line_of_text;
	}
}