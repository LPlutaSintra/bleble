<?php
namespace Magebees\QuotationManagerPro\Model;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement
{
	private $qdataProcessor;
	   public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $qaddress)
    {
        /** @var \Magento\Quote\Model\Quote $mquote */
        $mquote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
       // if ($mquote->isVirtual() || 0 == $mquote->getItemsCount()) {
		if ($mquote->isVirtual()) {
            return [];
        }
        return $this->getShippingMethods($mquote, $qaddress);
    }
	 private function getShippingMethods(\Magento\Quote\Model\Quote $mquote,$qaddress)
    {
        $qoutput = [];
        $qshippingAddress = $mquote->getShippingAddress();
        $qshippingAddress->addData($this->extractAddressData($qaddress));
        $qshippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($mquote, $qshippingAddress);
        $qshippingRates = $qshippingAddress->getGroupedAllShippingRates();
        foreach ($qshippingRates as $qcarrierRates) {
            foreach ($qcarrierRates as $qrate) {
                $qoutput[] = $this->converter->modelToDataObject($qrate, $mquote->getQuoteCurrencyCode());
            }
        }
        return $qoutput;
    }
	 private function extractAddressData($qaddress)
    {
        $qclassName = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($qaddress instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $qclassName = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($qaddress instanceof \Magento\Quote\Api\Data\EstimateAddressInterface) {
            $qclassName = \Magento\Quote\Api\Data\EstimateAddressInterface::class;
        }
        return $this->getDataProcessor()->buildOutputDataArray(
            $qaddress,
            $qclassName
        );
    }

    private function getDataProcessor()
    {
        if ($this->qdataProcessor === null) {
            $this->qdataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->qdataProcessor;
    }
}