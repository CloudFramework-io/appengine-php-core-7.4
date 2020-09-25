<?php
$rootPath = exec('pwd');
// Autoload libraries
require_once  $rootPath.'/vendor/autoload.php';

include_once __DIR__.'/src/Core7.php';
$core = new Core7($rootPath);


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

use Google\Cloud\Logging\LoggingClient;
$logger = null;
if(getenv('PROJECT_ID') && $core->is->production()) {
    $logger = LoggingClient::psrBatchLogger('app');
}



// Check test exist
if(true) {

    $script = [];


    if(count($argv)>1) {
        if(strpos($argv[1],'?'))
            list($script, $formParams) = explode('?', str_replace('..', '', $argv[1]), 2);
        else {
            $script = $argv[1];
            $formParams = '';
        }
        $script = explode('/', $script);
        $path = ($script[0][0] == '_') ? __DIR__.'/scripts' : (($core->config->get('core.scripts.path')?$core->config->get('core.scripts.path'):$core->system->app_path.'/scripts'));
    }

    echo "CloudFramwork Script v1.0\nroot_path: {$rootPath}\napp_path: {$path}\n";
    if(!strlen($script[0])) die ('Mising Script name: Use php vendor/cloudframework-io/appengine-php-core/runscript.php {script_name}[/params[?formParams]] [--options]'."\n\n");
    echo "Script: /scripts/{$script[0]}.php\n";

    // Reading local_script
    if(is_file('./local_script.json')) {
        $core->config->readConfigJSONFile('./local_script.json');
        if($core->errors->lines) {
            _printe(['errors'=>$core->errors->data]);
            exit;
        } else {
            echo "local_script.json: read\n";

        }


    }
    echo "------------------------------\n";


    // Evaluate options
    $options = ['performance'=>in_array('--p',$argv)];

    if(!is_file($path.'/'.$script[0].'.php')) die("Script not found. Create it with:\n-------\n<?php\nclass Script extends Scripts {\n\tfunction main() { }\n}\n-------\n\n");
    include_once $path.'/'.$script[0].'.php';
    if(!class_exists('Script')) die('The script does not include a "Class Script'."\nUse:\n-------\n<?php\nclass Script extends Scripts {\n\tfunction main() { }\n}\n-------\n\n");
    /** @var Script $script */

    $run = new Script($core,$argv);
    $run->params = $script;

    if(strlen($formParams))
        parse_str($formParams,$run->formParams);
    
    if(!method_exists($run,'main')) die('The class Script does not include the method "main()'."\n\n");
}
try {
    $core->__p->add('Running Script',$argv[1],"note");
    $run->main();
    $core->__p->add('Running Script','',"endnote");

} catch (Exception $e) {

    $run->addError(error_get_last());
    $run->addError($e->getMessage());
}
echo "\n------------------------------\n";
if($core->errors->lines) {
    $run->sendTerminal(['errors'=>$core->errors->data]);
    $run->sendTerminal('Script: Error');
}
else $run->sendTerminal('Script: OK');
if($core->logs->lines) $run->sendTerminal(['logs'=>$core->logs->data]);
if($options['performance']) $run->sendTerminal($core->__p->data['info']);
