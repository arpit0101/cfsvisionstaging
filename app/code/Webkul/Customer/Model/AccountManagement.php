<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Webkul\Customer\Model;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
    /**
     * {@inheritdoc}
     */
    /*public function authenticate($username, $password)
    {
        $this->checkPasswordStrength($password);

        try {
            $customer = $this->customerRepository->get($username);
        } catch (NoSuchEntityException $e) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        $hash = $this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash();
        if (!$this->encryptor->validateHash($password, $hash)) {
            throw new InvalidEmailOrPasswordException(__('Invalid login or password.'));
        }

        if ($customer->getConfirmation() && $this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__('This account is not confirmed.'));
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection =$objectManager->create('Webkul\Marketplace\Model\Verifymobile')->getCollection();
        $collections = $collection->addFieldToFilter('customer_id', $customer->getId());
        if(count($collections)>=1){
            foreach($collections as $value){
                if($value->getStatus()==0){
                    throw new EmailNotConfirmedException(__('This account is not confirmed by Otp.'));
                }
            }
        }

        $customerModel = $this->customerFactory->create()->updateData($customer);
        $this->eventManager->dispatch(
            'customer_customer_authenticated',
            ['model' => $customerModel, 'password' => $password]
        );

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }*/

}
