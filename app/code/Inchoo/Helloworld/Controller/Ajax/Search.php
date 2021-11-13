<?php
namespace Inchoo\Helloworld\Controller\Ajax;
	use Magento\Framework\App\Action\Context;
	class Search extends \Magento\Framework\App\Action\Action
	{
		protected $_resultPageFactory;
		protected $_sellerlistCollectionFactory;
		protected $_resultJsonFactory;
		protected $_layout;
		public  $imageHelperFactory;
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
		\Magento\Framework\View\LayoutInterface $layout,
		\Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
		array $data = []
		)
		{
			$this->_resultPageFactory 			= 	$resultPageFactory;
			$this->_categoryFactory 			= 	$categoryFactory;
			$this->_sellerlistCollectionFactory = 	$sellerlistCollectionFactory;
			$this->_resultJsonFactory 			= 	$resultJsonFactory;
			$this->_productCollectionFactory 	= 	$productCollectionFactory;
			$this->_layout 						= 	$layout;
			$this->imageHelperFactory 			= 	$imageHelperFactory;
			parent::__construct($context);
		}
		
		public function execute()
		{
			$html = $this->_layout
            ->createBlock('\Inchoo\Helloworld\Block\HeaderSearch')
            ->setTemplate('Inchoo_Helloworld::header_search.phtml')
            ->toHtml();
			$this->getResponse()
				->setHeader('Content-Type', 'text/html')
				->setBody($html);
			
			return;
		}
	}