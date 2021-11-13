<?php
namespace Inchoo\Hello\Api;
 
interface CartInterface
{
    /**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function getCart();
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function getCartForGuest($cartId);
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function mergeGuestToCustomerCart($guestQuoteId);
    
	/**
     * Returns greeting id to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting id with users name.
     */
    public function cartsid();
    
	
	/**
     * Returns greeting message to user
     *
     * @api
     * @param string $name Users name.
     * @return string Greeting message with users name.
     */
    public function setShippingInformation();
  
}
?>