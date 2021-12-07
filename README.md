# appengine-php-core-7.4
CloudFrameWork.io / APPENGINE PHP 7.4 Framework
```
composer require cloudframework-io/appengine-php-core-7.4
```

## Google Cloud
* https://cloud.google.com/appengine/docs/standard/php7/runtime?hl=id

## Package
https://packagist.org/packages/cloudframework-io/appengine-php-core-7.4

## Memorystore. Install REDIS to manage Memory Cache
In php7 we have lost MemoryCache :(. To solve it Google provide a 
solution call Memorystore: 
 * https://console.cloud.google.com/marketplace/details/google/redis.googleapis.com.
 * https://cloud.google.com/appengine/docs/standard/php7/using-memorystore

It is a REDIS server that can also be installed in localhost.
You can read more about it in:
 * https://medium.com/@petehouston/install-and-config-redis-on-mac-os-x-via-homebrew-eb8df9a4f298
 * https://redis.io/topics/quickstart

### Configure in Google Cloud a VPC to connect your redis server
https://cloud.google.com/appengine/docs/standard/php7/connecting-vpc#configuring 

### Install REDIS in localhost
```
brew install redis
```
Manual launch (stand alone)
```
redis-server /usr/local/etc/redis.conf
```

In Background 
```
brew services start redis
```
### Install the php library
```shell script
sudo pecl install redis-5.2.0
```

### Setup Env Vars for localhost
```
export REDIS_HOST=127.0.0.1
export REDIS_PORT=6379
```

### Setup app.yaml vars for Google Cloud
```
env_variables:
  REDIS_HOST: "10.*****"
  REDIS_PORT: "6379"
```

### Using it in CloudFramework
```
$core->cache->get/set/delete
```

