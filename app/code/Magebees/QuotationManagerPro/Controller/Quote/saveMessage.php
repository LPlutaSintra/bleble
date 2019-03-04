<?php
namespace Magebees\QuotationManagerPro\Controller\Quote;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magebees\QuotationManagerPro\Model\Quote\Status;

class saveMessage extends \Magento\Framework\App\Action\Action
{ 
	  public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		  \Magebees\QuotationManagerPro\Model\QuoteMessageFactory $quoteMessageFactory	,
		  \Magebees\QuotationManagerPro\Model\QuoteFilesFactory $quoteFilesFactory,
		   \Magebees\QuotationManagerPro\Helper\Quotation $quoteHelper,
		   \Magebees\QuotationManagerPro\Helper\Email $emailHelper,
			\Magento\Cms\Model\Template\FilterProvider $filterProvider,
		   \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
    
        	parent::__construct($context);
       		$this->_quoteMessageFactory = $quoteMessageFactory;
		   	$this->_quoteFilesFactory = $quoteFilesFactory;
		   	$this->filterProvider = $filterProvider;
		  	$this->quoteHelper = $quoteHelper;
		  	$this->emailHelper = $emailHelper;
		  	$this->date = $date;
    }
    public function execute()
    {
		 $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $result = [];
        	$params = $this->getRequest()->getParams();	
		$filedata=$this->getRequest()->getFiles()->toArray();
			$date = $this->date->gmtDate();
			$quote_id=$params['quote_id'];
			if(isset($params['quote_id']))
			{			
			$quote_id=$params['quote_id'];
			$quote = $this->quoteHelper->loadQuoteById($quote_id);
		
			 //for upload multiple files
            $uploaded_files = [];
            $post_files = [];
            $files = $this->getRequest()->getFiles();
			$files_arr = $this->getRequest()->getFiles()->toArray();
			if(!empty($files_arr)){
				$files_count = count($files_arr['upload_file']);
				if(array_key_exists('upload_file',$files_arr) && $files_arr['upload_file'][0]['name']){
					for($i=0;$i<$files_count;$i++){
						$post=[];
						$fileId = "upload_file[".$i."]";
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
							$post['name']=$result['name'];
							$post['path']=$result['file'];							
							$post_files[] = $post;
							$quote_files=$this->_quoteFilesFactory->create();
							$quote_files->setQuoteId($quote_id);
							$quote_files->setFileName($result['name']);
							$quote_files->setFilePath($result['file']);
							$quote_files->save();
							//$post_files['file_name'][] = $result['name'];
						} catch (\Exception $e) {
							$this->messageManager->addError(__($e->getMessage()));
							return $resultRedirect;
						}
					}
				}
				$serialize_post_files=$this->quoteHelper->getSerializeData($post_files);
				if(!empty($post_files))
				{
				$sub_detail_arr['files']=$serialize_post_files;
				}
			} 				
				
				// new status from message sent
				$currentQuoteStatus=$quote->getStatus();				
				if($currentQuoteStatus < Status::STARTING){
				$newStatus = Status::STARTING;
			}else if(($currentQuoteStatus >= Status::STARTING)&&($currentQuoteStatus < Status::PROCESSING)){
				$newStatus = Status::STARTING_ACTION_STORE_OWNER;
				
			}else if(($currentQuoteStatus >= Status::PROCESSING)&&($currentQuoteStatus < Status::PROPOSAL_CREATED)){
				
				$newStatus = Status::PROCESSING_ACTION_STORE_OWNER;
			}else if(($currentQuoteStatus >= Status::PROPOSAL_CREATED)&&($currentQuoteStatus < Status::PROPOSAL_SENT)){
				
				$newStatus = Status::PROCESSING_ACTION_STORE_OWNER;
				
			}else if(($currentQuoteStatus >= Status::PROPOSAL_SENT)&&($currentQuoteStatus < Status::PROPOSAL_CANCELLED)){
				
				$newStatus = Status::PROPOSAL_SENT_ACTION_STORE_OWNER;
			}else if(($currentQuoteStatus == Status::PROPOSAL_CANCELLED)||($currentQuoteStatus < Status::PROPOSAL_CANCELLED_OUTOF_STOCK)){
				$newStatus = $currentQuoteStatus;
			}else if($currentQuoteStatus == Status::PROPOSAL_REJECTED){
				$newStatus = $currentQuoteStatus;
			}else if($currentQuoteStatus == Status::PROPOSAL_ACCEPTED){
				$newStatus = $currentQuoteStatus;
			}else if($currentQuoteStatus == Status::PROPOSAL_ORDERED){
				$newStatus = $currentQuoteStatus;
			}else if($currentQuoteStatus == Status::PROPOSAL_PRINTED){
				$newStatus = $currentQuoteStatus;
			}else if($currentQuoteStatus == Status::PROPOSAL_CANCELLED_OUTOF_STOCK){
				$newStatus = $currentQuoteStatus;
			}
				
				
				// new message conversation save
			$quote_message=$this->_quoteMessageFactory->create()->load($quote_id,'quote_id');			
			$sub_detail_arr=[];
			$sub_detail_arr['is_frontend']=1;
			$sub_detail_arr['display_at_front']=1;
			$sub_detail_arr['quoteStatus']=$newStatus;
			if($params['editor_text'])
			{
			$editor_text=$params['editor_text'];
			$sub_detail_arr['message']=$editor_text;
			}
			else
			{
				$editor_text=null;
			}
				
				if(($editor_text!='')||(!empty($post_files)))
				{
				if($quote_message->getData())
				{
				
					$communication=$quote_message->getCommunication();
					$unserialize_communication=$this->quoteHelper->getUnserializeData($communication);
					$message_detail=$unserialize_communication['messages'];					
					$message_detail[$date]=$sub_detail_arr;				
					$unserialize_communication['messages']=$message_detail;			
					$serialize_msg_detail=$this->quoteHelper->getSerializeData($unserialize_communication);
					$quote_message->setCommunication($serialize_msg_detail);				
				}
				else
				{	
					$message_detail=[];
					$message_arr=[];					
					$message_arr[$date]=$sub_detail_arr;					
					$customer_id=$quote->getCustomerId();
					$admin_id=$quote->getAssignTo();
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
				$quote->setStatus($newStatus);
				$quote->save();
				$email_config=$this->quoteHelper->getEmailConfig();
				if($email_config['notify_admin'])
				{
				$this->emailHelper->sendNotifyMail($quote_id,$post_files,$editor_text,'admin');
				}
				//$this->messageManager->addSuccess(__('Message has been sent.'));     
				}
				else
				{
					$this->messageManager->addError(__('Please Enter Message'));
							return $resultRedirect;
				}
				
			}
			return $resultRedirect;	
    }
}
