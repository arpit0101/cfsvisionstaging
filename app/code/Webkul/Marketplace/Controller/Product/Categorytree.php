<?php
/**
 * Marketplace Product Categorytree controller.
 *
 */
namespace Webkul\Marketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Categorytree extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $category_model = $this->_objectManager->create('Magento\Catalog\Model\Category');
            $category = $category_model->load($data["CID"]);
            $children = $category->getChildren();
            $all = explode(",",$children);$result_tree = "";$ml = $data["ML"]+20;$count = 1;$total = count($all);
            $plus = 0;
            
            foreach($all as $each){
                $count++;
                $_category = $category_model->load($each);
                if(count($category_model->getResource()->getAllChildren($category_model->load($each)))-1 > 0){
                    $result[$plus]['counting']=1;           
                }else{
                    $result[$plus]['counting']=0;
                }
                $result[$plus]['id']= $_category['entity_id'];
                $result[$plus]['name']= $_category->getName();
                $categories = array();
                $data_cats = '';
                if(isset($data["CATS"])){
                    $categories = explode(",",$data["CATS"]);
                    $data_cats = $data["CATS"];
                }
                if($data_cats && in_array($_category["entity_id"],$categories)){
                    $result[$plus]['check']= 1;
                }else{
                    $result[$plus]['check']= 0;
                }
                $plus++;
            }
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
            );
        } catch (\Exception $e) {
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode('')
            );
        }
    }
}