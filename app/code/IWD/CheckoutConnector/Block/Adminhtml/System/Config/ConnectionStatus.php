<?php

namespace IWD\CheckoutConnector\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use IWD\CheckoutConnector\Helper\Data;

/**
 * Class ConnectionStatus
 *
 * @package IWD\CartToQuote\Block\Adminhtml\System\Config
 */
class ConnectionStatus extends Field
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $connectionCheck = $this->helper->checkIsAllow();

        $message = ($connectionCheck)
            ? '<b style="color:#059147; display: block;">' . __('Connection Successful') . '</b>'
            : '<b style="color:#D40707; display: block;">' .$this->helper->getErrorMessage() . '</b>';

        $note = $this->helper->getHelpText();
        $note = empty($note) ? '' : '<p class="note"><span>' . $note . '</span></p>';
        return "<span style='display:block; margin-top: 8px;'>" . $message . "</span>" . $note;
    }
}
