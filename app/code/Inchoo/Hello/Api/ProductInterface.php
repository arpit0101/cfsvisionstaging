<?php
namespace Inchoo\Hello\Api;
 
interface ProductInterface
{

    /**
     * Returns product info by barcode
     *
     * @api
     * @param string $barcode .
     * @return string Product data.
     */
    public function getProductByBarcode($barcode);
	
	/**
     * Returns product info by sku and attribute_id
     *
     * @api
     * @param string $sku .
     * @param string $attribute_id .
     * @return string Product data.
     */
    public function getConfigurableProductChilds($sku, $attribute_id);
	
	/**
     * Returns product info by sku
     *
     * @api
     * @param string $sku
     * @return mixed.
     */
    public function getProductInfo($sku);
	
	/**
     * Returns product attribute info by attribute_id and $product_id
     *
     * @api
     * @param string $attribute_id
     * @param string $product_id
     * @return mixed.
     */
    public function getAttributeInfo($attribute_id, $product_id);
  
}
?>