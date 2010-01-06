<?php
/**
 * DooFile class file.
 *
 * @author Leng Sheng Hong <darkredz@gmail.com>
 * @link http://www.doophp.com/
 * @copyright Copyright &copy; 2009-2010 Leng Sheng Hong
 * @license http://www.doophp.com/license
 * @since 1.3
 */
 
/**
 * Provides functions for managing file system
 */
class DooFile {

    const LIST_FILE = 'file';
    const LIST_FOLDER = 'folder';

    public $chmod;

    public function  __construct($chmod=null) {
        $this->chmod = $chmod;
    }

    /**
     * Delete contents in a folder recursively
     * @param string $dir Path of the folder to be deleted
     * @return int Total of deleted files/folders
     */
	public function purgeContent($dir){
        $totalDel = 0;
		$handle = opendir($dir);

		while (false !== ($file = readdir($handle))){
			if ($file != '.' && $file != '..'){
				if (is_dir($dir.$file)){
					$totalDel += $this->purgeContent($dir.$file.'/');
					if( rmdir($dir.$file) )
                        $totalDel++;
				}else{
					if( unlink($dir.$file) )
                        $totalDel++;
				}
			}
		}
		closedir($handle);
        return $totalDel;
	}

    /**
     * Delete a folder (and all files and folders below it)
     * @param string $path Path to folder to be deleted
     * @param bool $deleteSelf true if the folder should be deleted. false if just its contents.
     * @return int|bool Returns the total of deleted files/folder. Returns false if delete failed
     */
	public function delete($path, $deleteSelf=true){

        //delete all sub folder/files under, then delete the folder itself
        if(is_dir($path)){
            if($path[strlen($path)-1] != '/' && $path[strlen($path)-1] != '\\' ){
                $path .= DIRECTORY_SEPARATOR;
                $path = str_replace('\\', '/', $path);
            }
            if($total = $this->purgeContent($path)){
                if($deleteSelf)
                    if($t = rmdir($path))
                        return $total + $t;
                return $total;
            }
            return false;
        }
        else{
            return unlink($path);
        }
    }


	/**
	 * If the folder does not exist creates it (recursively)
	 * @param string $path Path to folder/file to be created
	 * @param mixed $content Content to be written to the file
	 * @param string $writeFileMode Mode to write the file
     * @return bool Returns true if file/folder created
	 */
	public function create($path, $content=null, $writeFileMode='w+') {
        //create file if content not empty
		if (!empty($content)) {
            $path = str_replace('\\', '/', $path);
            $filename = $path;
            $path = explode('/', $path);
            array_splice($path, sizeof($path)-1);

            $path = implode('/', $path);
            if($path[strlen($path)-1] != '/'){
                $path .= '/';
            }

            if(!file_exists($path))
                mkdir($path, $this->chmod, true);
            $fp = fopen($filename, $writeFileMode);
            $rs = fwrite($fp, $content);
            fclose($fp);
            return ($rs>0);
		}else{
            return mkdir($path, $this->chmod, true);
        }
	}

    /**
     * Move/rename a file/folder
     * @param string $from Original path of the folder/file
     * @param string $to Destination path of the folder/file
     * @return bool Returns true if file/folder created
     */
    public function move($from, $to) {
        $path = str_replace('\\', '/', $to);
        $path = explode('/', $path);
        array_splice($path, sizeof($path)-1);

        $path = implode('/', $path);
        if($path[strlen($path)-1] != '/'){
            $path .= '/';
        }

        if(!file_exists($path))
            mkdir($path, $this->chmod, true);
        return rename($from, $to);
    }

    /**
     * Copy a file/folder to a destination
     * @param string $from Original path of the folder/file
     * @param string $to Destination path of the folder/file
     * @return bool|int Returns true if file copied. If $from is a folder, returns the number of files/folders copied
     */
    public function copy($from, $to) {            
        if(is_dir($from)){
            if($to[strlen($to)-1] != '/' && $to[strlen($to)-1] != '\\' ){
                $to .= DIRECTORY_SEPARATOR;
                $to = str_replace('\\', '/', $to);
            }
            if($from[strlen($from)-1] != '/' && $from[strlen($from)-1] != '\\' ){
                $from .= DIRECTORY_SEPARATOR;
                $from = str_replace('\\', '/', $from);
            }
            if(!file_exists($to))
                mkdir($to, $this->chmod, true);

            return $this->copyContent($from, $to);
        }else{
            $path = str_replace('\\', '/', $to);
            $path = explode('/', $path);
            array_splice($path, sizeof($path)-1);

            $path = implode('/', $path);
            if($path[strlen($path)-1] != '/'){
                $path .= '/';
            }

            if(!file_exists($path))
                mkdir($path, $this->chmod, true);
            return copy($from, $to);
        }
    }

