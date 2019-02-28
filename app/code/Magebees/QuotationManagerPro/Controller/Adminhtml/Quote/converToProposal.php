<?php
namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;
use \Magebees\QuotationManagerPro\Model\Quote\Status;
use Magento\Framework\App\Filesystem\DirectoryList;

class converToProposal extends  \Magento\Backend\App\Action
{ 
	 public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		  \Magebees\QuotationManagerPro\Helper\Admin $backendHelper, \Magebees\QuotationManagerPro\Model\QuoteMessageFactory $quoteMessageFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		  \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
		 \Magebees\QuotationManagerPro\Helper\Email $emailHelper
    ) {
    
        parent::__construct($context);
		  $this->quoteHelper = $quoteHelper;
		  $this->backendHelper = $backendHelper;
		  $this->_quoteMessageFactory = $quoteMessageFactory;
		 $this->emailHelper = $emailHelper;
		 $this->_fileFactory = $fileFactory;
		$this->date = $date;
      
    }
    public function execute()
    {
        	if ($data = $this->getRequest()->getPost()) {
		
			try {
				
				$quote_id= $this->getRequest()->getParam('quote_id');				
				$quote = $this->quoteHelper->loadQuoteById($quote_id);	
				if($quote->getIsBackend())
				{
				$base_currency=$quote->getBaseCurrencyCode();
				$newStatus = Status::PROPOSAL_SENT;
				$quote->setStatus($newStatus);			
				$quote->save();
				$quote_id = $quote->getQuoteId();
				$currentQuoteItems =$this->backendHelper->getItemsCollection($quote_id);				
				$itemInfo= '';
				foreach($currentQuoteItems as $item):
				$defaultReqQtyData=$this->quoteHelper->getDefaultRequestQty($quote_id,$item->getId());
				$requsetedPrice=$defaultReqQtyData['request_qty_price'];
				$price=$this->quoteHelper->getFormatedPrice($requsetedPrice,$base_currency); 
				$requsetedQty=$defaultReqQtyData['request_qty'];
				$itemInfo .= floor($requsetedQty).' X '.$item->getName().' for '.$price.'<br/>';
				endforeach;	
				$itemInfo .= '<br/>';
			
				$reply = $itemInfo;
				$is_customer_notify=1;			
				$this->addSystemComment($quote_id,$newStatus,$reply,$is_customer_notify);
				
				
				
			if(isset($data['RequestedItem']))
			{
					$quoteRequest = $data['RequestedItem'];		
						
						foreach($quoteRequest as $id => $item):
						
						if(isset($item['client_request'])){
						$itemProduct = $this->quoteHelper->loadQuoteItemById($id);
						$itemProduct->setRequestInfo($item['client_request']);	
						$itemProduct->setId($id);
						$itemProduct->Save();
						}
						
						
						endforeach;
			}
			/* Sleep set because need to set comment base on the time so need to set interval in time for
			set both comment.
			*/
			 sleep(2);
			$newStatus = Status::PROPOSAL_SENT_ACTION_CUSTOMER;
			$statusLabel = $this->quoteHelper->getAllStatus();
					$reply =__('Status Change From <strong>'.$statusLabel[$quote->getStatus()].'</strong> To <strong>'.$statusLabel[$newStatus].'</strong>');
				$is_customer_notify=0;
				$this->addSystemComment($quote_id,$newStatus,$reply,$is_customer_notify);				
				$quote->setStatus($newStatus);
				$quote->setQuoteId($quote_id);
				$quote->save();
			
				// generate pdf
				
					$pdf = $this->_objectManager->create(\Magebees\QuotationManagerPro\Model\Pdf\Quote::class)->getPdf([$quote]);
			
				//send mail
				$email_config=$this->quoteHelper->getEmailConfig();
				if($email_config['quote_proposal'])
				{
				$pdf_name='#'.$quote->getIncrementId().'.pdf';
				$response=$this->emailHelper->sendQuoteProposalMail($quote_id,$pdf_name,$pdf);
					if($response)
				   {  $this->messageManager->addSuccess(__('The Proposal has been sent.')); 
					   $this->_redirect('*/*/view', ['quote_id' => $quote_id, '_current' => true]);
                  	 return;
				   }
				}
				}
				else
				{
					$this->messageManager->addNotice(__('Quote Item does not exist for proposal.')); 
				}
			//  $this->messageManager->addSuccess(__('The Proposal has been sent.'));               
               
                    $this->_redirect('*/*/view', ['quote_id' => $quote_id, '_current' => true]);
                  	 return;
			   
				
				
            }catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __($e->getMessage()));
				
            }
        }
		$this->quoteHelper->unsetErrorMsgArr();
        
        $this->_redirect('*/*/');
    }
	public function addSystemComment($quote_id,$newStatus,$reply,$is_customer_notify){
		
			$date = $this->date->gmtDate();
			$quote = $this->quoteHelper->loadQuoteById($quote_id);
		try{
			if($this->quoteHelper->getQuoteMessages($quote_id))
			{
				$quote_message=$this->_quoteMessageFactory->create()->load($quote_id,'quote_id');	
				$message_data=$this->quoteHelper->getQuoteMessages($quote_id);	
				$currentMessageId=$message_data['message_id'];
				$encoded_communication=$message_data['communication'];
				$decoded_communication=$this->quoteHelper->getUnserializeData($encoded_communication);
				$message_detail=$decoded_communication['messages'];							$message_detail[$date]=array('is_admin'=>'1','quoteStatus'=>$newStatus,'is_customer_notify'=>$is_customer_notify,'display_at_front'=>'0','message'=>$reply);
				$unserialize_communication['messages']=$message_detail;				
				$serialize_msg_detail=$this->quoteHelper->getSerializeData($unserialize_communication);
				$quote_message->setCommunication($serialize_msg_detail);
			}
			else
			{
					$message_detail=[];
					$message_arr=[];						$message_arr[$date]=array('is_admin'=>'1','quoteStatus'=>$newStatus,'is_customer_notify'=>$is_customer_notify,'display_at_front'=>'0','message'=>$reply);					
					$customer_id=$quote->getCustomerId();
					//$admin_id=$quote->getAssignTo();
					$admin_id=$this->backendHelper->getCurrentUser()->getData('user_id');
					$message_detail['customer_id']=$customer_id;
					$message_detail['admin_id']=$admin_id;
					$message_detail['quote_id']=$quote_id;
					$message_detail['messages']=$message_arr;
					$serialize_msg_detail=$this->quoteHelper->getSerializeData($message_detail);
					$quote_message=$this->_quoteMessageFactory->create();
					$quote_message->setQuoteId($quote_id);
					$quote_message->setCommunication($serialize_msg_detail);
			}
			$quote_message->save();
			} catch (\Exception $e) { 
           	$this->messageManager->addError(__($e->getMessage()));
							return $resultRedirect;
			}
	}
	
}
