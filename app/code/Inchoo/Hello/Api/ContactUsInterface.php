<?php
namespace Inchoo\Hello\Api;
 
interface ContactUsInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function ContactUs();
  
}
?>