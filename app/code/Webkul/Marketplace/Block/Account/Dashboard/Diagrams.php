<?php
namespace Webkul\Marketplace\Block\Account\Dashboard;

class Diagrams extends \Magento\Framework\View\Element\Template
{   
    /**
     * Api URL
     */
    const API_URL = 'http://chart.apis.google.com/chart';

    /**
     * Simple encoding chars
     *
     * @var string
     */
    protected $_simpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    /**
     * Extended encoding chars
     *
     * @var string
     */
    protected $_extendedEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';

    /**
     * Chart width
     *
     * @var string
     */
    protected $_width = '780';

    /**
     * Chart height
     *
     * @var string
     */
    protected $_height = '384';

    /**
     * Google chart api data encoding
     *
     * @var string
     */
    protected $_encoding = 'e';

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
    * @param Context $context
    * @param array $data
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param \Magento\Customer\Model\Session $customerSession
    */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getMonthlysale()
    {
        $seller_id = $this->_customerSession->getCustomerId();
        $data=array();  
		$canceledorders 	= 	$this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()->addAttributeToFilter('state', 'canceled');
		
		$all_canceled_order	=	[];
		if($canceledorders){
			foreach($canceledorders as $orderdata){
				$all_canceled_order[]	=	$orderdata->getId();
			}
		}
        $curryear = date('Y');
        for($i=0;$i<=12;$i++){
            $date1=$curryear."-".$i."-01 00:00:00";
            $date2=$curryear."-".$i."-31 23:59:59";
            $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist')
                            ->getCollection()
                            ->addFieldToFilter(
                                'seller_id',
                                ['eq' => $seller_id]
                            )
                            ->addFieldToFilter(
                                'order_id',
                                ['neq' => 0]
                            )->addFieldToFilter(
                                'order_id',
                                ['nin' => $all_canceled_order]
                            );
            $month=$collection->addFieldToFilter('created_at', array('datetime' => true,'from' =>  $date1,'to' =>  $date2));
            $sum=array();
            $temp=0;
            foreach ($collection as $record) {
				$temp += $record->getActualSellerAmount();
            }
            $price = round($temp,2);
            $data[$i]=$price;
        }
        return $data;
    }

    /**
     * Get chart url
     *
     * @param bool $directUrl
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getChartUrl($directUrl = true)
    {   
        $params = [
            'cht' => 'lc',
            'chf' => 'bg,s,ffffff',
            //'chm' => 'h,f2ebde,0,0:1:.1,1,-1',
            'chm' => 'N,000000,0,-1,11',
            'chxt' => 'x,y',
            'chds' =>'a',
            //'chg' => '0,10',
        ];

        $dates = [];
        $datas = [];

        $thisdataarray = array();

        $getMonthlysale = $this->getMonthlysale();

        $total_sale = max($getMonthlysale);

        foreach ($getMonthlysale as $key => $value) {
            $dates[] = $key;
            $thisdataarray[$key] = $value;
        }

        if($total_sale){
            $a = $total_sale/10;
            $axis_y_arr = array();
            for ($i=1; $i<=10 ; $i++) { 
                array_push($axis_y_arr, $a*$i);
            }
            $axis_y = implode('|', $axis_y_arr);
        }else{
            $axis_y = '10|20|30|40|50|60|70|80|90|100';
        }

        $params['chxl'] = '0:||'.__('January').'|'.__('February').'|'.__('March').'|'.__('April').'|'.__('May').'|'.__('June').'|'.__('July').'|'.__('August').'|'.__('September').'|'.__('October').'|'.__('November').'|'.__('December');

        //Google encoding values
        if ($this->_encoding == "s") {
            // simple encoding
            $params['chd'] = "s:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "_";
        } else {
            // extended encoding
            $params['chd'] = "e:";
            $dataDelimiter = "";
            $dataSetdelimiter = ",";
            $dataMissing = "__";
        }

        $minvalue = 0;
        $maxvalue = $total_sale;

        $params['chd'] = 't:'.implode(',', $thisdataarray);

        $valueBuffer = [];

        // chart size
        $params['chs'] = $this->_width . 'x' . $this->_height;


        // return the encoded data
        if ($directUrl) {
            $p = [];
            foreach ($params as $name => $value) {
                $p[] = $name . '=' . urlencode($value);
            }
            return self::API_URL . '?' . implode('&', $p);
        } else {
            $_dashboardData = $this->_objectManager->get('Webkul\Marketplace\Helper\Dashboard\Data');
            $gaData = urlencode(base64_encode(json_encode($params)));
            $gaHash = $_dashboardData->getChartDataHash($gaData);
            $params = ['ga' => $gaData, 'h' => $gaHash];
            return $this->getUrl('*/*/dashboard_tunnel', ['_query' => $params, '_secure' => $this->getRequest()->isSecure()]);
        }
    }
    /**
     * Return pow
     *
     * @param int $number
     * @return int
     */
    protected function _getPow($number)
    {
        $pow = 0;
        while ($number >= 10) {
            $number = $number / 10;
            $pow++;
        }
        return $pow;
    }
}
