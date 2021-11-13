<?php
namespace Inchoo\Hello\Api;
 
interface SellerProductInterface
{

    /**
	* Returns greeting message to user
	*
	* @api
	* @param string $name Users name.
	* @param string $area area
	* @return string Greeting message with users name.
	*/
    public function getProduct($seller_id, $cat_id);
	
  
}
?>