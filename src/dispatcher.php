<?php
$_root_path = (strlen($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['PWD'];
// Autoload libraries
require_once  $_root_path.'/vendor/autoload.php';

//region INIT $core = new Core7();
include_once(__DIR__ . "/Core7.php"); //
$core = new Core7();
//endregion

//region SET $datastore
// Load DataStoreClient to optimize calls
use Google\Cloud\Datastore\DatastoreClient;
$datastore = null;
if((getenv('PROJECT_ID') || $core->config->get("core.gcp.datastore.project_id")) && ($core->config->get('core.datastore.on') || $core->config->get('core.gcp.datastore.on'))) {

    //2021-02-25: Fix to force rest transport instead of grpc because it crash for certain content.
    if(isset($_GET['_fix_datastore_transport'])) $core->config->set('core.datastore.transport','rest');

    $transport = ($core->config->get('core.datastore.transport')=='grpc')?'grpc':'rest';
    $datastore = new DatastoreClient(['transport'=>$transport,'projectId'=>($core->config->get("core.gcp.datastore.project_id"))?:getenv('PROJECT_ID')]);
}
//endregion

//region SET $logger
// https://cloud.google.com/logging/docs/setup/php
use Google\Cloud\Logging\LoggingClient;
$logger = null;
if(getenv('PROJECT_ID') && $core->is->production()) {
    $logger = LoggingClient::psrBatchLogger('app');
}
//endregion

//region RUN $core->dispatch();
$core->dispatch();
//endregion

//region EVALUATE ?__p GET parameter when we are in a script
// Apply performance parameter
if (isset($_GET['__p'])) {
    _print($core->__p->data['info']);

    if ($core->errors->lines)
        _print($core->errors->data);

    if ($core->logs->lines)
        _print($core->logs->data);
}
//endregion
