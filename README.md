# PHP Array Parser
A small library to parse text representations of a PHP array and return an actual PHP array.

## Installation

Run `composer install` to run this script (and tests) in a standalone way. Alternatively, this can be used as a dependency in another project.

If you notice any bugs, please feel free to raise an issue or pull request.

## Example

In both of the following examples, `$result` would contain a PHP array containing the representation of the string or text file provided.

### Raw String

    $string = "array(0 => array('one' => 1, 'two' => 'two'));";
    $tokens = new tokens($string);
    $parser = new parser($tokens);
    $result = $parser->parse_array();

### Regex

    $regex = '/\$lang\s+=\s+array_merge\(\$lang, array\((.*?)\)\);/s';
    $file = __DIR__ . '/files/test_lang.php';
    $result = parser::parse_regex($regex, $file);

## Tests

The unit tests provide good examples of how to utilise this library and can be found in the `tests/` directory. To execute the unit tests, run:

    vendor/bin/simple-phpunit tests/