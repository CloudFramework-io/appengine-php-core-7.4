<?php
$_root_path = (strlen($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['PWD'];

echo "---------\n";
echo "Installing CloudFramework GCP for PHP 7.4\n";
echo "---------\n";

echo " - mkdir ./local_data/cache\n";
if(!is_dir("./local_data")) mkdir($_root_path.'/local_data');
if(!is_dir("./local_data/cache")) mkdir($_root_path.'/local_data/cache');
if(!is_dir("./local_data/cache")) die('ERROR trying to create [./local_data/cache]. Verify privileges');


echo " - Copying /api examples\n";
if(!is_dir("./api")) mkdir('api');
shell_exec("cp -Ra vendor/cloudframework-io/appengine-php-core-7.4/api-dist/* api");

echo " - Copying /scripts examples\n";
if(!is_dir("./scripts")) mkdir('scripts');
shell_exec("cp -Ra vendor/cloudframework-io/appengine-php-core-7.4/scripts-dist/* scripts");

if(!is_file('./composer.json')) {
    echo " - Copying composer.json\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/composer-dist.json", "./composer.json");
} else echo " - Already exist composer.json\n";

if(!is_file('./config.json')) {
    echo " - Copying composer.json\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/config-dist.json", "./config.json");
} else echo " - Already exist config.json\n";

if(!is_file('./app.yaml')) {
    echo " - Copying app.yaml\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/app-dist.yaml", "./app.yaml");
} else echo " - Already exist app.yaml\n";

if(!is_file('./.gitignore')) {
    echo " - Copying .gitignore\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/.gitignore", "./.gitignore");
} else echo " - Already exist .gitignore\n";

if(!is_file('./.gcloudignore')) {
    echo " - Copying .gcloudignore\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/.gcloudignore", "./.gcloudignore");
} else echo " - Already exist .gcloudignore\n";

if(!is_file('./README.md')) {
    echo " - Copying README.md\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.4/README-dist.md", "./README.md");
} else echo " - Already exist README.md\n";


/*
cp vendor/cloudframework-io/appengine-php-core-7.4/composer-dist.json composer.json
cp vendor/cloudframework-io/appengine-php-core-7.4/config-dist.json config.json
cp vendor/cloudframework-io/appengine-php-core-7.4/app-dist.yaml app.yaml
cp vendor/cloudframework-io/appengine-php-core-7.4/.gitignore .
cp vendor/cloudframework-io/appengine-php-core-7.4/.gcloudignore .
cp -Ra vendor/cloudframework-io/appengine-php-core-7.4/api-dist api
cp -Ra vendor/cloudframework-io/appengine-php-core-7.4/scripts-dist scripts
cp -Ra vendor/cloudframework-io/appengine-php-core-7.4/README-dist.md README.md*/
