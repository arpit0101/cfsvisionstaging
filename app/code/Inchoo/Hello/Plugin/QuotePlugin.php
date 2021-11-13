<?php

namespace Inchoo\Hello\Plugin;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Catalog\Helper\ImageFactory as ProductImageHelper;

class QuotePlugin {

    /**
     * @param \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Quote\Api\Data\CartItemExtensionFactory $cartItemExtension, 
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
		ProductImageHelper $productImageHelper
	) {
        $this->cartItemExtension = $cartItemExtension;
		$this->productImageHelper = $productImageHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGet(
    \Magento\Quote\Api\CartRepositoryInterface $subject, $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * Add attribute values
     *
     * @param   \Magento\Quote\Api\CartRepositoryInterface $subject,
     * @param   $quote
     * @return  $quoteData
     */
    public function afterGetActiveForCustomer(
    \Magento\Quote\Api\CartRepositoryInterface $subject, $quote
    ) {
        $quoteData = $this->setAttributeValue($quote);
        return $quoteData;
    }

    /**
     * set value of attributes
     *
     * @param   $product,
     * @return  $extensionAttributes
     */
    private function setAttributeValue($quote) {
        $data = [];
		//echo $quote->getItemsCount(); die("testt");
        if ($quote->getItemsCount() > 0) {
            foreach ($quote->getItems() as $item) { 
                $data = [];
                $extensionAttributes = $item->getExtensionAttributes();
                if ($extensionAttributes === null) {
                    $extensionAttributes = $this->cartItemExtension->create();
                }
                $productData = $this->productRepository->create()->get($item->getSku());
                $imageurl =$this->productImageHelper->create()->init($productData, 'product_thumbnail_image')->setImageFile($productData->getThumbnail())->getUrl();				
                $extensionAttributes->setImage($imageurl);
                $extensionAttributes->setVendor($productData->getVendor());
            }
            $item->setExtensionAttributes($extensionAttributes);
            return $quote;
        }
    }
}