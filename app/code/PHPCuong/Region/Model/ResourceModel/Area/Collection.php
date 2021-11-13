<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-11 23:48:21
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-12 17:58:16
 */

namespace PHPCuong\Region\Model\ResourceModel\Area;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'area_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init('PHPCuong\Region\Model\Area', 'PHPCuong\Region\Model\ResourceModel\Area');
    }
}
