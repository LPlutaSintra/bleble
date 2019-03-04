<?php
namespace Magebees\QuotationManagerPro\Controller\Download;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\Controller\Result\ForwardFactory;

class CustomOption extends \Magento\Framework\App\Action\Action
{
   
    protected $qresultForwardFactory;
    protected $download;
   
    public function __construct(
        Context $context,
        ForwardFactory $qresultForwardFactory,
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
        \Magento\Sales\Model\Download $qdownload
    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $qresultForwardFactory;
        $this->qdownload = $qdownload;   
		 $this->quoteHelper = $quoteHelper;
    }

    /**
     * Custom options download action    
     */
    public function execute()
    {
        $qItemOptionId = $this->getRequest()->getParam('id');       
        $qoption = $this->_objectManager->create(            \Magebees\QuotationManagerPro\Model\Quote\Item\Option::class
        )->load($qItemOptionId);       
        $qresultForward = $this->resultForwardFactory->create();

        if (!$qoption->getId()) {
            return $qresultForward->forward('noroute');
        }

        $qoptionId = null;
        if (strpos($qoption->getCode(), AbstractType::OPTION_PREFIX) === 0) {
            $qoptionId = str_replace(AbstractType::OPTION_PREFIX, '', $qoption->getCode());
            if ((int)$qoptionId != $qoptionId) {
                $qoptionId = null;
            }
        }
        $productOption = null;
        if ($qoptionId) {           
            $productOption = $this->_objectManager->create(
                \Magento\Catalog\Model\Product\Option::class
            )->load($qoptionId);
        }

        if (!$productOption) {
        //if (!$productOption || !$productOption->getId() || $productOption->getType() != 'file') {
            return $qresultForward->forward('noroute');
        }

        try {           
            $info = $this->quoteHelper->getUnserializeData($qoption->getValue());
            if ($this->getRequest()->getParam('key') != $info['secret_key']) {
                return $qresultForward->forward('noroute');
            }
            $this->qdownload->downloadFile($info);
        } catch (\Exception $e) {
            return $qresultForward->forward('noroute');
        }
        $this->endExecute();
    }
    
    protected function endExecute()
    {
       // exit(0);
		return;
    }
}