    /**
     * Copy contents in a folder recursively
     * @param string $dir Path of the folder to be copied
     * @param string $to Destination path
     * @return int Total of files/folders copied
     */
	public function copyContent($dir, $to){
        $totalCopy = 0;
		$handle = opendir($dir);

		while(false !== ($file = readdir($handle))){
			if($file != '.' && $file != '..'){

                if (is_dir($dir.$file)){
                    if(!file_exists($to.$file))
                        mkdir($to.$file, $this->chmod, true);

					$totalCopy += $this->copyContent($dir.$file.'/', $to.$file.'/');
				}else{
					if( copy($dir.$file, $to.$file) )
                        $totalCopy++;
				}
			}
		}
		closedir($handle);
        return $totalCopy;
	}

    /**
     * Get a list of folders or files or both in a given path.
     *
     * @param string $path Path to get the list of files/folders
     * @param string $listOnly List only files or folders. Use value DooFile::LIST_FILE or DooFile::LIST_FOLDER
     * @return array Returns an assoc array with keys: name(file name), path(full path to file/folder), folder(boolean), extension, type, size(KB)
     */
	public function getList($path, $listOnly=null){
        $path = str_replace('\\', '/', $path);
        if($path[strlen($path)-1] != '/'){
            $path .= '/';
        }

		$filetype = array('.', '..');
		$name = array();

		try{
			$dir = opendir($path);
		}catch (Exception $e){
			return false;
		}

		while( $file = readdir($dir) ){
			if( !in_array(substr($file, -1, strlen($file)), $filetype) && !in_array(substr($file, -2, strlen($file)), $filetype) ){
				$name[] = $path . $file;
			}
		}
		closedir($dir);

		if(count($name)==0)return false;

        if(!function_exists('mime_content_type')) {
            function mime_content_type($filename) {
                $mime_types = array(
                    'txt' => 'text/plain',
                    'htm' => 'text/html',
                    'html' => 'text/html',
                    'php' => 'text/html',
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'json' => 'application/json',
                    'xml' => 'application/xml',
                    'swf' => 'application/x-shockwave-flash',
                    'flv' => 'video/x-flv',
                    'sql' => 'text/x-sql',

                    // images
                    'png' => 'image/png',
                    'jpe' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'bmp' => 'image/bmp',
                    'ico' => 'image/vnd.microsoft.icon',
                    'tiff' => 'image/tiff',
                    'tif' => 'image/tiff',
                    'svg' => 'image/svg+xml',
                    'svgz' => 'image/svg+xml',

                    // archives
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    'exe' => 'application/x-msdownload',
                    'msi' => 'application/x-msdownload',
                    'cab' => 'application/vnd.ms-cab-compressed',

                    // audio/video
                    'mp3' => 'audio/mpeg',
                    'qt' => 'video/quicktime',
                    'mov' => 'video/quicktime',

                    // adobe
                    'pdf' => 'application/pdf',
                    'psd' => 'image/vnd.adobe.photoshop',
                    'ai' => 'application/postscript',
                    'eps' => 'application/postscript',
                    'ps' => 'application/postscript',

                    // ms office
                    'doc' => 'application/msword',
                    'rtf' => 'application/rtf',
                    'xls' => 'application/vnd.ms-excel',
                    'ppt' => 'application/vnd.ms-powerpoint',

                    // open office
                    'odt' => 'application/vnd.oasis.opendocument.text',
                    'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
                );

                $f = explode('.',$filename);
                $ext = strtolower(array_pop($f));
                if (array_key_exists($ext, $mime_types)) {
                    return $mime_types[$ext];
                }
                elseif (function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME);
                    $mimetype = finfo_file($finfo, $filename);
                    finfo_close($finfo);
                    return $mimetype;
                }
                else {
                    return 'application/octet-stream';
                }
            }
        }

