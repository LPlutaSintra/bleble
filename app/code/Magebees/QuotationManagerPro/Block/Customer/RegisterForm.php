<?php
namespace Magebees\QuotationManagerPro\Block\Customer;

use Magento\Customer\Model\AccountManagement;

class RegisterForm extends \Magento\Directory\Block\Data
{
    
    protected $_qcustomerSession;
    protected $_qmoduleManager;
    protected $_qcustomerUrl;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $qdirectoryHelper,
        \Magento\Framework\Json\EncoderInterface $qjsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $qconfigCacheType,        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $qregionCollectionFactory,        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $qcountryCollectionFactory,
        \Magento\Framework\Module\Manager $qmoduleManager,
        \Magento\Customer\Model\Session $qcustomerSession,
        \Magento\Customer\Model\Url $qcustomerUrl,
        array $data = []
    ) {
        $this->_qcustomerUrl = $qcustomerUrl;
        $this->_qmoduleManager = $qmoduleManager;
        $this->_qcustomerSession = $qcustomerSession;
        parent::__construct(
            $context,
            $qdirectoryHelper,
            $qjsonEncoder,
            $qconfigCacheType,
            $qregionCollectionFactory,
            $qcountryCollectionFactory,
            $data
        );
        $this->_isScopePrivate = false;
    }

    
    public function getConfig($qpath)
    {
        return $this->_scopeConfig->getValue($qpath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }   
    protected function _prepareLayout()
    {      
        return parent::_prepareLayout();
    }

    public function getPostActionUrl()
    {
        return $this->_qcustomerUrl->getRegisterPostUrl();
    }
	  public function getLoginPostUrl()
    {
        return $this->_qcustomerUrl->getLoginPostUrl();
    }

    public function getBackUrl()
    {
        $qurl = $this->getData('back_url');
        if ($qurl === null) {
            $qurl = $this->_qcustomerUrl->getLoginUrl();
        }
        return $qurl;
    }
   
    public function getFormData()
    {
        $qdata = $this->getData('form_data');
        if ($qdata === null) {
            $qformData = $this->_qcustomerSession->getCustomerFormData(true);
            $qdata = new \Magento\Framework\DataObject();
            if ($qformData) {
                $qdata->addData($qformData);
                $qdata->setCustomerData(1);
            }
            if (isset($qdata['region_id'])) {
                $qdata['region_id'] = (int)$qdata['region_id'];
            }
            $this->setData('form_data', $qdata);
        }
        return $qdata;
    }

    public function getCountryId()
    {
        $qcountryId = $this->getFormData()->getCountryId();
        if ($qcountryId) {
            return $qcountryId;
        }
        return parent::getCountryId();
    }

    public function getRegion()
    {
        if (null !== ($qregion = $this->getFormData()->getRegion())) {
            return $qregion;
        } elseif (null !== ($qregion = $this->getFormData()->getRegionId())) {
            return $qregion;
        }
        return null;
    }
    public function restoreSessionData(\Magento\Customer\Model\Metadata\Form $qform, $scope = null)
    {
        if ($this->getFormData()->getCustomerData()) {
            $qrequest = $form->prepareRequest($this->getFormData()->getData());
            $qdata = $form->extractData($qrequest, $scope, false);
            $qform->restoreData($qdata);
        }

        return $this;
    }

    public function getMinimumPwdLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }

    public function getRequiredClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }
}
