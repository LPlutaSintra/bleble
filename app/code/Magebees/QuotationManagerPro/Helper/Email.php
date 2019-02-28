<?php
namespace Magebees\QuotationManagerPro\Helper;
use Magento\Framework\Filesystem;

class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
   
	 public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,        
		\Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,		 
		   \Magento\Framework\Message\ManagerInterface $messageManager,
		   \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		   \Magebees\QuotationManagerPro\Model\Mail\Template\TransportBuilder $transportBuilder,
		   Filesystem $fileSystem,
		  \Magento\Cms\Model\Template\FilterProvider $filterProvider,
		  \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
		  \Magento\Framework\Filesystem\Driver\File $reader
		 
    ) {
        
        $this->_storeManager = $storeManager;  	
		$this->inlineTranslation = $inlineTranslation;
		$this->_transportBuilder = $transportBuilder;
		$this->quoteHelper = $quoteHelper;
		$this->messageManager = $messageManager;	
		   $this->filterProvider = $filterProvider;
		  $this->fileSystem = $fileSystem;
		 $this->reader = $reader;
		 $this->_localeDate = $localeDate;
        parent::__construct($context);
    }
   	public function sendQuoteCreateMail($quote_id)
	{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$assign_admin_id=$quote->getAssignTo();
		$assign_admin_data=$this->quoteHelper->getUserInfoById($assign_admin_id);
		$owner_email=$this->quoteHelper->getSenderMail();
		$owner_name = $this->quoteHelper->getSenderName();
		$update_time=$this->_localeDate->formatDate(
     $quote->getUpdatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
			$create_time=$this->_localeDate->formatDate(
     $quote->getCreatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
		$quote_view_url=$this->quoteHelper->getQuoteViewUrl($quote_id);
		$customer_id=$quote->getCustomerId();
		 $subject = 'New Quote Create';    
		if($customer_id)
		{
			
		$customer=$this->quoteHelper->loadCustomerById($customer_id);
        $customer_email = $customer->getEmail();           
        $customer_name = $customer->getFirstName()." ".$customer->getLastName();
		$template = 'magebees_email_new_quote_template';
		}
		else
		{
			$quote_customer=$this->quoteHelper->getQuoteCustomer($quote_id);		
			$customer_email=$quote_customer['email'];
			$customer_name = $quote_customer['fname']." ".$quote_customer['lname'];
		$template = 'magebees_email_new_quote_guest_template';
		}
		$quote_status=$this->quoteHelper->getStatus($quote->getStatus());
	
		$this->inlineTranslation->suspend();
		
		$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);

if($avail_shipping['is_default_address']!=0)
{	
		$shipping_address=$this->quoteHelper->renderCustomAddress($avail_shipping);	
}
else
{
	//$shipping_address=$this->quoteHelper->getFormattedAddress($quote->getshipAddressId());
	$default_shipping=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'shipping',$quote->getshipAddressId());
	if($default_shipping)
	{
	$shipping_address=$this->quoteHelper->renderCustomAddress($default_shipping);
	}
	else
	{
		$shipping_address='';
	}
	
}
		
$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);
if($avail_billing['is_default_address']!=0)
{	
		$billing_address=$this->quoteHelper->renderCustomAddress($avail_billing);	
}
else
{
	//$billing_address= $this->quoteHelper->getFormattedAddress($quote->getbillAddressId());
	$default_billing=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'billing',$quote->getbillAddressId());
	if($default_billing)
	{
	$billing_address=$this->quoteHelper->renderCustomAddress($default_billing);
	}
	else
	{
		$billing_address='';
	}
}
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $template
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars(
                 [				 
				  'quote' => $quote,          
				   'store' => $this->_storeManager->getStore(),			
					'formattedShippingAddress' =>$shipping_address,
					'formattedBillingAddress' =>$billing_address,
				 'customer_name'=>$customer_name,
				 'quote_status'=>$quote_status,
				 'updated_at'=>$update_time,
				 'created_at'=>$create_time,
				 'quote_view_url'=>$quote_view_url
                 ]
             )->setFrom(
                 [
                    'email' =>  $owner_email,
                    'name' => $owner_name
                 ]
             )->addTo(
              $customer_email,
			  $customer_name                
            );
				
			// send mail to assigned admin of quote
			$this->_transportBuilder->addTo($assign_admin_data->getData('email'),
										   $assign_admin_data->getData('firstname'));
				
         
            $email_copy_to=$this->quoteHelper->getEmailCopyTo();
			if($email_copy_to)
			{
				foreach($email_copy_to as $id => $bcc_email):				
				$this->_transportBuilder->addCc($bcc_email);
				endforeach;
			}				
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
		
	}
	public function sendQuoteProposalMail($quote_id,$pdf_name,$pdf)
	{
			$version=$this->quoteHelper->getMagentoVersion();
		$mediaPath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();
		$config=$this->quoteHelper->getConfig();
		$allow_customer_checkout=$config['allow_customer_checkout'];
		$enable_expiration_time=$config['enable_expiration_time'];
		if($allow_customer_checkout)
		{
		$guest_checkout_url=$this->quoteHelper->getGuestCheckoutUrl($quote_id);
		}
		else
		{
		$guest_checkout_url=$this->quoteHelper->getQuoteViewLoginUrl($quote_id);
		}
        $pdfFile = $mediaPath.'quotation/pdf/'.$pdf_name;
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote_view_url=$this->quoteHelper->getQuoteViewUrl($quote_id);
		$assign_admin_id=$quote->getAssignTo();
		$assign_admin_data=$this->quoteHelper->getUserInfoById($assign_admin_id);
		$owner_email=$this->quoteHelper->getSenderMail();
		  $owner_name = $this->quoteHelper->getSenderName();
		$update_time=$this->_localeDate->formatDate(
     $quote->getUpdatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
		$expire_time=$this->_localeDate->formatDate(
     $quote->getExpiredAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
		$customer_id=$quote->getCustomerId();
		if($customer_id)
		{
			$is_guest=false;
		$customer=$this->quoteHelper->loadCustomerById($customer_id);
        $customer_email = $customer->getEmail();          
        $customer_name = $customer->getFirstName()." ".$customer->getLastName();
		}
		else
		{
			$is_guest=true;
			$quote_customer=$this->quoteHelper->getQuoteCustomer($quote_id);		
			$customer_email=$quote_customer['email'];
			$customer_name = $quote_customer['fname']." ".$quote_customer['lname'];
		}
		$subject = 'Quote Proposal';     
		$template = 'magebees_email_quote_proposal_template';
		$this->inlineTranslation->suspend();
		$avail_shipping=$this->quoteHelper->IsCustomShipAddressAvail($quote_id);

if($avail_shipping['is_default_address']!=0)
{	
		$shipping_address=$this->quoteHelper->renderCustomAddress($avail_shipping);	
}
else
{
	//$shipping_address=$this->quoteHelper->getFormattedAddress($quote->getshipAddressId());
	$default_shipping=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'shipping',$quote->getshipAddressId());
	if($default_shipping)
	{
	$shipping_address=$this->quoteHelper->renderCustomAddress($default_shipping);
	}
	else
	{
		$shipping_address='';
	}
}
		
$avail_billing=$this->quoteHelper->IsCustomBillAddressAvail($quote_id);
if($avail_billing['is_default_address']!=0)
{	
		$billing_address=$this->quoteHelper->renderCustomAddress($avail_billing);	
}
else
{
	//$billing_address= $this->quoteHelper->getFormattedAddress($quote->getbillAddressId());
	$default_billing=$this->quoteHelper->getDefaultAddressInQuote($quote_id,'billing',$quote->getbillAddressId());
	if($default_billing)
	{
	$billing_address=$this->quoteHelper->renderCustomAddress($default_billing);
	}
	else
	{
		$billing_address='';
	}
}
        try {
		
           $this->_transportBuilder
             ->setTemplateIdentifier(
                 $template
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars(
                 [				 
				  'quote' => $quote,          
				  'is_guest' => $is_guest,          
				  'allow_customer_checkout' => $allow_customer_checkout=='1'?true:false,          
				  'customer_id' => $customer_id?$customer_id:null, 
				   'store' => $this->_storeManager->getStore(),						'guestCheckoutLink'=>$guest_checkout_url,
					'formattedShippingAddress' =>$shipping_address,
					'formattedBillingAddress' =>$billing_address,
				 'customer_name'=>$customer_name,
				 'enable_expiration_time'=>$enable_expiration_time=='1'?true:false,
				 
				  'quote_view_url'=>$quote_view_url,
				  'updated_at'=>$update_time,
				  'expired_at'=>$expire_time
                 ]
             )->setFrom(
                 [
                    'email' =>  $owner_email,
                    'name' => $owner_name
                 ]
             )
            ->addTo(
              $customer_email,
			  $customer_name                
            );		
			// send mail to assigned admin of quote
			$this->_transportBuilder->addTo($assign_admin_data->getData('email'),
										   $assign_admin_data->getData('firstname'));
            $email_copy_to=$this->quoteHelper->getEmailCopyTo();
			if($email_copy_to)
			{
				foreach($email_copy_to as $id => $bcc_email):				
				$this->_transportBuilder->addCc($bcc_email);
				endforeach;
			}	
			if(version_compare($version, '2.3.0', '<')):
			$this->_transportBuilder->addAttachment($pdf->render(),$pdf_name);
			endif;
			
		//**Start for add attachment and fix issue in magento 2.3*/
            $transport = $this->_transportBuilder->getTransport();
			
			if(version_compare($version, '2.3.0', '>=')):
			$html=$transport->getMessage()->getBody()->generateMessage();			
			$bodyMessage = new \Zend\Mime\Part($html);
			$bodyMessage->type = 'text/html';
			$attachment=$this->_transportBuilder->addAttachmentUpdated($pdf->render(),$pdf_name);		
			$bodyPart = new \Zend\Mime\Message();
    		$bodyPart->setParts(array($bodyMessage,$attachment));
			$transport->getMessage()->setBody($bodyPart);
			endif;
			//**End for add attachment and fix issue in magento 2.3*/
			
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
		
	}
	public function sendNotifyMail($quote_id,$post_files,$editor_text,$notify_to)
	{
		
		$mediaPath = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->getAbsolutePath();
	$reply=$this->filterProvider->getBlockFilter()->filter($editor_text);
    	$version=$this->quoteHelper->getMagentoVersion();	
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$quote_view_url=$this->quoteHelper->getQuoteViewUrl($quote_id);
		$assign_admin_id=$quote->getAssignTo();
		$assign_admin_data=$this->quoteHelper->getUserInfoById($assign_admin_id);
		$owner_email=$this->quoteHelper->getSenderMail();
		$owner_name = $this->quoteHelper->getSenderName();
		$quote_status=$this->quoteHelper->getStatus($quote->getStatus());
		$update_time=$this->_localeDate->formatDate(
     $quote->getUpdatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
		$customer_id=$quote->getCustomerId();
		if($customer_id)
		{
		$customer=$this->quoteHelper->loadCustomerById($customer_id);
        $customer_email = $customer->getEmail();               
        $customer_name = $customer->getFirstName()." ".$customer->getLastName();
		}
		else
		{
			$quote_customer=$this->quoteHelper->getQuoteCustomer($quote_id);		
			$customer_email=$quote_customer['email'];
			$customer_name = $quote_customer['fname']." ".$quote_customer['lname'];
		}
		$subject = 'Quote Admin Notify'; 
		if($notify_to=='admin')
		{
			$sender_mail=$customer_email;
			$sender_name=$customer_name;
			$recipient_mail=$owner_email;
			$recipient_name=$owner_name;
			$template = 'magebees_email_admin_notify_template';
		}
		else
		{
			$sender_mail=$owner_email;
			$sender_name=$owner_name;
			$recipient_mail=$customer_email;
			$recipient_name=$customer_name;
		$template = 'magebees_email_customer_notify_template';
		}
		$this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $template
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars(
                 [				 
				  'quote' => $quote,          
				   'store' => $this->_storeManager->getStore(),					
				 'customer_name'=>$customer_name,
				 'owner_name'=>$owner_name,				 
				  'updated_at'=>$update_time,
				  'quote_status'=>$quote_status,
				 'quote_view_url'=>$quote_view_url,
				  'editor_text'=>$reply
				 
                 ]
             )->setFrom(['email' =>  $sender_mail,'name' => $sender_name])->addTo($recipient_mail,$recipient_name);
			// send mail to assigned admin of quote
			$this->_transportBuilder->addTo($assign_admin_data->getData('email'),
			 $assign_admin_data->getData('firstname'));
			if(!empty($post_files))
			{$file=[];
			$attachment_arr=[];
				
				foreach($post_files as $files)
				{
				  $file_path=$mediaPath.'quotation'.$files['path'];
				
				if(version_compare($version, '2.3.0', '<')):
			$attachment=$this->_transportBuilder->addAttachment($this->reader->fileGetContents($file_path),$files['name']);
				else:
					$attachment=$this->_transportBuilder->addAttachmentUpdated($this->reader->fileGetContents($file_path),$files['name']);
			endif;
					
			
					$attachment_arr[]=$attachment;
				}
			}
			$email_copy_to=$this->quoteHelper->getEmailCopyTo();
			if($email_copy_to)
			{
				foreach($email_copy_to as $id => $bcc_email):				
				$this->_transportBuilder->addCc($bcc_email);
				endforeach;
			}	
	
            $transport = $this->_transportBuilder->getTransport();
			$version=$this->quoteHelper->getMagentoVersion();	
			if(version_compare($version, '2.3.0', '>=')):
			$html=$transport->getMessage()->getBody()->generateMessage();			
			$bodyMessage = new \Zend\Mime\Part($html);
			$bodyMessage->type = 'text/html';				
			$bodyPart = new \Zend\Mime\Message();
			array_unshift($attachment_arr,$bodyMessage);			
    		$bodyPart->setParts($attachment_arr);	    		
			$transport->getMessage()->setBody($bodyPart);	
			endif;
            $transport->sendMessage();
            $this->inlineTranslation->resume();
			$this->messageManager->addSuccess(__('Message has been sent.')); 
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
	}
	public function sendProposalStatusMail($quote_id,$proposal_status,$new_quote_inc_id=null,$new_quote_id=null,$is_edited=null)
	{
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
		$config=$this->quoteHelper->getConfig();
		$allow_customer_checkout=$config['allow_customer_checkout'];
		if($allow_customer_checkout)
		{
		$guest_checkout_url=$this->quoteHelper->getGuestCheckoutUrl($quote_id);
		}
		else
		{
		$guest_checkout_url=$this->quoteHelper->getQuoteViewLoginUrl($quote_id);
		}
		$quote_view_url=$this->quoteHelper->getQuoteViewUrl($quote_id);		
		if($new_quote_id)
		{
		$new_quote_view_url=$this->quoteHelper->getQuoteViewUrl($new_quote_id);	
		}
		else
		{
			$new_quote_view_url=null;
		}
		$assign_admin_id=$quote->getAssignTo();
		$assign_admin_data=$this->quoteHelper->getUserInfoById($assign_admin_id);
		$owner_email=$this->quoteHelper->getSenderMail();
		$owner_name = $this->quoteHelper->getSenderName();
		$update_time=$this->_localeDate->formatDate(
     $quote->getUpdatedAt(),
    \IntlDateFormatter::MEDIUM,
    true
);
		$customer_id=$quote->getCustomerId();
		if($customer_id)
		{
		$is_guest=false;
		$customer=$this->quoteHelper->loadCustomerById($customer_id);
        $customer_email = $customer->getEmail();             
        $customer_name = $customer->getFirstName()." ".$customer->getLastName();
		}
		else
		{
			$is_guest=true;
			$quote_customer=$this->quoteHelper->getQuoteCustomer($quote_id);		
			$customer_email=$quote_customer['email'];
			$customer_name = $quote_customer['fname']." ".$quote_customer['lname'];
		}
		
		$quote_status=$this->quoteHelper->getStatus($quote->getStatus());
		if($proposal_status=='accepted')
		{
			 $subject = 'Proposal Accepted';  
				$template = 'magebees_email_proposal_accepted_template';
		}
		elseif($proposal_status=='cancelled')
		{ 
			$subject = 'Proposal Cancelled';  
			$template = 'magebees_email_proposal_cancelled_template';
		}
		else
		{
			$subject = 'Proposal Rejected';  
			$template = 'magebees_email_proposal_rejected_template';
		}
	
		$this->inlineTranslation->suspend();
        try {
            $this->_transportBuilder
             ->setTemplateIdentifier(
                 $template
             )->setTemplateOptions(
                 [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                 ]
             )->setTemplateVars(
                 [				 
				  'quote' => $quote,      
				 'quoteId'=>$quote->getIncrementId(),
				   'store' => $this->_storeManager->getStore(),
				 'customer_name'=>$customer_name,
				 'is_guest'=>$is_guest,
			'allow_customer_checkout' => $allow_customer_checkout=='1'?true:false,
			'new_quote_inc_id' => $new_quote_inc_id!=null?$new_quote_inc_id:null,
			'new_quote_view_url' => $new_quote_view_url!=null?$new_quote_view_url:null,
			'is_edited' => $is_edited!=null?$is_edited:null,
				 'customer_id'=>$customer_id?$customer_id:null,
				 'guestCheckoutLink'=>$guest_checkout_url,
				  'owner_name'=>$owner_name,
				 'quote_status'=>$quote_status,
				 'quoteAccessLink'=>$quote_view_url,
				 'updated_at'=>$update_time
                 ]
             )->setFrom(
                 [
                    'email' =>  $owner_email,
                    'name' => $owner_name
                 ]
             )
            ->addTo(
              $customer_email,
			  $customer_name                
            );
			// send mail to assigned admin of quote
			$this->_transportBuilder->addTo($assign_admin_data->getData('email'),
										   $assign_admin_data->getData('firstname'));
            $email_copy_to=$this->quoteHelper->getEmailCopyTo();
			if($email_copy_to)
			{
				foreach($email_copy_to as $id => $bcc_email):				
				$this->_transportBuilder->addCc($bcc_email);
				endforeach;
			}	
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
            $this->messageManager->addError(
                __($e->getMessage())
            );
        }
	}
	
	
}
