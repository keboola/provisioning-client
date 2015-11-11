# Keboola Provisioning Client

Get Credentials from Provisioning API.


## Usage

### Getting Credentials

If you have already an instance of [Storage API client](https://github.com/keboola/storage-api-php-client) in `$storageApi`, then you can get credentials to transformation database with the following call:
```
$provisioning = new \Keboola\Provisioning\Client('redshift', $storageApi->getTokenString(), $storageApi->getRunId());
$credentials = $provisioning->getCredentials('transformations');
```

First argument to `Client` constructor is database backend, which may be either *mysql* or *redshift*. The `$credentials` variable above will contain the following structure:

```
array (
    'id' => 'foo',
    'hostname' => 'ACMEdatabseServerAddress',
    'db' => 'ACMEDatabse',
    'password' => 'ACMEPassword',
    'user' => 'Wile.E.Coyote',
    'schema' => 'ACMESchema',
)
```

### Resetting Credentials

Resetting credentials is useful when you want to clean up the working schema. Resetting credentials will drop the entire schema and create a new empty one. Resetting credentials does not delete the credentials themselves - i.e. password and user name may remain the same. If you have already an instance of [Storage API client](https://github.com/keboola/storage-api-php-client) in `$storageApi`, then you can get credentials to transformation database with the following call:
```
// get current credentials
$provisioning = new \Keboola\Provisioning\Client('redshift', $storageApi->getTokenString(), $storageApi->getRunId());
$credentials = $provisioning->getCredentials('transformations');

$provisioning->dropCredentials($credentials['id']);

// get new credentials
$credentials = $provisioning->getCredentials('transformations');
```


## Installation

Library is available as composer package.
To start using composer in your project follow these steps:

**Install composer**

```bash
curl -s http://getcomposer.org/installer | php
mv ./composer.phar ~/bin/composer # or /usr/local/bin/composer
```

**Create composer.json file in your project root folder:**

```json
{
    "require": {
        "php" : ">=5.3.2",
        "keboola/provisioning-client": "0.3.*"
    }
}
```

**Install package:**

```bash
composer install
```


**Add autoloader in your bootstrap script:**

```bash
require 'vendor/autoload.php';
```


Read more in [Composer documentation](http://getcomposer.org/doc/01-basic-usage.md)
