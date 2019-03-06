<?php

namespace Wstechlab\B2cRegister\Controller\Rewrite\Account;

use Wstechlab\BaseRegister\Controller\Account\CreatePostAbstract;

class CreatePost extends CreatePostAbstract
{
    /**
     * Add required data to customer
     *
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return \Magento\Customer\Model\Data\Customer
     */
    protected function addRequiredData($request, $customer)
    {
        $customer->setCustomAttribute('fiscal_code', $request->getParam("fiscal-code"));
        $b2cGroupId = $this->scopeConfig->getValue(
            'b2cRegister/general/normalVatB2c',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $customer->setGroupId($b2cGroupId);
        
        return $customer;
    }

    /**
     * Dispatch register success events
     *
     * @param @param \Magento\Customer\Model\Data\Customer $customer
    */
    protected function dispatchRegisterSuccessEvents($customer)
    {
        $this->_eventManager->dispatch(
            'B2c_customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );
        $this->_eventManager->dispatch(
            'customer_register_success',
            ['account_controller' => $this, 'customer' => $customer]
        );
    }

    /**
     * Make data validation
     *
     * @var \Magento\Framework\App\Request\Http $request
     * @return bool
    */
    protected function validateDataFromRequest($request)
    {
        foreach ($request->getParams() as $key => $value) {
            $value = $this->escaper->escapeHtml($value);
            $request->setParam($key, $value);
        }

        $isValid = true;
        $notEmpty = new \Zend_Validate_NotEmpty();
        $isEmail = new \Zend_Validate_EmailAddress();

        $firstName = $request->getParam('firstname');
        $lastName = $request->getParam('lastname');
        //$fiscalCode = $request->getParam('fiscal-code');
        $email = $request->getParam('email');
        $password = $request->getParam('password');
        $isTerms = $request->getParam("is_terms");

        if (!$notEmpty->isValid($firstName)) {
            $isValid = false;
            $this->messageManager->addError(__('First name cannot be empty'));
        }

        if (!$notEmpty->isValid($lastName)) {
            $isValid = false;
            $this->messageManager->addError(__('Last name cannot be empty'));
        }

        if (!$notEmpty->isValid($email)) {
            $isValid = false;
            $this->messageManager->addError(__('Email cannot be empty'));
        }

        if (!$isEmail->isValid($email)) {
            $isValid = false;
            $this->messageManager->addError(__('Email not valid'));
        }

        if (!$notEmpty->isValid($password)) {
            $isValid = false;
            $this->messageManager->addError(__('Password cannot be empty'));
        }

        if (strlen($password) < 8) {
            $isValid = false;
            $this->messageManager->addError(__('Password must be equal or bigger than 8 chars.'));
        }

//        if (!$notEmpty->isValid($fiscalCode)) {
//            $isValid = false;
//            $this->messageManager->addError(__('Fiscal code cannot be empty'));
//        }

        if (!$notEmpty->isValid($isTerms)) {
            $isValid = false;
            $this->messageManager->addError(__('You must accept terms of service'));
        }

        return $isValid;
    }

    /**
     * Get success message
     *
     * @return string
    */
    protected function getSuccessMessage()
    {
        return __('Thank you for registering');
    }

    /**
     * Get redirect after registration url
     *
     * @return string
    */
    protected function getRedirectAfterRegistrationUrl()
    {
        $vatFacilitationSelfCertification = $this->getRequest()->getParam('vat-facilitation-self-certification');
        if ($vatFacilitationSelfCertification) {

            return $this->urlModel->getUrl("facilitated/customer/");
        }

        return parent::getRedirectAfterRegistrationUrl();
    }
}