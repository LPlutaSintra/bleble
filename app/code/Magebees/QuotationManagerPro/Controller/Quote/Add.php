<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Add extends \Magento\Framework\App\Action\Action
{
    
    protected $productRepository;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magebees\QuotationManagerPro\Model\CustomerQuote $customerQuote,
        ProductRepositoryInterface $productRepository,
		\Magebees\QuotationManagerPro\Model\Session $quoteSession
    ) {
        $this->productRepository = $productRepository;		
        $this->customerQuote = $customerQuote;
		$this->_quoteSession = $quoteSession;
		$this->_scopeConfig = $scopeConfig;
        parent::__construct(
            $context           
        );	
    }

	protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
    /**
     * Add product to shopping cart action
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
      	$params = $this->getRequest()->getParams(); 	
		try
		{
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get(
                        \Magento\Framework\Locale\ResolverInterface::class
                    )->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');           
           	$this->customerQuote->addProduct($product, $params);
			$this->customerQuote->save();
			 if (!$this->customerQuote->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your quotation.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
                }
			return $this->goBack();
		}
		catch (\Magento\Framework\Exception\LocalizedException $e) {
            if ($this->_quoteSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }

            $url = $this->_quoteSession->getRedirectUrl(true);

            if (!$url) {
               
                $url = $this->_redirect->getRedirectUrl($this->_url->getUrl('quotation/quote'));
            }

            return $this->goBack($url);
        } catch (\Exception $e) {
		//	print_r($e->getMessage());die;
            $this->messageManager->addException($e, __('We can\'t add this item to your quotation right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }          
    
	}
	  protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {         
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;	           
        }
        $result = [];
       if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode($result)
        );
    }
	 protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }
		  $shouldRedirectToCart = $this->_scopeConfig->getValue(
            'quotation/frontendsetting/enable_redirect_quote',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($shouldRedirectToCart) {           
            return $this->_url->getUrl('quotation/quote');
        }
        return $defaultUrl;
    }
    
}
