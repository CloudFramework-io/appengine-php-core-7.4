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
     * Create a Foo Datastore Object to be returned in case someone tries to access a non created object
     */
    private function createFooDatastoreObject($object) {
        if(!isset($this->dsObjects[$object])) {
            $model = json_decode('{
                                    "KeyName": ["keyname","index|minlength:4"]
                                  }',true);
            $this->dsObjects[$object] = $this->core->loadClass('DataStore',['Foo','default',$model]);
            if ($this->dsObjects[$object]->error) return($this->addError($this->dsObjects[$object]->errorMsg));
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