<?php
namespace Webkul\Marketplace\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Controller\UiActionInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Render
 */
abstract class AbstractAction extends \Magento\Customer\Controller\AbstractAccount implements UiActionInterface
{
    /**
     * @var UiComponentFactory
     */
    protected $factory;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     */
    public function __construct(Context $context, UiComponentFactory $factory)
    {
        parent::__construct($context);
        $this->factory = $factory;
    }

    /**
     * Getting name
     *
     * @return mixed
     */
    protected function getName()
    {
        return $this->_request->getParam('name');
    }

    /**
     * Getting component
     *
     * @return mixed
     */
    protected function getComponent()
    {
        return $this->_request->getParam('component');
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function executeAjaxRequest()
    {
        $this->execute();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
