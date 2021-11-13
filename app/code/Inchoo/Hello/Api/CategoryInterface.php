<?php
namespace Inchoo\Hello\Api;
 
interface CategoryInterface
{

    /**
	* Retrieve current store categories
	*
	* @return array
	*/    
	public function getCategories();
    
}
?>