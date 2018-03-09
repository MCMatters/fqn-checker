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

print_r(FqnChecker::check(file_get_contents(__DIR__.'/Wrong.php')));
```

Listing of **Wrong.php**

```php
<?php

declare(strict_types = 1);

namespace Acme;

class Wrong
{
    public function test()
    {
        return array_filter([]);
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
```
