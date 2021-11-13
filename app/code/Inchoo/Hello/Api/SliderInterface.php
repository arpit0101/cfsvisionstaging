<?php
namespace Inchoo\Hello\Api;
 
interface SliderInterface
{

    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @param string $area area
     * @return string Greeting message with users name.
     */
    public function getStoreSlider();
	
  
}
?>