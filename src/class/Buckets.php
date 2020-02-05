<?php

/**
 * @author Héctor López <hlopez@cloudframework.io>
 * @version 202001051309 - php7
 */

// CloudSQL Class v10
if (!defined ("_Buckets_CLASS_") ) {
    define ("_Buckets_CLASS_", TRUE);

    class Buckets {

        private $core;
        var $bucket = '';
        var $error = false;
        var $code = '';
        var $errorMsg = array();
        var $max = array();
        var $uploadedFiles = array();
        var $isUploaded = false;
        var $vars = [];
        var $gs_bucket = null;


        Function __construct (Core7 &$core,$bucket='') {
            $this->core = $core;

            if(strlen($bucket)) $this->bucket = $bucket;
            else $this->bucket = $this->core->config->get('bucketUploadPath');
            if(!$this->bucket) return($this->addError('Missing bucketUploadPath config var or $bucket in the constructor'));

            if(strpos($this->bucket,'gs://')===0) {
                try {
                    $this->gs_bucket = $this->core->gc_datastorage_client->bucket(str_replace('gs://','',$this->bucket));
                    if(!$this->gs_bucket->exists()) return($this->addError('I can not find bucket: '.$this->bucket));
                } catch (Exception $e) {
                    return($this->addError($e->getMessage()));
                }
            } else {
                if(!is_dir($this->bucket)) return($this->addError('I can not find bucket: '.$this->bucket));
            }

            $this->vars['upload_max_filesize'] = ini_get('upload_max_filesize');
            $this->vars['max_file_uploads'] = ini_get('max_file_uploads');
            $this->vars['file_uploads'] = ini_get('file_uploads');
            $this->vars['default_bucket'] = $this->bucket;
            $this->vars['retUploadUrl'] = $this->core->system->url['host_url_uri'];

            if(count($_FILES)) {
                foreach ($_FILES as $key => $value) {
                    if(is_array($value['name'])) {
                        for($j=0,$tr2=count($value['name']);$j<$tr2;$j++) {
                            foreach ($value as $key2 => $value2) {
                                $this->uploadedFiles[$key][$j][$key2] = $value[$key2][$j];
                            }
                        }
                    } else {
                        $this->uploadedFiles[$key][0] = $value;
                    }
                    $this->isUploaded = true;
                }
            }
        }

        function deleteUploadFiles() {
            if(strlen($_FILES['uploaded_files']['tmp_name'])) unlink($_FILES['uploaded_files']['tmp_name']);
        }

        /**
         * @param string $dest_bucket optional.. if this is passed then it has to start with: gs:// or / for local enviroments with google_app_engine.disable_readonly_filesystem=1 in php.ini
         * @param array $options ['public'=>(bool),'ssl'=>(bool),'apply_hash_to_filenames'=>(bool),'allowed_extensions'=>(array),'content_types'=>(array)]
         * @return array
         */
        function manageUploadFiles($dest_bucket='', $options =[]) {


            $public=($options['public'])?true:false;
            $ssl=($options['ssl'])?true:false;
            $apply_hash_to_filenames = ($options['apply_hash_to_filenames'])?true:false;
            $allowed_extensions = ($options['allowed_extensions'])?explode(',',strtolower($options['allowed_extensions'])):[];
            $allowed_content_types = ($options['allowed_content_types'])?explode(',',strtolower($options['allowed_content_types'])):[];


            // Calculate base_dir
            $base_dir = '';
            if($dest_bucket) {
                if(strpos($dest_bucket,'gs://')===0) $base_dir = $dest_bucket;
                else {
                    if($this->core->is->development()) {
                        $base_dir = $dest_bucket;
                    } else {
                        $dir_sep = (substr($dest_bucket,0,1)=='/')?'':'/';
                        $base_dir = 'gs:/'.$dir_sep.$dest_bucket;
                    }
                }
            } else {
                if($this->core->is->development()) {
                    $base_dir = $this->bucket;
                } else {
                    $base_dir = $this->bucket;
                }
            }

            if(!is_dir($base_dir)) {
                return($this->addError('the path to write the files does not exist: '.$base_dir));
            }


            if($this->isUploaded)  {
                foreach ($this->uploadedFiles as $key => $files) {
                    for($i=0,$tr=count($files);$i<$tr;$i++) {
                        $value = $files[$i];
                        if(!$value['error']) {

                            // If the name of the file uploaded has special chars, the system convert it into mime-encode-utf8
                            if(strpos($value['name'],'=?UTF-8') !== false) $value['name'] = iconv_mime_decode($value['name'],0,'UTF-8');

                            // Extension calculation
                            $extension = '';
                            if(strpos($value['name'],'.')) {
                                $parts = explode('.',$value['name']);
                                $extension = '.'.strtolower($parts[count($parts)-1]);
                            }

                            // Do I have allowed extensions
                            if($allowed_extensions) {
                                $allow = false;
                                if($extension)
                                    foreach ($allowed_extensions as $allowed_extension) {
                                        if('.'.trim($allowed_extension) == $extension ) {
                                            $allow=true;
                                            break;
                                        }
                                    }
                                if(!$allow) return($this->addError($value['name'].' does not have any of the following extensions: '.$options['allowed_extensions'],'extensions'));
                            }

                            // Do I have allowed content types
                            if($allowed_content_types) {

                                $allow = false;
                                foreach ($allowed_content_types as $allowed_content_type) {
                                    if(strpos(strtolower($value['type']),trim($allowed_content_type)) !== false ) {
                                        $allow=true;
                                        break;
                                    }
                                }
                                if(!$allow) return($this->addError($value['type'].' does not match with any of the following content-types: '.$options['allowed_content_types'],'content-type'));
                            }

                            // do not use the original name.. and transform to has+extension
                            if($apply_hash_to_filenames) {

                                $this->uploadedFiles[$key][$i]['hash_from_name'] = $value['name'];
                                $value['name'] = date('Ymshis').uniqid('_upload'). md5($value['name']).$extension;
                                $this->uploadedFiles[$key][$i]['name'] = $value['name'];
                            }


                            $dest = $base_dir.'/'.$value['name'];

                            // Let's try to move the temporal files to their destinations.
                            try {
                                $context = ['gs'=>['Content-Type' => $value['type']]];
                                if($public) {
                                    $context['gs']['acl'] = 'public-read';
                                }
                                stream_context_set_default($context);
                                if(move_uploaded_file($value['tmp_name'],$dest)) {
                                    $this->uploadedFiles[$key][$i]['movedTo'] = $dest;
                                    $this->uploadedFiles[$key][$i]['publicUrl'] = '';
                                    if(is_object($this->gs_bucket)) {
                                        if($public) {
                                            $file = str_replace($this->bucket . '/', '', $dest);
                                            $object = $this->gs_bucket->object($file);
                                            $object->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
                                            $this->uploadedFiles[$key][$i]['publicUrl'] = 'https://storage.googleapis.com/'.$this->gs_bucket->name().'/'.$file;
                                        }
                                    }
                                } else {
                                    $this->addError(error_get_last());
                                    $this->uploadedFiles[$key][$i]['error'] = $this->errorMsg;
                                }

                            }catch(Exception $e) {
                                $this->addError($e->getMessage());
                                $this->addError(error_get_last());
                                $this->uploadedFiles[$key][$i]['error'] = $this->errorMsg;
                            }
                        }
                    }

                }

            }
            return($this->uploadedFiles);
        }

        function getPublicUrl($file,$ssl=true) {

            // Check $file
            $ret = 'file missing';
            if(!$file) return $ret;

            // Check $this->bucket
            if(strpos($file,'gs:')!==0 && strpos($file,'http:')!==0 && strpos($file,'https:')!==0) {
                $ret = 'file missing';
                if(!$this->bucket) return $ret;
                $file = $this->bucket.$file;
            }

            // Calculating the return url
            if(strpos($file,'gs://') !== 0 ) {
                $ret  = $this->core->system->url['host_base_url'].str_replace($_SERVER['DOCUMENT_ROOT'], '',$file);
            }
            else {
                $file = 'https://storage.googleapis.com/'.$this->gs_bucket->name().$file;
                $ret = $file;
            }
            return $ret;

        }

        function scan($path='') {
            $ret = array();
            $tmp = scandir($this->bucket.$path);
            foreach ($tmp as $key => $value) {
                $ret[$value] = array('type'=>(is_file($this->bucket.$path.'/'.$value))?'file':'dir');
                if(isset($_REQUEST['__p'])) __p('is_dir: '.$this->bucket.$path.'/'.$value);
            }
            return($ret);
        }
        function fastScan($path='') {
            return(scandir('gs://'.$this->bucket.$path));
        }

        function deleteAllFiles($path='') { $this->deleteFiles($path,'*');}
        function deleteFiles($path='',$file) {
            if(is_array($file)) $files=$file;
            else if($file == '*') $files = $this->fastScan($path);
            else $file[] = file;
            foreach ($files as $key => $value) {
                $value = $this->bucket.$path.'/'.$value;
                $ret[$value] = 'ignored';
                if(is_file($value)) {
                    $ret[$value] = 'deleting: '.unlink($value);
                }
            }
            return($ret);
        }

        function rmdir($path='')  {
            $value = $this->bucket.$path;
            $ret = false;
            try {
                $ret = rmdir($value);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                $this->addError(error_get_last());
            }
            return $ret;
        }

        function mkdir($path='')  {
            $value = $this->bucket.$path;
            $ret = false;
            try {
                $ret = @mkdir($value);
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                $this->addError(error_get_last());
            }
            return $ret;
        }

        function isDir($path='')  {
            $value = 'gs://'.$this->bucket.$path;
            return(is_dir($value));
        }

        function isFile($file='')  {
            $value = $this->bucket.$file;
            return(is_file($value));
        }

        function isMkdir($path='')  {
            if($path && is_string($path) && $path[0]!='/') return($this->addError('$path does not start with /'));
            $value = $this->bucket.$path;
            $ret = is_dir($value);
            if(!$ret && $path) try {
                $elements = explode('/',$path);
                //delete the first empty value of /path
                array_shift($elements);
                $value = $this->bucket;
                $ret = false;
                foreach ($elements as $element) {
                    $value .= "/{$element}";
                    if(!is_dir($value)) $ret = @mkdir($value);
                }
                $ret = true;
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                $this->addError(error_get_last());
                return false;
            }
            return $ret;
        }


        function putContents($file, $data, $path='',$options = ['gs' =>['Content-Type' => 'text/plain']] ) {

            $ctx = stream_context_create($options);

            $ret = false;
            try{
                if(@file_put_contents($this->bucket.$path.'/'.$file, $data,0,$ctx) === false) {
                    $this->addError(error_get_last());
                } else {$ret = true;}
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                $this->addError(error_get_last());
            }
            return($ret);
        }

        function getContents($file,$path='') {
            $ret = '';
            try{
                $ret = @file_get_contents($this->bucket.$path.'/'.$file);
                if($ret=== false) {
                    $this->addError(error_get_last());
                }
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                $this->addError(error_get_last());
            }
            return($ret);
        }

        /**
         * Returns the URL to upload a file
         * https://stackoverflow.com/questions/53346083/create-google-cloud-storage-upload-urls-for-php7-2/53833015
         * @param string $returnUrl is the url the system has to call once the file has been uploaded
         * @return mixed
         */
        function getUploadUrl($returnUrl=null) {
            return($returnUrl);
            /*
            if(!$returnUrl) $returnUrl = $this->vars['retUploadUrl'];
            else $this->vars['retUploadUrl'] = $returnUrl;
            $options = array( 'gs_bucket_name' => str_replace('gs://','',$this->bucket) );
            $upload_url = CloudStorageTools::createUploadUrl($returnUrl, $options);
            return($upload_url);
            */
        }

        /**
         * Returns an url to download a gs_file using CFBlobDownload Technique
         * @param $url
         * @param $params array ['downloads'=>'number of downloads allowed, by default 1', 'spacename'=>'to store downloads files in Datastore', 'content-type' =>'content type of the file']
         * @return string URL to download the file $params['donwloads'] times
         */
        function getCFBlobDownloadUrl($gs_file, $params,$blob_service = 'https://api.cloudframework.io/blobs') {

            // check the file exists
            if(!is_file($gs_file)) return($this->addError("{$gs_file} does not exist"));

            // Get Hash from file_name and params
            $hash = md5($gs_file.json_encode($params));
            $url = $blob_service.'/'.$hash;

            $cache = $params;

            // downloads allowed
            $cache['downloads'] = (isset($params['downloads']) && intval($params['downloads'])>0)?intval($params['downloads']):1;

            //if we are going to use DataStore temporal files retrive spacename
            $spacename = (isset($params['spacename']))?$params['spacename']:null;
            if($spacename) {
                $this->core->cache->activateDataStore($this->core,$spacename);
                $url.='/ds/'.$spacename;
            }

            // adding $url
            $cache['url'] = $gs_file;

            // downloads allowed
            $cache['content-type'] = (isset($params['content-type']))?$params['content-type']:'application/octet-stream';

            $this->core->cache->set($hash,$cache);

            return $url;

        }



        function setError($msg,$code='') {
            $this->errorMsg = array();
            $this->addError($msg,$code);
        }
        function addError($msg,$code='') {
            $this->error = true;
            $this->errorMsg[] = $msg;
            $this->code = $code;
        }
    }
}


