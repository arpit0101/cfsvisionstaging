<?php
namespace Inchoo\Hello\Api;
 
interface WishlistInterface
{

    /**
     * Return Wishlist items.
     *
     * @param int $customerId
     * @return array
     */
    public function getWishlistForCustomer();

    /**
     * Return Added wishlist item.
     *
     * @param int $productId
     * @return array
     *
     */
    public function addWishlistForCustomer($product_id);
	
	/**
     * remove wishlist item.
     *
     * @param int $productId
     * @return array
     *
     */
    public function removeWishlistForCustomer($product_id);
	
  
}
?>