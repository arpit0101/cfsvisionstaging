<?php

namespace Location\Custom\Controller\Ajax;

Class Index extends \Magento\Framework\App\Action\Action {

    public function __construct(\Magento\Framework\App\Action\Context $context) {
        parent::__construct($context);
    }

    public function execute() {
        $city_id = (int) $this->getRequest()->getParam('city_id', false);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('directory_country_area');

		//Select Data from table
		$sql = "Select * FROM " . $tableName . " where `region_id`='".$city_id."' ORDER BY default_name ASC";
		$result = $connection->fetchAll($sql);
		if(!empty($result)){
			foreach($result as &$row){
				$row['default_name']	=	__($row['default_name']);
			}
		}
		echo json_encode($result);die; 
    }
}
