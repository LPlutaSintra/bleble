<?php
namespace Magebees\QuotationManagerPro\CustomerData;
use Magento\Customer\CustomerData\SectionSourceInterface;

class QuoteDirectoryData implements SectionSourceInterface
{
   
    protected $directoryHelper;
    public function __construct(\Magento\Directory\Helper\Data $directoryHelper)
    {
        $this->directoryHelper = $directoryHelper;
    }

    public function getSectionData()
    {
        $qoutput = [];
        $qregionsData = $this->directoryHelper->getRegionData();
      
        foreach ($this->directoryHelper->getCountryCollection() as $sectioncode => $data) {
            $qoutput[$sectioncode]['name'] = $data->getName();
            if (array_key_exists($sectioncode, $qregionsData)) {
                foreach ($qregionsData[$sectioncode] as $key => $region) {
                    $qoutput[$sectioncode]['regions'][$key]['code'] = $region['code'];
                    $qoutput[$sectioncode]['regions'][$key]['name'] = $region['name'];
                }
            }
        }
        return $qoutput;
    }
}
