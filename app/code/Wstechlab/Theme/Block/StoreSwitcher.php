<?php

namespace Wstechlab\Theme\Block;

use \Magento\Checkout\Model\Session as CheckoutSession;

class StoreSwitcher extends \Magento\Framework\View\Element\Template {

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    private $countriesArray = null;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;
    protected $checkoutSession;
    protected $_coreSession;
    
    
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;


    /**
     * StoreSwitcher constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CheckoutSession $checkoutSession
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, 
            CheckoutSession $checkoutSession, 
            \Magento\Framework\Data\Helper\PostHelper $postDataHelper, 
            \Magento\Store\Model\StoreManagerInterface $storeManager, 
            \Magento\Framework\ObjectManagerInterface $objectManager, 
            \Magento\Framework\Session\SessionManagerInterface $coreSession, 
            \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Customer\Model\Session $customerSession,
            array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_postDataHelper = $postDataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->cookieManager = $cookieManager;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    protected function _construct() {
        $this->countriesArray = $this->prepareCountriesArray();
    }

    /**
     * Get list of allowed countries for all stores
     * @return array
     */
    public function getCountriesForAllStores() {

        $options = [];
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website) {

            // exclude b2b from store switcher
            if ($website->getCode()=='website_b2b') continue;



            $url = null;
            $storeObj = null;
            foreach ($website->getStores() as $store) {
                if ($store->isActive()) {
                    $storeObj = $this->storeManager->getStore($store);
                    break;
                }
            }
            if (empty($storeObj)) {
                continue;
            }
            $countries = $this->scopeConfig->getValue('general/country/allow', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE, $website->getId());
            if (empty($countries)) {
                continue;
            }
            $countries = explode(',', $countries);
            ksort($countries);
            foreach ($countries as $country) {
                $url = $this->getTargetStorePostData($storeObj, array('ssc_h_dd' => $country));
                if (empty($url)) {
                    continue;
                }
                $countryName = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($country)->getName();
                $options[$countryName] = array('code' => $country, 'url' => $url, 'label' => $countryName);
            }
        }
        ksort($options);
        return $options;
    }

    /**
     * Get name of country by code
     * @return string
     */
    public function getCountryNameByCode($code) {
        $name = $code;

        if (!empty($name) and isset($this->countriesArray[$code])) {
            $name = $this->countriesArray[$code];
        }

        return $name;
    }

    /**
     * prepare array of all countries
     * @return array
     */
    public function prepareCountriesArray() {
        $countries = [];

        $countryHelper = $this->_objectManager->get('Magento\Directory\Model\Config\Source\Country');
        $countryList = $countryHelper->toOptionArray();

        if (!empty($countryList)) {
            foreach ($countryList as $country) {
                $countries[$country['value']] = $country['label'];
            }
        }

        return $countries;
    }

    /**
     * Returns target store post data
     *
     * @param \Magento\Store\Model\Store $store
     * @param array $data
     * @return string
     */
    public function getTargetStorePostData(\Magento\Store\Model\Store $store, $data = []) {
        //$data[\Magento\Store\Api\StoreResolverInterface::PARAM_NAME] = $store->getCode();
        //We need to fromStore as true because it will enable proper URL
        //rewriting during store switching.
        return $this->_postDataHelper->getPostData(
                        $store->getBaseUrl(), $data
        );
    }

    /**
     * @return string
     */
    public function getCurrentShippingCode() {
        $country = null;
        $shipping = $this->checkoutSession->getQuote()->getShippingAddress();

        $shippCountryParamSession = $this->getCookie('ssc_h_dd');
        if (!empty($shippCountryParamSession)) {
            $country = $shippCountryParamSession;
        }

        if (!empty($country) and ! empty($shipping)) {
            $shipping->setCountryId($country)->save();
        }

        if (empty($country)) {
            $country = $this->getCountryFromLocale();
        }

        if (empty($country)) {
            $country = 'IT';
        }

        return $country;
    }

    /**
     * Get store name
     *
     * @return null|string
     */
    public function getCurrentShippingCountry() {
        $country = null;
        $code = $this->getCurrentShippingCode();
        if (!empty($code)) {
            $countryName = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($code);
            if (!empty($countryName)) {
                $country = $countryName->getName();
            }
        }
        if (empty($country)) {
            $country = __('Italy');
        }
        return $country;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId() {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @return string
     */
    public function getCountryFromLocale() {
        $country = null;
        $resolver = $this->_objectManager->get('Magento\Framework\Locale\Resolver');
        if (!empty($resolver)) {
            $locale = $resolver->getLocale();
            $lang = strstr($locale, '_', true);
            $code = strtoupper($lang);
            if (!empty($code) and isset($this->countriesArray[$code])) {
                $country = $code;
            }
        }

        return $country;
    }
    
    
     public function getCookie($key) {
        if ($cookieResult = $this->cookieManager->getCookie($key)) {
            return $cookieResult;
        } else {
            return false;
        }
    }

}
