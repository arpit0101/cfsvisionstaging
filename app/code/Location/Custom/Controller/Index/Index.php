<?php

namespace Location\Custom\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\RequestInterface;

Class Index extends \Magento\Framework\App\Action\Action {

	/**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
	
	
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
       
		/** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Location'));
		
		/* $block = $this->_view->getLayout()
                ->createBlock('Location\Custom\Block\Categories')
                ->setTemplate('Location_Custom::categories.phtml')
                ->toHtml();
        $this->getResponse()->setBody($block); */
		// $resultPage->addHandle('custom_index_index'); //loads the layout of custom_index_index.xml file with its name
        return $resultPage;
    }
}
