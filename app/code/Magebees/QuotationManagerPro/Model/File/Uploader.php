<?php
namespace Magebees\QuotationManagerPro\Model\File;

class Uploader extends \Magebees\QuotationManagerPro\Helper\File\Upload
{
  
    protected $_qskipDbProcessing = false;
    protected $_qcoreFileStorage = null;
    protected $_qcoreFileStorageDb = null;
    protected $_validator;
    public function __construct(
        $fileId,
        $files,
        \Magento\MediaStorage\Helper\File\Storage\Database $qcoreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $qcoreFileStorage,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator
    ) {
		
        $this->_qcoreFileStorageDb = $qcoreFileStorageDb;
        $this->_qcoreFileStorage = $qcoreFileStorage;
        $this->_validator = $validator;
        parent::__construct($fileId,$files);
    }

    protected function _afterSave($fresult)
    {
        if (empty($fresult['path']) || empty($fresult['file'])) {
            return $this;
        }

        if ($this->_qcoreFileStorage->isInternalStorage() || $this->skipFileDbProcessing()) {
            return $this;
        }

        $this->_result['file'] = $this->_qcoreFileStorageDb->saveUploadedFile($fresult);

        return $this;
    }

    public function skipFileDbProcessing($flag = null)
    {
        if ($flag === null) {
            return $this->_qskipDbProcessing;
        }
        $this->_qskipDbProcessing = (bool)$flag;
        return $this;
    }

    public function checkAllowedExtension($extension)
    {
        //validate with protected file types
        if (!$this->_validator->isValid($extension)) {
            return false;
        }

        return parent::checkAllowedExtension($extension);
    }

    public function getFileSize()
    {
        return $this->_file['size'];
    }

    public function validateFile()
    {
        $this->_validateFile();
        return $this->_file;
    }
}
