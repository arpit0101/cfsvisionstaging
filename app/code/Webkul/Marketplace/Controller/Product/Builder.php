<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Cms\Model\Wysiwyg as WysiwygModel;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry;

class Builder
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @param ProductFactory $productFactory
     * @param Logger $logger
     * @param Registry $registry
     * @param WysiwygModel\Config $wysiwygConfig
     */
    public function __construct(
        ProductFactory $productFactory,
        Logger $logger,
        Registry $registry,
        WysiwygModel\Config $wysiwygConfig
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->wysiwygConfig = $wysiwygConfig;
    }

    /**
     * Build product based on user request
     *
     * @param RequestInterface $request
     * @return \Magento\Catalog\Model\Product
     */
    public function build($request,$store=0)
    {
        if(!empty($request['id'])){
            $productId = (int)$request['id'];
        }else{
            $productId = '';
        }
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $product->setStoreId($store);

        if(!empty($request['type'])){
            $typeId = $request['type'];
        }else{
            $typeId = '';
        }
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }

        $product->setData('_edit_mode', true);
        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                $this->logger->critical($e);
            }
        }
        if(!empty($request['set'])){
            $setId = (int)$request['set'];
        }else{
            $setId = '';
        }
        if ($setId) {
            $product->setAttributeSetId($setId);
        }

        $this->registry->register('product', $product);
        $this->registry->register('current_product', $product);
        $this->wysiwygConfig->setStoreId($store);
        return $product;
    }
}
