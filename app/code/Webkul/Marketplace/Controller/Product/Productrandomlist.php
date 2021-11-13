<?php
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Productrandomlist extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /** 
     * @var \Magento\Catalog\Model\Product 
     */
    protected $productlists;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
       $page = $this->getRequest()->getParam('page');
       echo $this->_view->getLayout()->createBlock('Webkul\Marketplace\Block\Product\Homerandomitems')->setTemplate('Webkul_Marketplace::product/homerandomitemsajax.phtml')->toHtml();
       die;
    }
}
