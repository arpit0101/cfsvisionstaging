<?php
namespace Webkul\Marketplace\Controller\Mui\Bookmark;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Webkul\Marketplace\Controller\AbstractAction;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\BookmarkManagementInterface;

/**
 * Class Delete action
 */
class Delete extends AbstractAction
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $viewIds = explode('.', $this->_request->getParam('data'));
        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            array_pop($viewIds),
            $this->_request->getParam('namespace')
        );

        if ($bookmark && $bookmark->getId()) {
            $this->bookmarkRepository->delete($bookmark);
        }

    }
}
