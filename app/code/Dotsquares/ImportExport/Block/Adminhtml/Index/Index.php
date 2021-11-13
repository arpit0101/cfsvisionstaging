<?php

namespace Dotsquares\ImportExport\Block\Adminhtml\Index;

class Index extends \Magento\Backend\Block\Widget\Container
{

	protected $_backendUrl;

    public function __construct(
	\Magento\Backend\Model\UrlInterface $backendUrl,
	\Magento\Backend\Block\Widget\Context $context,array $data = [])
    {
        $this->_backendUrl = $backendUrl;
		parent::__construct($context, $data);
    }

	public function getPostUrl()
    {
		$url = $this->_backendUrl->getUrl("importexport/index/index/export/1");
		return $url;
    }
	
	public function getImportUrl()
    {
		$url = $this->_backendUrl->getUrl("importexport/index/importcat/type/category");
		return $url;
    }


}
