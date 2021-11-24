cp vendor/cloudframework-io/appengine-php-core-7.3/composer-dist.json composer.json
cp vendor/cloudframework-io/appengine-php-core-7.3/config-dist.json config.json
cp vendor/cloudframework-io/appengine-php-core-7.3/app-dist.yaml app.yaml
cp vendor/cloudframework-io/appengine-php-core-7.3/.gitignore .
cp vendor/cloudframework-io/appengine-php-core-7.3/.gcloudignore .
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/api-dist api
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/scripts-dist scripts
cp -Ra vendor/cloudframework-io/appengine-php-core-7.3/README-dist.md README.md
composer develop