# blesta/Proration

Proration Calculator

## Installation

Install via composer:

```js
    "repositories": [
        {
            "type": "package",
            "package": {
                "name": "blesta/proration",
                "version": "dev-master",
                "dist": {
                    "url": "http://git.blestalabs.com/billing/proration/repository/archive.zip",
                    "type": "zip"
                },
                "source": {
                    "url": "http://git.blestalabs.com/billing/proration",
                    "type": "git",
                    "reference": "tree/master"
                }
            }
        }
    ]
```
```sh
composer require blesta/proration:dev-master
```

## Basic Usage
```php
require "path/to/vendor/blesta/proration/src/autoload.php";

$start_date = date('c');
$prorate_day = 1;
$term = 1;
$period = "month";
$proration = new Proration($start_date, $prorate_day, $term, $period);

echo $proration->prorateDate();
echo $proration->canProrate();
echo $proration->prorateDays();
echo $proration->proratePrice(5.00);
```