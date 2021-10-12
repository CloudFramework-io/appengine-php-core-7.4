<?php
/*
 * CloudFramework Mongo Class
 * https://www.php.net/manual/en/mongodb.installation.homebrew.php
 * https://www.mongodb.com/developer/quickstart/php-setup/
 * Mac:
 *     pecl install mongodb
 *     php -i | grep mongodb
 *     composer require mongodb/mongodb:^1.9
 */

if (!defined ("_MONGODB_CLASS_") ) {
    define("_MONGODB_CLASS_", TRUE);

    class DataMongo
    {

        /** @var Core7  */
        protected $core;
        var $error=false;                      // Holds the last error
        var $errorMsg=[];                      // Holds the last error

        /** @var MongoDB\Client $_client */
        protected $_client = null;                // Database Connection Link
        var $_debug = false;
        var $uri = '';

        // Query Variables
        var $limit = 100;


        function __construct(Core7 &$core, $uri = '')
        {
            $this->core = $core;
            if($uri) $this->uri = $uri;
            else $this->uri = $this->core->config->get('mongo.uri');
        }

        /**
         * @param string $h Host
         * @param string $u User
         * @param string $p Password
         * @param string $db DB Name
         * @param string $port Port. Default 3306
         * @param string $socket Socket
         * @return bool True if connection is ok.
         */
        function connect($uri='')
        {

            if ($this->_client) return true; // Optimize current connection.
            if($uri) $this->uri = $uri;
            if(!$this->uri) return($this->addError('Missing uri of connection'));
            $this->_client = new MongoDB\Client($this->uri);
            return true;

        }

        /**
         * Get the list of databases of a Mongo Conection
         * @return array|void
         */
        public function getDatabases() {
            if(!$this->connect()) return;
            $dbs =  $this->_client->listDatabases();
            $ret = [];
            foreach ($dbs as $db) {
                $ret[] = $db->getName();
            }
            return $ret;
        }

        /**
         * Get the list of collections of a Mongo Database
         * @return array|void
         */
        public function getCollections($db) {
            if(!$this->connect()) return;
            $db=  $this->_client->selectDatabase($db);
            $collections = $db->listCollections();
            $ret = [];
            foreach ($collections as $collection) {
                $ret[] = $collection->getName();
            }
            return $ret;
        }

        /**
         * Execute a find action over a collection
         * @param $db
         * @param $collection
         * @param $filter
         * @param array $options [projection=>array,sort=array,skip=>integer,limit=>integer,comment=>string,returnKey=>boolean,]. More info in https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-find/
         * @return array|void
         */
        public function find($db,$collection,$filter=[],$options = []) {
            $db = $this->_client->selectDatabase($db);
            $collection = $db->selectCollection($collection,[
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ]]);
            $options = $options+['limit'=>$this->limit];
            $ret = $collection->find($filter,$options)->toArray();

            // Transform the result into a simple array
            foreach ($ret as $i=>$item) {
                $ret[$i] = $this->transformTypes($item);
            }
            return($ret);
        }

        /**
         * Execute a insertion of one or multiple documents
         * https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-insertOne/
         * @param $db
         * @param $collection
         * @param $documents
         * @param array $options [projection=>array,sort=array,skip=>integer,limit=>integer,comment=>string,returnKey=>boolean,]. More info in https://docs.mongodb.com/php-library/current/reference/method/MongoDBCollection-find/
         * @return array|void
         */
        public function insert($db,$collection,$documents) {
            if(!$documents || !is_array($documents)) return ($this->addError(' insert($db,$collection,$document) $document is empty or not an array'));
            $db = $this->_client->selectDatabase($db);
            $collection = $db->selectCollection($collection,[
                'typeMap' => [
                    'root' => 'array',
                    'document' => 'array',
                    'array' => 'array',
                ]]);

            // if $documents is not an array 0..n convert into it
            if(!isset($documents[0])) $documents = [$documents[0]];

            //Evaluate _id fields and datefields to be converted in MongoDB\BSON\ObjectId or MongoDB\BSON\UTCDateTime
            foreach ($documents as $i=>$document) {
                if(isset($document['_id']) && is_string($document['_id'])) $documents[$i]['_id'] = new MongoDB\BSON\ObjectId($document['_id']);
                foreach ($document as $field=>$data) {
                    if(is_string($data) && preg_match('/[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]T[0-9][0-9]:[0-9][0-9]:[0-9][0-9]Z$/',$data)) {
                        $documents[$i][$field] = new MongoDB\BSON\UTCDateTime(new DateTime($data));
                    }
                }
            }

            // Insert documents
            $insertManyResults =$collection->insertMany($documents);
            $ids = $insertManyResults->getInsertedIds();

            // Transform the results of the insertions
            foreach ($ids as $i=>$id) {
                $documents[$i]['_id'] = $id->jsonSerialize()['$oid'];
                $documents[$i] = $this->transformTypes($documents[$i]);
            }

            return($documents);

        }

        /**
         * Transform Mongo objects in arrays, strings , numbers
         * @param array $entity
         * @param int $level
         * @return array
         */
        private function transformTypes($entity,$level=0) {
            if(!is_array($entity)) return $entity;
            $ret = [];
            foreach ($entity as $i=>$item) {
                if(is_array($item)) {
                    $item = $this->transformTypes($item,$level+1);
                }
                elseif(is_object($item)) {
                    switch (get_class($item)) {
                        case "MongoDB\BSON\ObjectId":
                            /** @var  MongoDB\BSON\ObjectId $item */
                            $item = $item->jsonSerialize()['$oid'];
                            break;
                        case "MongoDB\BSON\UTCDateTime":
                            /** @var  MongoDB\BSON\UTCDateTime $item */
                            $item = $item->toDateTime()->format('Y-m-d\TH:i:s\Z');
                            break;
                        default:
                            $item = $item;
                            break;
                    }
                }
                $ret[$i] = $item;
            }
            return $ret;
        }

        /**
         * Error Functions
         * @return bool
         */
        function addError($err) {
            $this->errorMsg[] = $err;
            $this->error = true;
        }
    }
}