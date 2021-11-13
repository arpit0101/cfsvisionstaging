<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-13 01:08:32
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2017-11-11 23:02:48
 */

namespace PHPCuong\Region\Controller\Adminhtml\Area;

class NewAction extends \Magento\Backend\App\Action
{
    protected $coreRegistry = null;
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_Region::area_create';

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultPageFactory;
    protected $_regionCollection;
    protected $_options;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Create new Region
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultPageFactory = $this->resultPageFactory->create();

        $FormData = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($FormData)) {
            $model = $this->_objectManager->create('PHPCuong\Region\Model\Area');
            $model->setData($FormData);
            $this->coreRegistry->register('phpcuong_area', $model);
        }

        $countryHelper 	= 	$this->_objectManager->get('Magento\Directory\Model\Config\Source\Country');
		
		$cityHelper 	= 	$this->cityArray();
        $this->coreRegistry->register('phpcuong_region_country_list', $countryHelper->toOptionArray());
        $this->coreRegistry->register('phpcuong_region_list', $cityHelper);

        return $resultPageFactory;
    }
	
	public function cityArray($isMultiselect = false, $foregroundCountries = '')
    {
		$ret = [];
		
        if (!$this->_options) {
            $this->_options = $this->_objectManager->get('PHPCuong\Region\Model\ResourceModel\Region\Collection')->loadData()->toArray();
			
			foreach ($this->_options["items"] as $key => $value)
			{
				$ret[] = [
					'value' => $value['region_id'], 
					'label' => $value['default_name']
				];
			}
			$this->_options = $ret;
			
        }
        $options = $this->_options;
        if (!$isMultiselect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }
        return $options;
    }
}
