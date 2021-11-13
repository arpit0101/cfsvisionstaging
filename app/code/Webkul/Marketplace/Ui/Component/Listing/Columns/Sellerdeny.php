<?php
namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Sellerdeny extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) { 
                $item[$fieldName . '_html'] = "<button class='button'><span>deny</span></button>";
                $item[$fieldName . '_title'] = __('What is the reason to deny this seller?');
                $item[$fieldName . '_submitlabel'] = __('Deny');
                $item[$fieldName . '_cancellabel'] = __('Cancel');
                $item[$fieldName . '_sellerid'] = $item['seller_id'];

                $item[$fieldName . '_formaction'] = $this->urlBuilder->getUrl('marketplace/seller/deny');
            }
        }

        return $dataSource;
    }
}