<?php

/**
 *
 * @Author              Ngo Quang Cuong <bestearnmoney87@gmail.com>
 * @Date                2016-12-12 22:29:31
 * @Last modified by:   nquangcuong
 * @Last Modified time: 2016-12-13 15:12:07
 */

namespace PHPCuong\Region\Controller\Adminhtml\Area;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'PHPCuong_Region::area_edit';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Magento_Customer::customer');
        return $resultPage;
    }

    /**
     * Edit Area page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // Get ID and create model
        $id = (int) $this->getRequest()->getParam('area_id');
        $model = $this->_objectManager->create('PHPCuong\Region\Model\Area');
        $model->setData([]);
        // Initial checking
        if ($id && $id > 0) {
            $model->load($id);
            if (!$model->getAreaId()) {
                $this->messageManager->addError(__('This area no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $default_name = $model->getDefaultName();
        }

        $FormData = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($FormData)) {
            $model->setData($FormData);
        }

        $this->_coreRegistry->register('phpcuong_region', $model);

        $countryHelper = $this->_objectManager->get('Magento\Directory\Model\Config\Source\Country');

        $this->_coreRegistry->register('phpcuong_region_country_list', $countryHelper->toOptionArray());

        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Area') : __('New Area'),
            $id ? __('Edit Area') : __('New Area')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Areas Manager'));
        $resultPage->getConfig()->getTitle()
            ->prepend($id ? 'Edit: '.$default_name.' ('.$id.')' : __('New Area'));

        return $resultPage;
    }
}
