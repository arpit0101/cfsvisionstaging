<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-13 00:05:54
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-13 00:12:38
 */

namespace PHPCuong\Region\Block\Adminhtml;

/**
 * Adminhtml cms blocks content block
 */
class Area extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'PHPCuong_Region';
        $this->_controller = 'adminhtml_area';
        $this->_headerText = __('Area Manager');
        $this->_addButtonLabel = __('Add New Area');
        parent::_construct();
    }
}
