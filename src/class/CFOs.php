<?php
/**
 * [$cfos = $this->core->loadClass('CFOs');] Class CFOs to handle CFO app for CloudFrameworkInterface
 * https://www.notion.so/cloudframework/CFI-PHP-Class-c26b2a1dd2254ddd9e663f2f8febe038
 * last_update: 202201
 * @package CoreClasses
 */
class CFOs {

    /** @var Core7  */
    var $core;
    /** @var string $integrationKey To connect with the ERP */
    var $integrationKey='';
    var $error = false;                 // When error true
    var $errorMsg = [];                 // When error array of messages
    var $namespace = 'default';
    var $project_id = null;
    var $service_account = null;
    var $keyId = null;
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
        $this->project_id = $this->core->gc_project_id;
        //region Create a
    }

    /**
     * @param $object
     * @param string $namespace
     * @param string $project_id
     * @param array $service_account
     */
    public function dsInit ($object,$namespace='',$project_id='',$service_account=[])
    {

        //region SET $options['cf_models_api_key'], $options['namespace'], $options['projectId']
        $options = ['cf_models_api_key'=>$this->integrationKey];
        if($namespace) $options['namespace'] = $namespace;
        if($project_id) $options['projectId'] = $project_id;
        elseif($this->project_id) $options['projectId'] = $this->project_id;

        if($service_account) $options['keyFile'] = $service_account;
        elseif($this->service_account) $options['keyFile'] = $this->service_account;

        //endregion

        $this->dsObjects[$object] = $this->core->model->getModelObject('ds:'.$object,$options);
        if($this->core->model->error) {
            //$this->addError($this->core->model->errorMsg);
            //Return a Foo object instead to avoid exceptions in the execution
            $this->createFooDatastoreObject($object);
            $this->dsObjects[$object]->error = true;
            $this->dsObjects[$object]->errorMsg = $this->core->model->errorMsg;
        }
    }

    /**
     * @param $object
     * @return DataStore
     */
    public function ds ($object): DataStore
    {
        if(!isset($this->dsObjects[$object]))
            $this->dsInit($object);
        return $this->dsObjects[$object];
    }

    /**
     * Initialize a bq $object
     * @param $object
     * @param string $project_id
     * @param array $service_account
     */
    public function bqInit ($object,$project_id='',$service_account=[])
    {
        $options = ['cf_models_api_key'=>$this->integrationKey];
        if($project_id) $options['projectId'] = $project_id;
        elseif($this->project_id) $options['projectId'] = $this->project_id;
        if($service_account) $options['keyFile'] = $service_account;
        elseif($this->service_account) $options['keyFile'] = $this->service_account;

        $this->bqObjects[$object] = $this->core->model->getModelObject('bq:'.$object,$options);
        if($this->core->model->error) {
            if(!is_object($this->bqObjects[$object]))
                $this->createFooBQObject($object);
            $this->bqObjects[$object]->error = true;
            $this->bqObjects[$object]->errorMsg = $this->core->model->errorMsg;
        }
    }

    /**
     * Return a bq $object
     * @param $object
     * @return DataStore
     */
    public function bq ($object): DataBQ
    {
        if(!isset($this->bqObjects[$object]))
            $this->bqInit($object);

        return $this->bqObjects[$object];
    }

    /**
     * @param $object
     * @return DataSQL
     */
    public function db ($object,$connection='default'): DataSQL
    {
        if(!isset($this->dbObjects[$object])) {
            $this->dbObjects[$object] = $this->core->model->getModelObject('db:'.$object,['cf_models_api_key'=>$this->integrationKey]);
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
     * Execute a Direct query inside a $connection
     * @param $q
     * @param null $params
     * @param string $connection
     * @return array|void
     */
    public function dbQuery ($q,$params=null,$connection='default')
    {
        $this->core->model->dbInit($connection);
        $ret= $this->core->model->dbQuery('CFO Direct Query for connection  '.$connection,$q,$params);
        if($this->core->model->error) $this->addError($this->core->model->errorMsg);
        return($ret);
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
     * Assign DB Credentials to stablish connection
     * @param array $credentials Varaibles to establish a connection
     * $credentials['dbServer']
     * $credentials['dbUser']
     * $credentials['dbPassword']??null);
     * $credentials['dbName']??null);
     * $credentials['dbSocket']??null);
     * $credentials['dbProxy']??null);
     * $credentials['dbProxyHeaders']??null);
     * $credentials['dbCharset']??null);
     * $credentials['dbPort']??'3306');
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
     * @ignore
     */
    private function createFooDatastoreObject($object) {
        if(!isset($this->dsObjects[$object])) {
            $model = json_decode('{
                                    "KeyName": ["keyname","index|minlength:4"]
                                  }',true);
            $this->dsObjects[$object] = $this->core->loadClass('Datastore',['Foo','default',$model]);
            if ($this->dsObjects[$object]->error) return($this->addError($this->dsObjects[$object]->errorMsg));
        }
    }

    /**
     * Create a Foo BQ Object to be returned in case someone tries to access a non created object
     * @ignore
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
     * @ignore
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
     * Set a default project_id overwritting the default project_id
     * @param $project_id
     */
    function setProjectId($project_id) {
        $this->project_id = $project_id;
    }

    /**
     * Set a default service account for Datastore and BigQuery Objects. It has to be an array
     * @param array $service_account
     */
    function setServiceAccount(array $service_account) {
        $this->service_account = $service_account;
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