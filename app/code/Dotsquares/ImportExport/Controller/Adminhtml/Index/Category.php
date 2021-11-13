<?php
namespace Dotsquares\ImportExport\Controller\Adminhtml\Index;

class Category extends \Magento\Backend\App\Action
{
	protected $dataHelper;
	protected $jsonHelper;
	protected $catModel;
	protected $categoryFactory;

	public function __construct(\Magento\Backend\App\Action\Context $context,
	\Dotsquares\ImportExport\Helper\Data $dataHelper,
	\Magento\Framework\Json\Helper\Data $jsonHelper,
	\Magento\Catalog\Model\Category $catModel,
	\Magento\Catalog\Model\CategoryFactory $categoryFactory
	)
	{ 
		$this->dataHelper = $dataHelper;
		$this->jsonHelper = $jsonHelper;
		$this->catModel = $catModel;
		$this->categoryFactory = $categoryFactory;
		parent::__construct($context);	
    }
	
	public function execute() {
		if ($this->getRequest()->isPost()) {
			$count = $this->getRequest()->getPost('count',0);
			$result['count'] = $count;
			$error = '';
			$includeId =  $this->getRequest()->getParam('cat_id');
			try {
				$attributes = $this->dataHelper->getAttributes($includeId);
	
				$categoryPath = $this->getRequest()->getPost('path');
				$categoryName = $this->getRequest()->getPost('name');
				$catid = $this->dataHelper->getCategoryIdFromPath($categoryPath, $categoryName);
				if (is_array($catid)) {
					$error = __('Path provided is not a valid one.');
				} else {
					//$category = $this->catModel;
					$category = $this->categoryFactory->create();
					if ($catid > 0){
					$category->load($catid);
					$category->setData('store_id', 0); //saved admin store id for update the row
					}
					foreach($attributes as $attribute) {
						$fieldvalue = $this->getRequest()->getPost($attribute['field']);
						if (isset($attribute['function'])) {
							//echo $attribute['function'];
							//die;
							$functionName = $attribute['function'];
							$fieldvalue = $this->dataHelper->$functionName($fieldvalue);
						}
						if (isset($attribute['required'])) {
							if (strtolower($attribute['required']) == 'yes') {
								if (!$fieldvalue) {
									$error = sprintf(__('Please provide the value for "%s"'), $attribute['field']);
									break;
								}
							}
						}
						if (isset($attribute['importfn'])) {
							$category->$attribute['importfn']($attribute['field'], $fieldvalue);
						} else {
							if($attribute['field'] != "entity_id"){
							$category->setData($attribute['field'], $fieldvalue);
							}
						}
					}
				}

				if ($error) {
					$result['error'] = $error;
				} else {
					/*echo "<pre>";
					print_r($category->getData());
					die;*/
					$category->save();
					$result['message'] = sprintf(__('Imported the category "%s" successfully.'), $category->getName());
				}
			} catch (\Exception $e) {
				$result['error'] = $e->getMessage();
			}
		} else {
			$result['error'] = __('Invalid request');
		}
		$this->getResponse()->setBody($this->jsonHelper->jsonEncode($result));
	}

}