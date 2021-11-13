<?php


namespace Inchoo\HelloWorld\Model;
	 
use Magento\Framework\Model\AbstractModel;
	 
	class Data extends AbstractModel
	{	
	    protected function _construct()
	    {
	        $this->_init('Inchoo\HelloWorld\Model\ResourceModel\Data');
	    }
	}