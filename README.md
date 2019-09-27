# PHP Array Parser
A small library to parse text representations of a PHP array and return an actual PHP array.

## Installation

Run `composer install` to run this script (and tests) in a standalone way. Alternatively, this can be used as a dependency in another project by running `composer require battye/php-array-parser "~1.0"`.

Reference the namespace at the top of your PHP files to utilise the included classes:

```php
use battye\array_parser\parser;
use battye\array_parser\tokens;
```

If you notice any bugs, please raise an issue or pull request.

## Example

In both of the following examples, `$result` would contain a PHP array containing the representation of the string or text file provided.

### Raw String

To parse a simple array is very easy:

```php
$value = "array(0 => array('one' => 1, 'two' => 'two'));";
$result = parser::parse_simple($value);
```

In this case, `$result` would produce the following:

    array(1) {
      [0] =>
      array(2) {
        'one' =>
        int(1)
        'two' =>
        string(3) "two"
      }
    }

### Regex

Regular expressions can also be used to parse complex files and extract array values:

```php
$regex = '/\$lang\s+=\s+array_merge\(\$lang, array\((.*?)\)\);/s';
$file = __DIR__ . '/files/test_lang.php';
$result = parser::parse_regex($regex, $file);
```

## Tests

[![Latest Stable Version](https://poser.pugx.org/battye/php-array-parser/v/stable)](https://packagist.org/packages/battye/php-array-parser) [![Build Status](https://travis-ci.com/battye/php-array-parser.svg?branch=master)](https://travis-ci.com/battye/php-array-parser) [![Total Downloads](https://poser.pugx.org/battye/php-array-parser/downloads)](https://packagist.org/packages/battye/php-array-parser)

The unit tests provide good examples of how to utilise this library and can be found in the `tests/` directory. To execute the unit tests, run:

    vendor/bin/simple-phpunit tests/
