<?php
	function saveCustomerAddress(){
		
		echo 123;die; 
		$addresss = $objectManager->get('\Magento\Customer\Model\AddressFactory');

		$address = $addresss->create();
		
		$address->setCustomerId($customer->getId())
		
		->setFirstname(FirstName)
		
		->setLastname(LastName)
		
		->setCountryId(VN)
		
		->setPostcode(10000)
		
		->setCity(HaNoi)
		
		->setTelephone(1234567890)
		
		->setFax(123456789)
		
		->setCompany(Company)
		
		->setStreet(Street)
		
		->setIsDefaultBilling('1')
		
		->setIsDefaultShipping('1')
		
		->setSaveInAddressBook('1')
		
		$address->save();
	}
?>