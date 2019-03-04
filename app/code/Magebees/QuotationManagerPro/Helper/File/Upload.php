<?php

namespace Magebees\QuotationManagerPro\Helper\File;
use Magento\Framework\File\Mime;

class Upload
{
  
    protected $_file;
    protected $_fileMimeType;
    protected $_quploadType;
    protected $_quploadedFileName;
    protected $_uploadedFileDir;
    protected $_qallowCreateFolders = true;
    protected $_qallowRenameFiles = false;
    protected $_qenableFilesDispersion = false;
    protected $_qcaseInsensitiveFilenames = true;
    protected $_qdispretionPath = null;
    protected $_qfileExists = false;
    protected $_qallowedExtensions = null;
    protected $_qvalidateCallbacks = [];
    private $fileMime;
    const SINGLE_STYLE = 0;
    const MULTIPLE_STYLE = 1;
    const TMP_NAME_EMPTY = 666;    
    const MAX_IMAGE_WIDTH = 4096;
    const MAX_IMAGE_HEIGHT = 2160;
    protected $_result;
    public function __construct(
        $fileId,
		$files,
        Mime $fileMime = null
    ) {
		
        $this->_setUploadQuoteFileId($fileId,$files);
        if (!file_exists($this->_file['tmp_name'])) {
            $code = empty($this->_file['tmp_name']) ? self::TMP_NAME_EMPTY : 0;
            throw new \Exception('The file was not uploaded.', $code);
        } else {
            $this->_qfileExists = true;
        }
        $this->fileMime = $fileMime ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Mime::class);
    }
	 protected function _afterSave($result)
    {
        return $this;
    }

    /**
     * Used to save uploaded file into destination folder with original or new file name (if specified).
     *
     * @param string $destinationFolder
     * @param string $newFileName
     * @return array
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function save($destinationFolder, $newFileName = null)
    {
        $this->_validateFile();
        $this->validateDestination($destinationFolder);

        $this->_result = false;
        $destinationFile = $destinationFolder;
        $fileName = isset($newFileName) ? $newFileName : $this->_file['name'];
        $fileName = static::getCorrectFileName($fileName);
        if ($this->_qenableFilesDispersion) {
            $fileName = $this->correctFileNameCase($fileName);
            $this->setAllowCreateFolders(true);
            $this->_qdispretionPath = static::getDispersionPath($fileName);
            $destinationFile .= $this->_qdispretionPath;
            $this->_createDestinationFolder($destinationFile);
        }

        if ($this->_qallowRenameFiles) {
            $fileName = static::getNewFileName(
                static::_addDirSeparator($destinationFile) . $fileName
            );
        }

        $destinationFile = static::_addDirSeparator($destinationFile) . $fileName;

        try {
            $this->_result = $this->_moveFile($this->_file['tmp_name'], $destinationFile);
        } catch (\Exception $e) {
            // if the file exists and we had an exception continue anyway
            if (file_exists($destinationFile)) {
                $this->_result = true;
            } else {
                throw $e;
            }
        }

        if ($this->_result) {
            if ($this->_qenableFilesDispersion) {
                $fileName = str_replace('\\', '/', self::_addDirSeparator($this->_qdispretionPath)) . $fileName;
            }
            $this->_quploadedFileName = $fileName;
            $this->_uploadedFileDir = $destinationFolder;
            $this->_result = $this->_file;
            $this->_result['path'] = $destinationFolder;
            $this->_result['file'] = $fileName;

            $this->_afterSave($this->_result);
        }

        return $this->_result;
    }

    /**
     * Validates destination directory to be writable
     *
     * @param string $destinationFolder
     * @return void
     * @throws \Exception
     */
    private function validateDestination($destinationFolder)
    {
        if ($this->_qallowCreateFolders) {
            $this->_createDestinationFolder($destinationFolder);
        }

        if (!is_writable($destinationFolder)) {
            throw new \Exception('Destination folder is not writable or does not exists.');
        }
    }

    /**
     * Set access permissions to file.
     *
     * @param string $file
     * @return void
     *
     * @deprecated 100.0.8
     */
    protected function chmod($file)
    {
        chmod($file, 0777);
    }

    /**
     * Move files from TMP folder into destination folder
     *
     * @param string $tmpPath
     * @param string $destPath
     * @return bool|void
     */
    protected function _moveFile($tmpPath, $destPath)
    {
        if (is_uploaded_file($tmpPath)) {
            return move_uploaded_file($tmpPath, $destPath);
        } elseif (is_file($tmpPath)) {
            return rename($tmpPath, $destPath);
        }
    }

    /**
     * Validate file before save
     *
     * @return void
     * @throws \Exception
     */
    protected function _validateFile()
    {
        if ($this->_qfileExists === false) {
            return;
        }

        //is file extension allowed
        if (!$this->checkAllowedExtension($this->getFileExtension())) {
            throw new \Exception('Disallowed file type.');
        }
        //run validate callbacks
        foreach ($this->_qvalidateCallbacks as $params) {
            if (is_object($params['object'])
                && method_exists($params['object'], $params['method'])
                && is_callable([$params['object'], $params['method']])
            ) {
                $params['object']->{$params['method']}($this->_file['tmp_name']);
            }
        }
    }

    /**
     * Returns extension of the uploaded file
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->_qfileExists ? pathinfo($this->_file['name'], PATHINFO_EXTENSION) : '';
    }

    /**
     * Add validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @param object $callbackObject
     * @param string $callbackMethod    Method name of $callbackObject. It must
     *                                  have interface (string $tmpFilePath)
     * @return \Magento\Framework\File\Uploader
     */
    public function addValidateCallback($callbackName, $callbackObject, $callbackMethod)
    {
        $this->_qvalidateCallbacks[$callbackName] = ['object' => $callbackObject, 'method' => $callbackMethod];
        return $this;
    }

    /**
     * Delete validation callback model for us in self::_validateFile()
     *
     * @param string $callbackName
     * @access public
     * @return \Magento\Framework\File\Uploader
     */
    public function removeValidateCallback($callbackName)
    {
        if (isset($this->_qvalidateCallbacks[$callbackName])) {
            unset($this->_qvalidateCallbacks[$callbackName]);
        }
        return $this;
    }

    /**
     * Correct filename with special chars and spaces
     *
     * @param string $fileName
     * @return string
     */
    public static function getCorrectFileName($fileName)
    {
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);
        $fileInfo = pathinfo($fileName);

        if (preg_match('/^_+$/', $fileInfo['filename'])) {
            $fileName = 'file.' . $fileInfo['extension'];
        }
        return $fileName;
    }

    /**
     * Convert filename to lowercase in case of case-insensitive file names
     *
     * @param string $fileName
     * @return string
     */
    public function correctFileNameCase($fileName)
    {
        if ($this->_qcaseInsensitiveFilenames) {
            return strtolower($fileName);
        }
        return $fileName;
    }

    /**
     * Add directory separator
     *
     * @param string $dir
     * @return string
     */
    protected static function _addDirSeparator($filedir)
    {
        if (substr($filedir, -1) != '/') {
            $filedir .= '/';
        }
        return $filedir;
    }

    /**
     * Used to check if uploaded file mime type is valid or not
     *
     * @param string[] $validTypes
     * @access public
     * @return bool
     */
    public function checkMimeType($filevalidTypes = [])
    {
        if (count($filevalidTypes) > 0) {
            if (!in_array($this->_getMimeType(), $filevalidTypes)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns a name of uploaded file
     *
     * @access public
     * @return string
     */
    public function getQUploadedFileName()
    {
        return $this->_quploadedFileName;
    }

    /**
     * Used to set {@link _qallowCreateFolders} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setAllowCreateFolders($flag)
    {
        $this->_qallowCreateFolders = $flag;
        return $this;
    }

    /**
     * Used to set {@link _qallowRenameFiles} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setAllowRenameFiles($flag)
    {
        $this->_qallowRenameFiles = $flag;
        return $this;
    }

    /**
     * Used to set {@link _qenableFilesDispersion} value
     *
     * @param bool $flag
     * @access public
     * @return $this
     */
    public function setFilesDispersion($flag)
    {
        $this->_qenableFilesDispersion = $flag;
        return $this;
    }

    /**
     * File names Case-sensitivity setter
     *
     * @param bool $flag
     * @return $this
     */
    public function setFilenamesCaseSensitivity($flag)
    {
        $this->_qcaseInsensitiveFilenames = $flag;
        return $this;
    }

    /**
     * Set allowed extensions
     *
     * @param string[] $extensions
     * @return $this
     */
    public function setAllowedExtensions($extensions = [])
    {
        foreach ((array)$extensions as $extension) {
            $this->_qallowedExtensions[] = strtolower($extension);
        }
        return $this;
    }

    /**
     * Check if specified extension is allowed
     *
     * @param string $extension
     * @return boolean
     */
    public function checkAllowedExtension($extension)
    {
        if (!is_array($this->_qallowedExtensions) || empty($this->_qallowedExtensions)) {
            return true;
        }

        return in_array(strtolower($extension), $this->_qallowedExtensions);
    }

    /**
     * Return file mime type
     *
     * @return string
     */
    private function _getMimeType()
    {
        return $this->fileMime->getMimeType($this->_file['tmp_name']);
    }

    /**
     * Set upload field id
     *
     * @param string|array $fileId
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function _setUploadQuoteFileId($fileId,$files)
    {
		
        if (is_array($fileId)) {
            $this->_quploadType = self::MULTIPLE_STYLE;
            $this->_file = $fileId;
        } else {
            if (empty($files)) {
                throw new \Exception('$_FILES array is empty');
            }

            preg_match("/^(.*?)\[(.*?)\]$/", $fileId, $file);
            if (is_array($file)) {
                array_shift($file);
                $this->_quploadType = self::MULTIPLE_STYLE;
				$file1=$file[0];
				$file2=$file[1];
				//print_r($file);die;
                $fileAttributes = $files[$file1][$file2];
				//print_r($fileAttributes);	
                $tmpVar = [];

                foreach ($fileAttributes as $attributeName => $attributeValue) {
                    $tmpVar[$attributeName] = $fileAttributes[$attributeName];					
                }

                $fileAttributes = $tmpVar;				
                $this->_file = $fileAttributes;
            } elseif (!empty($fileId) && isset($files[$fileId])) {
                $this->_quploadType = self::SINGLE_STYLE;
                $this->_file = $files[$fileId];
            } elseif ($fileId == '') {
                throw new \Exception('Invalid parameter given. A valid $_FILES[] identifier is expected.');
            }
        }
    }
    /**
     * Create destination folder
     *
     * @param string $destinationFolder
     * @return \Magento\Framework\File\Uploader
     * @throws \Exception
     */
    private function _createDestinationFolder($fdestinationFolder)
    {
        if (!$fdestinationFolder) {
            return $this;
        }

        if (substr($fdestinationFolder, -1) == '/') {
            $fdestinationFolder = substr($fdestinationFolder, 0, -1);
        }

        if (!(@is_dir($fdestinationFolder)
            || @mkdir($fdestinationFolder, 0777, true)
        )) {
            throw new \Exception("Unable to create directory '{$fdestinationFolder}'.");
        }
        return $this;
    }

    /**
     * Get new file name if the same is already exists
     *
     * @param string $destinationFile
     * @return string
     */
    public static function getNewFileName($fdestinationFile)
    {
        $qfileInfo = pathinfo($fdestinationFile);
        if (file_exists($fdestinationFile)) {
            $index = 1;
            $baseName = $qfileInfo['filename'] . '.' . $qfileInfo['extension'];
            while (file_exists($qfileInfo['dirname'] . '/' . $baseName)) {
                $baseName = $qfileInfo['filename'] . '_' . $index . '.' . $qfileInfo['extension'];
                $index++;
            }
            $destFileName = $baseName;
        } else {
            return $qfileInfo['basename'];
        }

        return $destFileName;
    }

    /**
     * Get dispertion path
     *
     * @param string $fileName
     * @return string
     * @deprecated 101.0.4
     */
    public static function getQDispretionPath($fileName)
    {
        return self::getDispersionPath($fileName);
    }

    /**
     * Get dispertion path
     *
     * @param string $fileName
     * @return string
     * @since 101.0.4
     */
    public static function getDispersionPath($fileName)
    {
        $char = 0;
        $dispertionPath = '';
        while ($char < 2 && $char < strlen($fileName)) {
            if (empty($dispertionPath)) {
                $dispertionPath = '/' . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            } else {
                $dispertionPath = self::_addDirSeparator(
                    $dispertionPath
                ) . ('.' == $fileName[$char] ? '_' : $fileName[$char]);
            }
            $char++;
        }
        return $dispertionPath;
    }
	   
 


   
}
