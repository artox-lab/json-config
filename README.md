# JSON Config

## Installation via Composer

```json
{
    "require": {
        "artox-lab/json-config": "1.0"
    }
}
```

run ```composer update```

## Config Definition

***config.json***

```json
{
    "name": "Suppa Project",
    "version": 1,
    "authors": [
    	"code.god@gmail.com"
    ],
    "emails": {
    	"subscription": [
    		"user1@gmail.com",
    		"user2@gmail.com"
    	],
    	"should_notify": true
    }
}
```

## Config Usage

***index.php***
```php
<?php

include 'vendor/autoload.php';

// Setup path to config file
JsonConfig\Config::setup('config.json');

// Get data from config
$subscriptions = JsonConfig\Config::get('emails.subscription');

```