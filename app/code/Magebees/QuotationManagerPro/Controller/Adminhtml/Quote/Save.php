<?php

namespace Magebees\QuotationManagerPro\Controller\Adminhtml\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class Save extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		 \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
			\Magebees\QuotationManagerPro\Helper\Email $emailHelper,
			\Magebees\QuotationManagerPro\Helper\Admin $backendHelper,
		\Magebees\QuotationManagerPro\Model\QuoteFilesFactory $quoteFilesFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteItemFactory $quoteItemFactory,
		   \Magebees\QuotationManagerPro\Model\QuoteMessageFactory $quoteMessageFactory,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
		\Magento\Framework\Stdlib\DateTime\DateTime $datetime
    ) {
    
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		 $this->quoteHelper = $quoteHelper;
		 $this->emailHelper = $emailHelper;
		  $this->backendHelper = $backendHelper;
		  $this->_quoteFilesFactory = $quoteFilesFactory;
		  $this->_quoteMessageFactory = $quoteMessageFactory;
		$this->_quoteItemFactory = $quoteItemFactory;  
		$this->datetime = $datetime;
		$this->timezone = $timezone;
    }
    public function execute()
    {
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);    
		$resultRedirect->setUrl($this->_redirect->getRefererUrl());
       	$data = $this->getRequest()->getPost()->toarray();
	//	print_r($data);die;
		$email_config=$this->quoteHelper->getEmailConfig();		
		if(isset($data['quote_req_info'] ))
		{
		foreach($data['quote_req_info'] as $key=>$quote_req_info)
		{			
			/*if(!empty($quote_req_info['request_info']))
			{*/
				$request_info=$quote_req_info['request_info'];
				$item_id=$key;			
				$quoteitem=$this->_quoteItemFactory->create()->load($item_id,'id');
				$quoteitem->setRequestInfo($request_info);
				$quoteitem->save();    
			//}
		}
		}
		$quote_id=$data['quote_id'];		
		$assign_to=$data['admin_assign'];
		//$assign_to=implode(',',$data['admin_assign']);
		$newStatus=$data['quote_status'];
		$quote_request_info=$data['quote_request_info'];
		$quote=$this->quoteHelper->loadQuoteById($quote_id);
	
		if($newStatus==Status::PROPOSAL_SENT){
					$this->_forward('converToProposal');
					return;
				}
		
			if($newStatus!=$quote->getStatus()){
						$statusLabel = $this->quoteHelper->getAllStatus();
						$reply = 'Status Change From <strong>'.$statusLabel[$quote->getStatus()].'</strong> To <strong>'.$statusLabel[$newStatus].'</strong>';
				$is_customer_notify=0;

				if($newStatus==Status::PROPOSAL_ACCEPTED)
				{
					if($email_config['proposal_accept'])
					{
						$this->emailHelper->sendProposalStatusMail($quote_id,'accepted');
					}	
					$is_customer_notify=1;
				}
				if($newStatus==Status::PROPOSAL_REJECTED)
				{
					if($email_config['proposal_reject'])
					{
					$this->emailHelper->sendProposalStatusMail($quote_id,'rejected');
					}
					$is_customer_notify=1;
				}
				if($newStatus==Status::PROPOSAL_CANCELLED)
				{
					if($email_config['proposal_cancel'])
					{
						$this->emailHelper->sendProposalStatusMail($quote_id,'cancelled');
					}
					$is_customer_notify=1;
				}
				$this->addSystemComment($quote_id,$newStatus,$reply,$is_customer_notify);
			}
			$quote->setAssignTo($assign_to);
			$quote->setStatus($newStatus);		
			$quote->setQuoteRequestInfo($quote_request_info);
		if(isset($data['expiry_date']))
		{			
			
		//	$expiry_date = $this->timezone->date($data['expiry_date'])->format('Y-m-d H:i:s');
			//print_r($expiry_date);die;
			$quote->setExpiredAt($data['expiry_date']);		
		}
			$quote->save();	


			 //for upload multiple files
            $uploaded_files = [];
            $post_files = [];
            $files = $this->getRequest()->getFiles();
			$files_arr = $this->getRequest()->getFiles()->toArray();
			if(!empty($files_arr)){				
					
				$files_count = count($files_arr['quote_file']);
				if(array_key_exists('quote_file',$files_arr) && $files_arr['quote_file'][0]['name']){
					for($i=0;$i<$files_count;$i++){
						$post=[];
						$fileId = "quote_file[".$i."]";
						try {
							$uploader = $this->_objectManager->create('Magebees\QuotationManagerPro\Model\File\Uploader', ['fileId' => $fileId,'files'=>$files_arr]);
							$file_upload_config = $this->quoteHelper->getFileUploadConfig();
							$allowed_ext=$file_upload_config['file_type'];
							$allowed_ext_array = [];
							$allowed_ext_array = explode(',', $allowed_ext);
							$uploader->setAllowedExtensions($allowed_ext_array);
							$uploader->setAllowRenameFiles(true);
							$uploader->setFilesDispersion(true);
							$mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
								->getDirectoryRead(DirectoryList::MEDIA);
							$result = $uploader->save($mediaDirectory->getAbsolutePath('quotation/'));
							unset($result['tmp_name']);
							unset($result['path']);
							$file_name=$result['name'];
							$file_path=$result['file'];		
							$quote_files=$this->_quoteFilesFactory->create();
							$quote_files->setQuoteId($quote_id);
							$quote_files->setFileName($file_name);
							$quote_files->setFilePath($file_path);
							$quote_files->save();
							
						} catch (\Exception $e) {
							$this->messageManager->addError(__($e->getMessage()));
							return $resultRedirect;
						}
					}
				}
				
			} 
		$this->messageManager->addSuccess(__('The Quote has been saved.'));
		  $this->_redirect('*/*/view', ['quote_id' => $quote_id, '_current' => true]);
		
		
    }

	public function addSystemComment($quote_id,$newStatus,$reply,$is_customer_notify){
		
			$datetime = $this->datetime->gmtDate();
			$quote = $this->quoteHelper->loadQuoteById($quote_id);
		try{
				$admin_id=$this->backendHelper->getCurrentUser()->getData('user_id');
			if($this->quoteHelper->getQuoteMessages($quote_id))
			{
				$quote_message=$this->_quoteMessageFactory->create()->load($quote_id,'quote_id');	
				$message_data=$this->quoteHelper->getQuoteMessages($quote_id);	
				$currentMessageId=$message_data['message_id'];
				$encoded_communication=$message_data['communication'];
				$decoded_communication=$this->quoteHelper->getUnserializeData($encoded_communication);
				$message_detail=$decoded_communication['messages'];							$message_detail[$datetime]=array('is_admin'=>'1','quoteStatus'=>$newStatus,'is_customer_notify'=>$is_customer_notify,'display_at_front'=>'0','message'=>$reply,'admin_id'=>$admin_id);
				$unserialize_communication['messages']=$message_detail;				
				$serialize_msg_detail=$this->quoteHelper->getSerializeData($unserialize_communication);
				$quote_message->setCommunication($serialize_msg_detail);
			}
			else
			{
					$message_detail=[];
					$message_arr=[];						$message_arr[$datetime]=array('is_admin'=>'1','quoteStatus'=>$newStatus,'is_customer_notify'=>$is_customer_notify,'display_at_front'=>'0','message'=>$reply,'admin_id'=>$admin_id);					
					$customer_id=$quote->getCustomerId();
					//$admin_id=$quote->getAssignTo();
				
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
	
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebees_QuotationManagerPro::quotation');
    }
}
