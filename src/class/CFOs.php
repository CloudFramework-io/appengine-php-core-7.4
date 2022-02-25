<?php
/**
 * Class CFOs to handle CFO app for CloudFrameworkInterface
 * https://www.notion.so/cloudframework/CFI-PHP-Class-c26b2a1dd2254ddd9e663f2f8febe038
 * last_update: 202201
 */
class CFOs {

    /** @var Core7  */
    var $core;
    /** @var string $integrationKey To connect with the ERP */
    var $integrationKey='';
    var $error = false;                 // When error true
    var $errorMsg = [];                 // When error array of messages
    var $namespace = 'default';
    var $dsObjects = [];
    var $bqObjects = [];
    var $dbObjects = [];


    /**
     * DataSQL constructor.
     * @param Core $core
     * @param array $model where [0] is the table name and [1] is the model ['model'=>[],'mapping'=>[], etc..]
     */
    function __construct(Core7 &$core,$integrationKey='')
    {
        $this->core = $core;
        $this->integrationKey = $integrationKey;
        //region Create a
    }

    /**
     * @param $object
     * @return DataStore
     */
    public function ds ($object): DataStore
    {
        if(!isset($this->dsObjects[$object])) {
            $this->dsObjects[$object] = $this->core->model->getModelObject($object,['cf_models_api_key'=>$this->integrationKey]);
            if($this->core->model->error) {
                //$this->addError($this->core->model->errorMsg);
                //Return a Foo object instead to avoid exceptions in the execution
                    $this->createFooDatastoreObject($object);
                $this->dsObjects[$object]->error = true;
                $this->dsObjects[$object]->errorMsg = $this->core->model->errorMsg;
            }
        }
        return $this->dsObjects[$object];
    }

    /**
     * @param $object
     * @return DataStore
     */
    public function bq ($object): DataBQ
    {
        if(!isset($this->bqObjects[$object])) {
            $this->bqObjects[$object] = $this->core->model->getModelObject($object,['cf_models_api_key'=>$this->integrationKey]);
            if($this->core->model->error) {
                if(!is_object($this->bqObjects[$object]))
                    $this->createFooBQObject($object);
                $this->bqObjects[$object]->error = true;
                $this->bqObjects[$object]->errorMsg = $this->core->model->errorMsg;
            }
        }
        return $this->bqObjects[$object];
    }

    /**
     * @param $object
     * @return DataSQL
     */
    public function db ($object,$connection='default'): DataSQL
    {
        if(!isset($this->dbObjects[$object])) {
            $this->dbObjects[$object] = $this->core->model->getModelObject($object,['cf_models_api_key'=>$this->integrationKey]);
            if($this->core->model->error) {
                if(!is_object($this->dbObjects[$object]))
                    $this->createFooDBObject($object);
                $this->dbObjects[$object]->error = true;
                $this->dbObjects[$object]->errorMsg = $this->core->model->errorMsg;
            }
        }
        $this->core->model->dbInit($connection);
        return $this->dbObjects[$object];
    }

    /**
     * @param string $object
     * @return CloudSQL
     */
    public function dbConnection (string $connection='default'): CloudSQL
    {
        if(!$connection) $connection='default';

        if(!isset($this->core->model->dbConnections[$connection]))
            $this->addError("connection [$connection] has not previously defined");

        $this->core->model->dbInit($connection);
        return($this->core->model->db);

    }

    /**
     * Close Database Connections
     * @param string $connection Optional it specify to close a specific connection instead of all
     */
    public function dbClose (string $connection='')
    {
        $this->core->model->dbClose($connection);
    }

    /**
     * @param array $credentials Varaibles to establish a connection
     * @param string $connection Optional name of the connection. If empty it will be default
     * @return boolean
     */
    public function setDBCredentials (array $credentials,string $connection='default')
    {
        $this->core->config->set("dbServer",$credentials['dbServer']??null);
        $this->core->config->set("dbUser",$credentials['dbUser']??null);
        $this->core->config->set("dbPassword",$credentials['dbPassword']??null);
        $this->core->config->set("dbName",$credentials['dbName']??null);
        $this->core->config->set("dbSocket",$credentials['dbSocket']??null);
        $this->core->config->set("dbProxy",$credentials['dbProxy']??null);
        $this->core->config->set("dbProxyHeaders",$credentials['dbProxyHeaders']??null);
        $this->core->config->set("dbCharset",$credentials['dbCharset']??null);
        $this->core->config->set("dbPort",$credentials['dbPort']??'3306');

        if(!$this->core->model->dbInit($connection)) {
            $this->addError($this->core->model->errorMsg);
            return false;
        }
        else return true;

    }

    /**
     * Create a Foo Datastore Object to be returned in case someone tries to access a non created object
     */
    private function createFooDatastoreObject($object) {
        if(!isset($this->dsObjects[$object])) {
            $model = json_decode('{
                                    "KeyName": ["keyname","index|minlength:4"]
                                  }',true);
            $this->dsObjects[$object] = $this->core->loadClass('DataBQ',['Foo','default',$model]);
            if ($this->dsObjects[$object]->error) return($this->addError($this->dsObjects[$object]->errorMsg));
        }
    }

    /**
     * Create a Foo BQ Object to be returned in case someone tries to access a non created object
     */
    private function createFooBQObject($object) {
        if(!isset($this->bqObjects[$object])) {
            $model = json_decode('{
                                    "KeyName": ["string","index|minlength:4"]
                                  }',true);
            $this->bqObjects[$object] = $this->core->loadClass('DataBQ',['Foo',$model]);
            if ($this->bqObjects[$object]->error) return($this->addError($this->dsObjects[$object]->errorMsg));
        }
    }

    /**
     * Create a Foo DB Object to be returned in case someone tries to access a non created object
     */
    private function createFooDBObject($object) {

        if(!isset($this->dbObjects[$object])) {
            $model = json_decode('{
                                    "KeyName": ["int","isKey"]
                                  }',true);

            $this->dbObjects[$object] = $this->core->loadClass('DataSQL',['Foo',['model'=>$model]]);
            if ($this->dbObjects[$object]->error) return($this->addError($this->dbObjects[$object]->errorMsg));
        }
    }

    /**
     * @param $namespace
     */
    function setNameSpace($namespace) {
        $this->namespace = $namespace;
        $this->core->config->set('DataStoreSpaceName',$this->namespace);
        foreach (array_keys($this->dsObjects) as $object) {
            $this->ds($object)->namespace=$namespace;
        }
    }

    /**
     * Reset the cache to load the CFOs
     * @param $namespace
     */
    function resetCache() {
        $this->core->model->resetCache();
    }

    /**
     * Add an error in the class
     * @param $value
     */
    function addError($value)
    {
        $this->error = true;
        $this->errorMsg[] = $value;
    }

}