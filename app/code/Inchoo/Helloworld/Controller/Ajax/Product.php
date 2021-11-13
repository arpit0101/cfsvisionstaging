<?php
namespace Inchoo\Helloworld\Controller\Ajax;
	use Magento\Framework\App\Action\Context;
	class Product extends \Magento\Framework\App\Action\Action
	{
		protected $_resultPageFactory;
		protected $_sellerlistCollectionFactory;
		protected $_resultJsonFactory;
		
		/**
		* @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
		*/
		protected $_productCollectionFactory;
		
		public function __construct(
		Context $context, 
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $sellerlistCollectionFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		array $data = []
		)
		{
			$this->_resultPageFactory 			= 	$resultPageFactory;
			$this->_categoryFactory 			= 	$categoryFactory;
			$this->_sellerlistCollectionFactory = 	$sellerlistCollectionFactory;
			$this->_resultJsonFactory 			= 	$resultJsonFactory;
			$this->_productCollectionFactory 	= $productCollectionFactory;
			
			parent::__construct($context);
		}
		
		public function execute()
		{
			$layout_object = $this->_objectManager->get('\Magento\Framework\View\LayoutInterface');
			$html = $layout_object
            ->createBlock('\Inchoo\Helloworld\Block\ProductSearch')
            ->setTemplate('Inchoo_Helloworld::product_search.phtml')
            ->toHtml();
			$this->getResponse()
				->setHeader('Content-Type', 'text/html')
				->setBody($html);
			
			return;
		}
	}