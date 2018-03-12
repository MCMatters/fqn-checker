## PHP Fqn checker

Checks your php-code for the presence of un-imported functions and gives you information about where they are located.

### Installation

```bash
composer require mcmatters/fqn-checker
```

### Usage

```php
<?php

declare(strict_types = 1);

use McMatters\FqnChecker\FqnChecker;

require 'vendor/autoload.php';

$checker = new FqnChecker(file_get_contents(__DIR__.'/Wrong.php'));

print_r($checker->getUnimported());
print_r($checker->getImported());
```

Listing of **Wrong.php**

```php
<?php

declare(strict_types = 1);

namespace Acme;

use function ucfirst;

class Wrong
{
    public function testArray()
    {
        return array_filter([]);
    }
    
    public function testString()
    {
        return ucfirst('hello');
    }
}
```

**Result**
```text
Array
(
    [0] => Array
        (
            [line] => 11
            [function] => array_filter
        )
)

Array
(
    [ucfirst] => true
)
```
