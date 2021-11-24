# Development
Following [CloudFramework Instructions](https://www.notion.so/cloudframework/appengine-php-core20-74c573448dc94ebba7e51fc86b8ad9cb) to start programming in localhost execute:
```shell
# lines you have executed
composer require cloudframework-io/appengine-php-core-7.3
sh vendor/cloudframework-io/appengine-php-core-7.3/copy-basic-files-to-start.sh

# create temporal local data directory: ./local_data/cache
composer develop

# To work with APIs. Try now http://localhost:8080/training/hello
composer serve

# To work with Scripts. Try now: composer script hello
composer script {name-of-script-under-the-directory-/scripts-wihout-.php}
```

# Deploy your project
Comming soon.