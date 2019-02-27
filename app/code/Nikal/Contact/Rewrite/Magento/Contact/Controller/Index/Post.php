<?php


namespace Nikal\Contact\Rewrite\Magento\Contact\Controller\Index;

use Magento\Contact\Model\ConfigInterface;
use Magento\Contact\Model\MailInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\DataObject;

class Post extends \Magento\Contact\Controller\Index\Post
{

    private $dataPersistor;
    private $mail;
    private $logger;

    public function __construct(
        Context $context,
        ConfigInterface $contactsConfig,
        MailInterface $mail,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger
    )
    {
        parent::__construct($context, $contactsConfig, $mail, $dataPersistor, $logger);
        $this->mail = $mail;
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        try {
            $this->sendEmail($this->validatedParams());
            $this->messageManager->addSuccessMessage(
                __('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.')
            );
            $this->dataPersistor->clear('contact_us');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage(
                __('An error occurred while processing your form. Please try again later.')
            );
            $this->dataPersistor->set('contact_us', $this->getRequest()->getParams());
        }
        return $this->resultRedirectFactory->create()->setPath('contact/index');
    }

    private function sendEmail($post)
    {
        $this->mail->send($post['email'], ['data' => new DataObject($post)]);
    }

    private function validatedParams()
    {
        $request = $this->getRequest();
        if (false === \strpos($request->getParam('email'), '@')) {
            throw new LocalizedException(__('Invalid email address'));
        }
        if (trim($request->getParam('comment')) === '') {
            throw new LocalizedException(__('Message is missing'));
        }
        if (trim($request->getParam('policy')) === '') {
            throw new LocalizedException(__('You didn\'t accept the Return Policy regulations'));
        }
        if (trim($request->getParam('hideit')) !== '') {
            throw new \Exception();
        }

        return $request->getParams();
    }
}