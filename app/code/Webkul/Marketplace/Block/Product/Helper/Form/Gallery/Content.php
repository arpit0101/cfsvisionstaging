<?php
namespace Webkul\Marketplace\Block\Product\Helper\Form\Gallery;

use Magento\Catalog\Model\Product;
use Webkul\Marketplace\Block\Product\Media\Uploader;
use Magento\Framework\View\Element\AbstractBlock;

class Content extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Catalog\Model\Product\Media\Config
     */
    protected $_mediaConfig;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $_fileSizeService;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Catalog\Model\Product\Media\Config $mediaConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\Product\Media\Config $mediaConfig,
        \Magento\Framework\File\Size $fileSize,
        \Magento\Framework\Registry $coreRegistry,
        Product $product,
        array $data = []
    ) {
        $this->Product = $product;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_mediaConfig = $mediaConfig;
        $this->_fileSizeService = $fileSize;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * Get file size
     *
     * @return \Magento\Framework\File\Size
     */
    public function getFileSizeService()
    {
        return $this->_fileSizeService;
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('current_product');
    }

    public function getImagesJson()
    {       
        $product_coll =  $this->getProduct();
        $value = $product_coll->getMediaGalleryImages();
        $images = array();
        if (count($value)>0) {
            foreach ($value as &$image) {
                $image['url'] = $this->_mediaConfig->getMediaUrl($image['file']);
                array_push($images, $image->getData());
            }
            return $this->_jsonEncoder->encode($images);
        }
        return '[]';
    }

    public function getImageTypes()
    {
        $imageTypes = [];
        $product_coll =  $this->getProduct();
        foreach ($this->getMediaAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            $imageTypes[$attribute->getAttributeCode()] = [
                'code' => $attribute->getAttributeCode(),
                'value' => $product_coll[$attribute->getAttributeCode()],
                'label' => $attribute->getFrontend()->getLabel(),
                //'scope' => __($this->getElement()->getScopeLabel($attribute)),
                'name' => "product[".$attribute->getAttributeCode()."]",
            ];
        }
        return $imageTypes;
    }

    /**
     * @return bool
     */
    public function hasUseDefault()
    {
        foreach ($this->getMediaAttributes() as $attribute) {
            if ($this->getElement()->canDisplayUseDefault($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Enter description here...
     *
     * @return array
     */
    public function getMediaAttributes()
    {
        return $this->Product->getMediaAttributes();
    }

    /**
     * @return string
     */
    public function getImageTypesJson()
    {
        return $this->_jsonEncoder->encode($this->getImageTypes());
    }
}
