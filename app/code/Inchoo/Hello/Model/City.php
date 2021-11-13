<?php
namespace Inchoo\Hello\Model;
use Inchoo\Hello\Api\CityInterface;
 
class City implements CityInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function getCity() {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('directory_country_region ');
		$sql = "Select * FROM " . $tableName . " where `country_id`='AE'";
		$result = $connection->fetchAll($sql);
        return $result;
    }
}