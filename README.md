# PHP Array Parser
A small library to parse text representations of a PHP array and return an actual PHP array.

## Installation

Run `composer install` to run this script (and tests) in a standalone way. Alternatively, this can be used as a dependency in another project by running `composer require battye/php-array-parser "~1.0"`.

If you notice any bugs, please feel free to raise an issue or pull request.

## Example

In both of the following examples, `$result` would contain a PHP array containing the representation of the string or text file provided.

### Raw String

To parse a simple array is very easy:

```php
$string = "array(0 => array('one' => 1, 'two' => 'two'));";
$tokens = new tokens($string);
$parser = new parser($tokens);
$result = $parser->parse_array();
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

[![Build Status](https://travis-ci.com/battye/php-array-parser.svg?branch=master)](https://travis-ci.com/battye/php-array-parser)

The unit tests provide good examples of how to utilise this library and can be found in the `tests/` directory. To execute the unit tests, run:

    vendor/bin/simple-phpunit tests/
