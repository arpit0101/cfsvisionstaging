<?php
namespace Webkul\Marketplace\Controller\Product\Initialization\Helper;

interface HandlerInterface
{
    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function handle(\Magento\Catalog\Model\Product $product);
}
