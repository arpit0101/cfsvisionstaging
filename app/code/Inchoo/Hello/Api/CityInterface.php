<?php
namespace Inchoo\Hello\Api;
 
interface CityInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    //public function area();
    public function getCity();
	
  
}
?>