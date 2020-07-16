<?php
$_root_path = (strlen($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['PWD'];
// Autoload libraries
require_once  $_root_path.'/vendor/autoload.php';

// Load Core class
include_once(__DIR__ . "/Core7.php"); //
$core = new Core7();

// Load DataStoreClient to optimize calls
use Google\Cloud\Datastore\DatastoreClient;
$datastore = null;
if(getenv('PROJECT_ID') && $core->config->get('core.datastore.on')) {
    if($core->is->development()) {
        $datastore = new DatastoreClient(['transport'=>'rest']);
    } else {
        $datastore = new DatastoreClient(['transport'=>'grpc']);
    }
}

// https://cloud.google.com/logging/docs/setup/php
use Google\Cloud\Logging\LoggingClient;
$logger = null;
if(getenv('PROJECT_ID') && $core->is->production()) {
    $logger = LoggingClient::psrBatchLogger('app');
}

// Run Dispatch
$core->dispatch();

// Apply performance parameter
//region performance ?__p parameter
if (isset($_GET['__p'])) {
    _print($core->__p->data['info']);

    if ($core->errors->lines)
        _print($core->errors->data);

    if ($core->logs->lines)
        _print($core->logs->data);
}
//endregion
