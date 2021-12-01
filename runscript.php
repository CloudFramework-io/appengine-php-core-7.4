<?php
//region SET $root_path
$rootPath = exec('pwd');
// Autoload libraries
require_once  $rootPath.'/vendor/autoload.php';
//endregion

//region CREATE $core
include_once __DIR__.'/src/Core7.php';
$core = new Core7($rootPath);
//endregion

//region EVALUATE create $datastore
// Load DataStoreClient to optimize calls
use Google\Cloud\Datastore\DatastoreClient;
$datastore = null;
if((getenv('PROJECT_ID') || $core->config->get("core.gcp.datastore.project_id")) && $core->config->get('core.datastore.on')) {
    $transport = ($core->config->get('core.datastore.transport'))?:'rest';
    $datastore = new DatastoreClient(['transport'=>$transport,'projectId'=>($core->config->get("core.gcp.datastore.project_id"))?:getenv('PROJECT_ID')]);
}
//endregion

//region SET $script, $script_name, $path, $show_path
$script = [];
$path='';
if(count($argv)>1) {
    if(strpos($argv[1],'?'))
        list($script, $formParams) = explode('?', str_replace('..', '', $argv[1]), 2);
    else {
        $script = $argv[1];
        $formParams = '';
    }
    $script = explode('/', $script);
    $script_name = $script[0];
    if($script_name[0]=='_') {
        $path = __DIR__.'/scripts';
    } else {
        $path =($core->config->get('core.scripts.path')?$core->config->get('core.scripts.path'):$core->system->app_path.'/scripts');
    }
    if(is_dir($path.'/'.$script_name)) {
        $path.="/{$script[0]}";
        $script_name =(isset($script[1]))?$script[1]:null;
    }
}
$show_path = str_replace($rootPath,'.',$path);
//endregion

//region CHECK if $script_name is empty
echo "CloudFramwork Script v21.11\nroot_path: {$rootPath}\napp_path: {$show_path}\n";
if(!$script_name) die ('Missing Script name: Use php vendor/cloudframework-io/appengine-php-core/runscript.php {script_name}[/params[?formParams]] [--options]'."\n\n");
echo "Script: {$show_path}/{$script_name}.php\n";
//endregion

//region LOAD local_script.json
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
//endregion

//region SET $options,
$options = ['performance'=>in_array('--p',$argv)];
//endregion

//region VERIFY if the script exist
if(!is_file($path.'/'.$script_name.'.php')) die("Script not found. Create it with: composer script _create/<your-script-name>\n");
include_once $path.'/'.$script_name.'.php';
if(!class_exists('Script')) die('The script does not include a "Class Script'."\nUse:\n-------\n<?php\nclass Script extends Scripts2020 {\n\tfunction main() { }\n}\n-------\n\n");
/** @var Script $script */
//endregion

//region SET $run = new Script($core,$argv); and verify if the method main exist
$run = new Script($core,$argv);
$run->params = $script;
if(strlen($formParams))
    parse_str($formParams,$run->formParams);

if(!method_exists($run,'main')) die('The class Script does not include the method "main()'."\n\n");
//endregion

//region TRY $run->main();
try {
    $core->__p->add('Running Script',$show_path.'/'.$script_name,"note");
    $run->main();
    $core->__p->add('Running Script','',"endnote");

} catch (Exception $e) {

    $run->addError(error_get_last());
    $run->addError($e->getMessage());
}
echo "\n------------------------------\n";
//endregion

//region EVALUATE to show logs and errors and end the scrpit
if($core->errors->lines) {
    $run->sendTerminal(['errors'=>$core->errors->data]);
    $run->sendTerminal('Script: Error');
}
else $run->sendTerminal('Script: OK');
if($core->logs->lines) $run->sendTerminal(['logs'=>$core->logs->data]);
if($options['performance']) $run->sendTerminal($core->__p->data['info']);
//endregion
