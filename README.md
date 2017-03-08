# Openings

This package provides basic functionality to determine openings such as shop or restaurant openings.

## Features

- Provide openings in an easy array syntax
- Handle exceptional openings such als holidays, christmas etc.

## Installation

```
composer require crtl/openings
```

## Examples / Usage

### Format

Openings and opening exceptions must have the following format:

```php
[
    "key" => ["H:i-H:i", "H:i-H:i"],
    "key1, key1 , keyN" => ["H:i-H:i"],
    "key-key" => ["H:i-H:i"]
]
```

Where **key** is one of the following date formats:

- `D` for openings
- `Y/m/d` for opening exceptions

### Usage

```php

<?php

use Crtl\Openings\OpeningsManager;
use Crtl\Openings\Exceptions\InvalidFormatException;

require_once(__DIR__ . "/vendor/autoload.php");

//Define openings
$openings = [
    "Mon-Fri" => ["08:00-12:00", "14:30-19:00"],
    "Sat" => ["10:00-16:00"],
    "Sun" => [] //Closed, you can also asign any other false value
];

//Define opening exceptions
$exceptions = [
    "2017/12/24-2018/01/01" => [], //Closed  from 2017/12/24 to 2018/01/01
    "2018/01/02" => ["10:00-14:00"]
];

//Create instance
try {
    $openingsManager = new OpeningsManager($openings, $exceptions);
}
catch (InvalidFormatException $ex) {
    die($ex->getMessage()); //Invalid format supplied either  for keys or values
}

$openingsManager->isOpen() //same as $openingsManager->isOpen(new DateTime());
$openingsManager->isOpen($myDateTime);

$date = new \DateTime("2017-12-24 10:00:00");
$openingsManager->isOpen($date) 

$nextWeek = new \DateTime("09:00:00");
$nextWeek->add(new \DateInterval("P1W"));

$openingsManager->isOpen($nextWeek);

```