# JSON Config

![Build Status](http://teamcity.rlx.by/app/rest/builds/buildType:(id:JsonConfig_Master)/statusIcon?guest=1)

## Installation via Composer

```json
{
    "require": {
        "artox-lab/json-config": "1.1"
    }
}
```

Run ```composer update```

## Config Definition

***config.json***

```json
{
  "name": "Suppa Project",
  "version": 1,
  "rating": 2.5,
  "authors": [
    "code.god@gmail.com"
  ],
  "emails": {
    "subscription": [
      "user1@gmail.com",
      "user2@gmail.com"
    ],
    "user": {
      "name": "Johny"
    },
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