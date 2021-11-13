<?php
namespace Webkul\Marketplace\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Orderprodetails
 */
class Orderprodetails extends Column
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
            	$product_name = $item[$fieldName];
				$result = array();
				if ($options = unserialize($item['product_options'])) {
					if (isset($options['options'])) {
						$result = array_merge($result, $options['options']);
					}
					if (isset($options['additional_options'])) {
						$result = array_merge($result, $options['additional_options']);
					}
					if (isset($options['attributes_info'])) {
						$result = array_merge($result, $options['attributes_info']);
					}
				}
				if($_options = $result){  
					$pro_option_data = '<dl class="item-options">';
					foreach ($_options as $_option) {
						$pro_option_data .= '<dt>'.$_option['label'].'</dt>';
						if (!$this->getPrintStatus()){
							//$_formatedOptionValue = $this->getFormatedOptionValue($_option);
							$_formatedOptionValue = $_option;
							$class = '';
							if (isset($_formatedOptionValue['full_view'])){ 
								$class = "truncated"; 
							}
							$pro_option_data .= '<dd class="'.$class.'">'.$_option['value'];
							if (isset($_formatedOptionValue['full_view'])){
							$pro_option_data .= '<div class="truncated_full_value"><dl class="item-options"><dt>'.$_option['label'].'</dt><dd>'.$_formatedOptionValue['full_view'].'</dd></dl></div>';
							}
							$pro_option_data .= '</dd>';
						}else {
							$pro_option_data .= '<dd>'.nl2br((isset($_option['print_value']) ? $_option['print_value'] : $_option['value']) ).'</dd>';
						}
					}
					$pro_option_data .= "</dl>";
					$product_name = $product_name."<br/>".$pro_option_data;
				}else{
					$product_name = $product_name."<br/>";
				}
				/*prepare product quantity status*/
				$is_for_item_pay = 0;
				if ($item['qty_ordered'] > 0){
					$product_name = $product_name.__('Ordered').": <strong>".($item['qty_ordered']*1)."</strong><br />";
				}
				if ($item['qty_invoiced'] > 0){
					$is_for_item_pay++;
					$product_name = $product_name.__('Invoiced').": <strong>".($item['qty_invoiced']*1)."</strong><br />";
				}
				if ($item['qty_shipped'] > 0){
					$is_for_item_pay++;
					$product_name = $product_name.__('Shipped').": <strong>".($item['qty_shipped']*1)."</strong><br />";
				}
				if ($item['qty_canceled'] > 0){
					$is_for_item_pay=4;
					$product_name = $product_name.__('Canceled').": <strong>".($item['qty_canceled']*1)."</strong><br />";
				}
				if ($item['qty_refunded'] > 0){
					$is_for_item_pay=3;
					$product_name = $product_name.__('Refunded').": <strong>".($item['qty_refunded']*1)."</strong><br />";
				}				

				$item[$fieldName . '_html'] = $product_name;
            }
        }

        return $dataSource;
    }
}
