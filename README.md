#  Eloquent for FastD

BASE: [illuminate/database](https://github.com/illuminate/database).


## Install

```
composer require zhaqq/fastd-eloquent
```

## Usage


> vim  `config/app.conf`

```php
<?php

return [
    'services' => [
        // ...
        Zhaqq\Eloquent\EloquentServiceProvider::class,
    ],
];
```
