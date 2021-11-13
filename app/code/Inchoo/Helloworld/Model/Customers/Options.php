<?php namespace Inchoo\Helloworld\Model\Customers;

class Options implements \Magento\Framework\Option\ArrayInterface
{
    public function __construct(
	    \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory,
		\Magento\Customer\Model\CustomerFactory $customerFactory
	){
		$this->_customerFactory = $customerFactory;
        $this->_groupCollectionFactory = $groupCollectionFactory;
    }

    public function toOptionArray()
    {
		$customers = $this->getCustomers();
		$options = [];
		$i = 0;
		foreach($customers as $customer){
			$options[$i]['label'] = $customer->getName().'('.$customer->getEmail().')';
			$options[$i]['value'] = $customer->getEmail();
			$i++;
		}
		return $options;
    }
	
	public function getCustomers(){
		return $this->_customerFactory->create()->getCollection()->addAttributeToSelect("*")->load();
	}
}