		$fileInfo=array();
		foreach($name as $key=>$val){
            if($listOnly==DooFile::LIST_FILE){
                if(is_dir($val)) continue;
            }
			$filename = explode('/', $val);
			$filename = $filename[count($filename)-1];
            $ext = explode('.',$val);

            if(!is_dir($val)){
                $fileInfo[] = array('name' => $filename,
                                    'path' => $val,
                                    'folder' => is_dir($val),
                                    'extension' => strtolower($ext[sizeof($ext)-1]),
                                    'type' => mime_content_type($val),
                                    'size' => round(filesize($val)/1024)
                                );
            }else{
                $fileInfo[] = array('name' => $filename,
                                    'path' => $val,
                                    'folder' => is_dir($val));
            }
		}
		return $fileInfo;
	}


    /**
     * Save the uploaded file(s) in HTTP File Upload variables
     *
     * @param string $uploadPath Path to save the uploaded file(s)
     * @param string $filename The file input field name in $_FILES HTTP File Upload variables
     * @param string $rename Rename the uploaded file (without extension)
     * @return string|array The file name of the uploaded file.
     */
    public function upload($uploadPath, $filename, $rename=''){
        $file = !empty($_FILES[$filename]) ? $_FILES[$filename] : null;
        if($file==Null)return;

        if(is_array($file['name'])===False){
            $pic = strrpos($file['name'], '.');
            $ext = substr($file['name'], $pic+1);

            if ($this->timeAsName){
                $newName = time().'-'.mt_rand(1000,9999) . '.' . $ext;
            }else{
                $newName = $file['name'];
            }

            if($rename=='')
                $filePath = $this->uploadPath . $newName;
            else
                $filePath = $this->uploadPath . $rename . '.' . $ext;

            if (move_uploaded_file($file['tmp_name'], $filePath)){
                return ($rename=='')? $newName : $rename. '.' . $ext;
            }
        }
        else{
            $uploadedPath = array();
            foreach($file['error'] as $k=>$error){
                if(empty($file['name'][$k])) continue;
                if ($error == UPLOAD_ERR_OK) {
                   $pic = strrpos($file['name'][$k], '.');
                   $ext = substr($file['name'][$k], $pic+1);

                   if($this->timeAsName){
                       $newName = time().'-'.mt_rand(1000,9999) . '_' . $k . '.' . $ext;
                   }else{
                       $newName = $file['name'][$k];
                   }

                   if($rename=='')
                       $filePath = $uploadPath . $newName;
                   else
                       $filePath = $uploadPath . $rename . '_' . $k . '.' . $ext;

                   if (move_uploaded_file($file['tmp_name'][$k], $filePath)){
                       $uploadedPath[] = $newName;
                   }
                }else{
                   return false;
                }
            }
            return $uploadedPath;
        }
    }

    /**
     * Get the uploaded files' type
     *
     * @param string $filename The file field name in $_FILES HTTP File Upload variables
     * @return string|array The image format type of the uploaded image.
     */
    public function getUploadFormat($filename){
        if(!empty($_FILES[$filename])){
            $type = $_FILES[$filename]['type'];
            if(is_array($type)===False){
                if(!empty($type)){
                    return $type;
                }
            }
            else{
                $typelist = array();
                foreach($type as $t){
                    $typelist[] = $t;
                }
                return $typelist;
            }
        }
    }

    /**
     * Checks if file mime type of the uploaded file(s) is in the allowed list
     *
     * @param string $filename The file input field name in $_FILES HTTP File Upload variables
     * @param array $allowType Allowed file type.
     * @return bool Returns true if file mime type is in the allowed list.
     */
    public function checkFileType($filename, $allowType){
        $type = $this->getUploadFormat($filename);
        if(is_array($type)===False)
            return in_array($type, $allowType);
        else{
            foreach($type as $t){
                if($t===Null || $t==='') continue;
                if(!in_array($t, $allowType)){
                    return false;
                }
            }
            return true;
        }
    }

    /**
     * Checks if file extension of the uploaded file(s) is in the allowed list.
     *
     * @param string $filename The file input field name in $_FILES HTTP File Upload variables
     * @param array $allowExt Allowed file extensions.
     * @return bool Returns true if file extension is in the allowed list.
     */
    public function checkFileExtension($filename, $allowExt){
        if(!empty($_FILES[$filename])){
            $name = $_FILES[$filename]['name'];
            if(is_array($name)===False){
                $n = strrpos($name, '.');
                $ext = strtolower(substr($name, $n+1));
                return in_array($ext, $allowExt);
            }
            else{
                foreach($name as $nm){
                    $n = strrpos($nm, '.');
                    $ext = strtolower(substr($nm, $n+1));
                    if(!in_array($ext, $allowExt)){
                        return false;
                    }
                }
                return true;
            }
        }
    }

    /**
     * Checks if file size does not exceed the max file size allowed.
     *
     * @param string $filename The file input field name in $_FILES HTTP File Upload variables
     * @param int $maxSize Allowed max file size in kilo bytes.
     * @return bool Returns true if file size does not exceed the max file size allowed.
     */
    public function checkFileSize($filename, $maxSize){
        if(!empty($_FILES[$filename])){
            $size = $_FILES[$filename]['size'];
            if(is_array($size)===False){
                if(($size/1024)>$maxSize){
                    return false;
                }
            }
            else{
                foreach($size as $s){
                    if(($s/1024)>$maxSize){
                        return false;
                    }
                }
            }
            return true;
        }
    }

}

?>