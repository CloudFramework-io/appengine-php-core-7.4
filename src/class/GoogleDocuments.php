<?php
// https://developers.google.com/drive/v3/web/quickstart/php
// php composer.phar require google/apiclient:^2.0
/**
 * Class to facilitate the Google Drive and Google Documents creation
 * Documents mime-type: https://developers.google.com/drive/api/v3/mime-types
 * Last update: 2021-11-29
 */

// Instagram Class v1
if (!defined ("_Google_CLASS_GoogleDocuments") ) {
    define("_Google_CLASS_GoogleDocuments", TRUE);

    class GoogleDocuments
    {

        /** @var Core7 $core */
        private $core;

        /** @var Google_Service_Sheets $spreedsheet with contain the properties to feed an Excel File*/
        var $spreedsheet = null;

        /** @var Google_Service_Drive $drive will manage the drive access properties*/
        private $drive = null;

        /** @var Google_Service_Drive_DriveFile $file will manage  files in general*/
        private $file = null;


        var $error = false;
        var $errorMsg = [];

        /**
         * CloudFramework GoogleDocument class
         * @param Core7 $core
         * @param array $config
         */
        function __construct(Core7 &$core, $config = [])
        {
            $this->core = $core;
            $client = $this->getGoogleClient($config);
            $this->spreedsheet = new Google_Service_Sheets($client);
            $this->drive = new Google_Service_Drive($client);
            $this->file = new Google_Service_Drive($client);
        }


        /**
         * Deprecated Create a Spreadsheet
         * @param $title
         * @param string $parent_id
         * @return string|false
         */
        //        public function createSpreadsheet($title,$parent_id='') {
        //            try {
        //                $spreadsheet = new Google_Service_Sheets_Spreadsheet(['properties' => ['title' => $title]]);
        //                $ss = $this->spreedsheet->spreadsheets->create($spreadsheet,['fields' => 'spreadsheetId']);
        //                return $ss->spreadsheetId;
        //            } catch(Exception $e) {
        //                $this->addError($e->getMessage());
        //                return false;
        //            }
        //        }

        /**
         * Assign to a $user_email priveleges over a $document_id with a specific rol
         * https://stackoverflow.com/questions/37846076/create-a-spreadsheet-api-v4
         * https://developers.google.com/drive/api/v3/ref-roles
         * $permissions_examples: permissions = [
         * {
         * 'type': 'user',
         * 'role': 'writer',
         * 'emailAddress': 'user@example.com'
         * }, {
         * 'type': 'domain',
         * 'role': 'writer',
         * 'domain': 'example.com'
         * }]
         * @param $document_id
         * @param $user_email
         * @param string $role values can be: reader, writer, commenter, fileOrganizer(only shared drives), organizer(only shared drives), owner
         * @return bool
         */
        public function assignDocumentPermissions($document_id,$user_email,$role='writer') {

            if(!is_object($this->drive)) return($this->add);
            $newPermission = new Google_Service_Drive_Permission();
            $newPermission->setEmailAddress($user_email);
            $newPermission->setType('user');
            $newPermission->setRole('writer');

            // The user $user_mail has to be in the same organization than the owner
            if($role=='owner') {
                $optParams = array('sendNotificationEmail' => true,'transferOwnership'=>true);
            }
            $optParams = array('sendNotificationEmail' => false);
            try {
                $this->drive->permissions->create($document_id,$newPermission,$optParams);
                return true;
            } catch(Exception $e) {
                $this->addError($e->getMessage());
                return false;
            }
        }

        /**
         * Create a Spreadsheet file
         * @param string $spreadsheet_name
         * @param string $parent_id value of the parent folder id
         * @return void
         */
        public function createSpreadSheet(string $spreadsheet_name, string $parent_id = "") {
            try {
                $file = new Google_Service_Drive_DriveFile();
                $file->setName($spreadsheet_name);
                $file->setMimeType('application/vnd.google-apps.spreadsheet');
                if($parent_id)
                    $file->setParents([$parent_id]);

                $retFile = $this->drive->files->create($file);
                return $retFile->getId();
            } catch (Exception $e) {
                return $this->addError($e->getMessage());
            }

        }

        /**
         * Create a folder under a parent
         * @param string $folder_name
         * @param string $parent_id value of the parent folder id
         */
        public function createDriveFolder(string $folder_name, string $parent_id = "") {
            try {
                $file = new Google_Service_Drive_DriveFile();
                $file->setName($folder_name);
                $file->setMimeType('application/vnd.google-apps.folder');
                if($parent_id)
                    $file->setParents([$parent_id]);

                $retFile = $this->drive->files->create($file,[ 'supportsAllDrives' => true]);
                return $retFile->getId();
            } catch (Exception $e) {
                return $this->addError($e->getMessage());
            }

        }

        /**
         * List the Files Shared we the user
         * @return array|void
         */
        public function getFilesSharedWithMe($pageSize=1000,$pageToken='') {
            return $this->getFiles('sharedWithMe',$pageSize,$pageToken);
        }

        /**
         * List the Files
         * @param string $q Some examples of queries: "'me' in owners and trashed = false" or "sharedWithMe"
         * @param int $pageSize
         * @param string $pageToken
         * @return array|void
         */
        private function getFiles($q='',$pageSize=1000,$pageToken='') {
            try {
                //region SET $options
                $options =[
                    'pageSize' => $pageSize,
                    'fields' => 'nextPageToken,files(id,name,mimeType)',  // or '*'
                ];
                if($q) $opt['q'] = $q;
                if($pageToken) $opt['pageToken'] = $pageToken;

                $files = $this->drive->files->listFiles($options);
                $ret=[
                    'pageToken'=>$pageToken
                    ,'nextPageToken'=>$files->getNextPageToken()
                    ,'pageSize'=>$pageSize
                    ,'files'=>[]
                ];
                /** @var Google\Service\Drive\DriveFile $file */
                foreach ($files as $file) {
                    $mimeType = $file->getMimeType();
                    $name = $file->getName();
                    $url='';
                    switch ($mimeType) {
                        case "application/vnd.google-apps.folder":
                            $url = "https://drive.google.com/drive/folders/".$file->getId();
                            break;
                        case "application/vnd.google-apps.spreadsheet":
                            $url = "https://docs.google.com/spreadsheets/d/".$file->getId();
                            break;
                        case "application/vnd.google-apps.presentation":
                            $url = "https://docs.google.com/presentation/d/".$file->getId();
                            break;
                        case "application/vnd.google-apps.document":
                        case "text/html":
                            $url = "https://docs.google.com/document/d/".$file->getId();
                            break;
                    }
                    $ret['files'][] = ['name'=>$name,'id'=>$file->getId(),'mimeType'=>$file->getMimeType(),'url'=>$url];
                }
                return($ret);
            } catch (Exception $e) {
                return $this->addError($e->getMessage());
            }
        }

        /**
         * Delete a file in drive
         * The error codes can be: 404 = not found, 403 = insufficient permissions
         * @param string $fileId
         * @return bool
         */
        public function deleteDriveFile($fileId) {
            try {
                $this->drive->files->delete($fileId);
                return true;
            } catch (Exception $e) {
                $this->addError(['code'=>$e->getCode(),'message'=>$e->getMessage()]);
                return false;
            }
        }

        /**
         * Update SpreadSheet
         * The error codes can be: 404 = not found, 403 = insufficient permissions
         * @param string $fileId
         * @param array $values Array of [ rows [cols]]
         * @param string $range Where to start the update
         * @return array|void
         */
        public function updateSpreadSheet($fileId,$values,$range='A1') {
            try {
                $update_body = new Google_Service_Sheets_ValueRange();
                $update_body->setRange($range);
                $update_body->setValues($values);
                $update = $this->spreedsheet->spreadsheets_values->update($fileId,$range,$update_body,[
                    'valueInputOption' => 'USER_ENTERED'
                ]);
                $ret = [
                    'updatedRows'=>$update->getUpdatedRows()
                    ,'updatedCells'=>$update->getUpdatedCells()
                    ,'updatedRange'=>$update->getUpdatedRange()
                    ,'spreadsheetId'=>$update->getSpreadsheetId()
                    ,'url'=>'https://docs.google.com/spreadsheets/d/'.$update->getSpreadsheetId()
                ];
                return $ret;
            } catch (Exception $e) {
                $this->addError(['code'=>$e->getCode(),'message'=>$e->getMessage()]);
                return false;
            }
        }

        /**
         * Insert data in  SpreadSheet
         * The error codes can be: 404 = not found, 403 = insufficient permissions
         * @param string $fileId
         * @param array $values Array of [ rows [cols]]
         * @param string $range Where to start the update
         * @return array|void
         */
        public function insertSpreadSheet($fileId,$values,$range='A1') {
            try {
                $insert_body = new Google_Service_Sheets_ValueRange();
                $insert_body->setRange($range);
                $insert_body->setValues($values);
                /** @var Google\Service\Sheets\AppendValuesResponse $insert */
                $insert = $this->spreedsheet->spreadsheets_values->append($fileId,$range,$insert_body,[
                    'valueInputOption' => 'USER_ENTERED',
                    'insertDataOption' => 'INSERT_ROWS'
                ]);
                $update = $insert->getUpdates();

                // "valueInputOption" => "RAW"
                $ret = [
                    'updatedRows'=>$update->getUpdatedRows()
                    ,'updatedCells'=>$update->getUpdatedCells()
                    ,'updatedRange'=>$update->getUpdatedRange()
                    ,'spreadsheetId'=>$update->getSpreadsheetId()
                    ,'url'=>'https://docs.google.com/spreadsheets/d/'.$update->getSpreadsheetId()
                ];
                return $ret;
            } catch (Exception $e) {
                $this->addError(['code'=>$e->getCode(),'message'=>$e->getMessage()]);
                return false;
            }
        }

        /**
         * Read data from  SpreadSheet
         * The error codes can be: 404 = not found, 403 = insufficient permissions
         * @param string $fileId
         * @param string $range Where to start the update
         * @return array|void
         */
        public function readSpreadSheet($fileId,$range='A1') {

            try {
                $result = $this->spreedsheet->spreadsheets_values->get($fileId,$range);
                $ret = [];
                foreach ($result as $item) {
                    $ret[] = $item;
                }
                return $ret;
            } catch (Exception $e) {
                $this->addError(['code'=>$e->getCode(),'message'=>$e->getMessage()]);
                return false;
            }
        }

        /**
         * Return a Google Client with the scopes necessary to manage Google Documents
         */
        private function getGoogleClient(&$config) {
            $client = new Google_Client();

            if($config)
                $client->setAuthConfig($config);
            $client->useApplicationDefaultCredentials();
            $client->setScopes([Google_Service_Sheets::SPREADSHEETS,Google_Service_Sheets::DRIVE,Google_Service_Sheets::DRIVE_FILE]);
            return($client);
        }


        /**
         * Add an error into the classs
         * @param $msg
         */
        private function addError($msg) {
            $this->error = true;
            $this->errorMsg[] = $msg;
        }
    }
}