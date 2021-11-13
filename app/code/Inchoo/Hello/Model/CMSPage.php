<?php
namespace Inchoo\Hello\Model;

use Inchoo\Hello\Api\CMSPageInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
 
class CMSPage implements CMSPageInterface
{
	/**
     * @var PageRepositoryInterface
     */
    private $pageRepository;
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    /**
     * @var PageFactory
     */
    private $pageFactory;
    /**
     * @var Page
     */
    private $pageResource;
    /**
     * @var Page\CollectionFactory
     */
    private $pageCollectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var State
     */
    private $appState;
    /**
     * @var Emulation
     */
    private $emulation;
    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterProvider $filterProvider
     * @param PageFactory $pageFactory
     * @param Page $pageResource
     * @param CollectionProcessorInterface $collectionProcessor
     * @param State $appState
     * @param Emulation $emulation
     */
    public function __construct(
        PageRepositoryInterface $pageRepository,
        FilterProvider $filterProvider,
        PageFactory $pageFactory,
        Page $pageResource,
        CollectionProcessorInterface $collectionProcessor,
        State $appState,
        Emulation $emulation
    ) {
        $this->pageRepository = $pageRepository;
        $this->filterProvider = $filterProvider;
        $this->pageFactory = $pageFactory;
        $this->pageResource = $pageResource;
        $this->collectionProcessor = $collectionProcessor;
        $this->appState = $appState;
        $this->emulation = $emulation;
    }
	
    
	/**
     * Returns greeting pages
     *
     * @api
     * @param string $page_url .
     * @return string Page data.
     */
    public function getPageData($page_url) {
		
        if(!empty($page_url)){
			
			$page = $this->pageFactory->create();
			$page->setStoreId(1);
			$this->pageResource->load($page, $page_url, PageInterface::IDENTIFIER);
			if (!$page->getId()) {
				return [["status"=>false,"msg"=>"Invalid page key."]];
			}
			$content = $this->getPageContentFiltered($page->getContent());
			$page->setContent($content);
			return [$page->getData()];
        } else {
            return [["status"=>false,"msg"=>"Invalid page key."]];
        }
    }
	
	/**
     * @param string $content
     * @return string
     */
    private function getPageContentFiltered($content)
    {
        $emulatedResult = $this->appState->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$this->filterProvider->getPageFilter(), 'filter'],
            [$content]
        );
        return $emulatedResult;
    }
}