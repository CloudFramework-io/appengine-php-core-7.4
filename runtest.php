<?php
$rootPath = exec('pwd');
// Autoload libraries
require_once  $rootPath.'/vendor/autoload.php';
include_once __DIR__.'/src/Core7.php';
include_once __DIR__.'/src/class/Tests.php';
$core = new Core7($rootPath);

// Load DataStoreClient to optimize calls
use Google\Cloud\Datastore\DatastoreClient;
$datastore = null;
if($core->config->get('core.datastore.on')) {
    if($core->is->development()) {
        $datastore = new DatastoreClient(['transport'=>'rest']);
    } else {
        $datastore = new DatastoreClient(['transport'=>'grpc']);
    }
}

use Google\Cloud\Logging\LoggingClient;
$logger = LoggingClient::psrBatchLogger('app');

// Check test exist
if(true) {
    system('clear');
    $script=$argv[1];
    $params='';
    if(isset($argv[1]) && strpos($argv[1],'?'))
        list($script,$params) = explode('?',str_replace('..','',$argv[1]),2);

    $script = explode('/',$script);
    $path = ($script[0][0]=='_')?__DIR__:$core->system->app_path.'/tests';
    if($core->config->get('core.tests.path')) $path=$core->config->get('core.tests.path');

    echo "CloudFramwork Test Script v1.0\napp_path: {$path}\n------------------------------\n\n";
    if(!strlen($script[0])) die ('Mising Test name: Use php vendor/cloudframework-io/appengine-php-core/runtest.php {test_name}'."\n\n");

    // $script[0] = string
    if(!is_file($path.'/'.$script[0].'.php')) die('Test not found: '.$path.'/'.$script[0]."\n\n Create it with:\n-------\n<?php\nclass Test extends Tests {\n\tfunction main() { \$this->wants('Check class works'); }\n}\n-------\n\n");

    include_once $path.'/'.$script[0].'.php';
    if(!class_exists('Test')) die($path.'/'.$script[0].' does not include a "Class Test'."\n\n");

    /** @var Tests $test */
    $test = new Test($core,$argv);
    $test->params = $script;
    if(strlen($params))
        parse_str($params,$test->formParams);
    if(!method_exists($test,'main')) die('The class Test does not include the method "main()'."\n\n");
}

$test->main();
//system("clear");
//echo $test->send(true,true,$argv);
echo "\n\n";
