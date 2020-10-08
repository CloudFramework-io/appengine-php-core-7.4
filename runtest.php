<?php
$rootPath = exec('pwd');
// Autoload libraries
require_once  $rootPath.'/vendor/autoload.php';

include_once __DIR__.'/src/Core7.php';
$core = new Core7($rootPath);


// Check test exist
if(true) {

    $script = [];

    $path='';
    $formParams = '';
    if(count($argv)>1) {
        if(strpos($argv[1],'?'))
            list($script, $formParams) = explode('?', str_replace('..', '', $argv[1]), 2);
        else {
            $script = $argv[1];
            $formParams = '';
        }
        $script = explode('/', $script);

    }

    echo "CloudFramworkTest v202010\nroot_path: {$rootPath}\n";
    echo "------------------------------\n";


    include_once __DIR__.'/scripts/_test.php';
    if(!class_exists('Script')) die('The script does not include a "Class Script'."\nUse:\n-------\n<?php\nclass Script extends Scripts {\n\tfunction main() { }\n}\n-------\n\n");
    /** @var Script $script */

    $run = new Script($core,$argv);
    $run->params = $script;

    if(strlen($formParams))
        parse_str($formParams,$run->formParams);

    if(!method_exists($run,'main')) die('The class Script does not include the method "main()'."\n\n");
}

try {
    $run->main();
} catch (Exception $e) {
    $run->sendTerminal(error_get_last());
    $run->sendTerminal($e->getMessage());
}
echo "------------------------------\n";
if($core->errors->data) {
    $run->sendTerminal('Test: ERROR');
}
else $run->sendTerminal('Test: OK');
