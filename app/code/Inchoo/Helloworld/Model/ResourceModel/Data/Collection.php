<?php


namespace Inchoo\HelloWorld\Model\ResourceModel\Data;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;


class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
	    'Inchoo\HelloWorld\Model\Data',
	    'Inchoo\HelloWorld\Model\ResourceModel\Data'
	);
    }
}