<?php

 
namespace Magebees\QuotationManagerPro\Model\Mail\Template;
 
class TransportBuilder extends \Magento\Framework\Mail\Template\TransportBuilder
{
    
    public function addAttachment($pdfString,$filename=null)
    {
		 if($filename == '') {
           $filename="Price_proposal";
       }
        $this->message->createAttachment(
            $pdfString,
            \Zend_Mime::TYPE_OCTETSTREAM,
            \Zend_Mime::DISPOSITION_ATTACHMENT,
            \Zend_Mime::ENCODING_BASE64,
            $filename
        );	
		return $this;		
			
    }
	 public function addAttachmentUpdated($pdfString,$filename=null)
    {
		 if($filename == '') {
           $filename="Price_proposal";
       }			
			$attachment = new \Zend\Mime\Part($pdfString);
			$attachment->type = \Zend_Mime::TYPE_OCTETSTREAM;
			$attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
			$attachment->encoding = \Zend_Mime::ENCODING_BASE64;
			$attachment->filename = $filename;
        return $attachment;
    }
}