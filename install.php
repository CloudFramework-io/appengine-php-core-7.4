<?php
$_root_path = (strlen($_SERVER['DOCUMENT_ROOT'])) ? $_SERVER['DOCUMENT_ROOT'] : $_SERVER['PWD'];

echo "---------\n";
echo "Installing CloudFramework GCP for PHP 7.3\n";
echo "---------\n";

echo " - Copying /api examples\n";
if(!is_dir("./api")) mkdir('api');
shell_exec("cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/api-dist/* api");

echo " - Copying /scripts examples\n";
if(!is_dir("./scripts")) mkdir('scripts');
shell_exec("cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/scripts-dist/* scripts");

if(!is_file('./composer.json')) {
    echo " - Copying composer.json\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/composer-dist.json", "./composer.json");
} else echo " - Already exist composer.json\n";

if(!is_file('./config.json')) {
    echo " - Copying composer.json\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/config-dist.json", "./config.json");
} else echo " - Already exist config.json\n";

if(!is_file('./app.json')) {
    echo " - Copying app.json\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/app-dist.yaml", "./app.json");
} else echo " - Already exist app.json\n";

if(!is_file('./.gitignore')) {
    echo " - Copying .gitignore\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/.gitignore", "./.gitignore");
} else echo " - Already exist .gitignore\n";

if(!is_file('./.gcloudignore')) {
    echo " - Copying .gcloudignore\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/.gcloudignore", "./.gcloudignore");
} else echo " - Already exist .gcloudignore\n";

if(!is_file('./README.md')) {
    echo " - Copying README.md\n";
    copy("vendor/cloudframework-io/appengine-php-core-7.3/README-dist.md", "./README.md");
} else echo " - Already exist README.md\n";


/*
cp vendor/cloudframework-io/appengine-php-core-7.3/composer-dist.json composer.json
cp vendor/cloudframework-io/appengine-php-core-7.3/config-dist.json config.json
cp vendor/cloudframework-io/appengine-php-core-7.3/app-dist.yaml app.yaml
cp vendor/cloudframework-io/appengine-php-core-7.3/.gitignore .
cp vendor/cloudframework-io/appengine-php-core-7.3/.gcloudignore .
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/api-dist api
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/scripts-dist scripts
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/README-dist.md README.md*/
