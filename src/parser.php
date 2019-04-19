<?php
/**
 * Class parser
 * Parser for simple PHP arrays
 * Modified by battye, originally from: http://jsteemann.github.io/blog/2015/06/16/parsing-php-arrays-with-php/
 */

namespace battye\array_parser;

class parser
{
	const NOT_STRING_LITERAL = '-- Not a supported string literal (unprocessed value). --';

	private static $CONSTANTS = array(
		'null' => null,
		'true' => true,
		'false' => false
	);

	private $tokens;

	public function __construct(tokens $tokens)
	{
		$this->tokens = $tokens;
	}

	/**
	 * Simple and generic regex parser, made with phpBB in mind.
	 * @param $regex
	 * @param $path
	 * @param int $group capture group
	 * @return array|null
	 */
	public static function parse_regex($regex, $path, $group = 1)
	{
		// Find a regex match
		$result = null;
		$file = file_get_contents($path);
		preg_match_all($regex, $file, $matches);

		$multiple_matches = count($matches[$group]);

		$parse = function($match) {
			$tokens = new tokens($match, true);
			$parser = new parser($tokens);

			$result = $parser->parse_array();

			if (!$tokens->done())
			{
				throw new \Exception("Still tokens left after parsing.");
			}

			return $result;
		};

		// Return the results in an array
		$result = [];

		if ($multiple_matches > 0)
		{
			foreach ($matches[$group] as $match)
			{
				$result[] = $parse($match);
			}
		}

		return $result;
	}

	public function parse_value()
	{
		// Ignore values that rely on another variable
		if (!$this->tokens->done())
		{
			if (is_array($this->tokens->peek()) && count($this->tokens->peek()) > 1 && substr($this->tokens->peek()[1], 0, 1) === "$")
			{
				// If a variable is being used as a value then don't bother with this any more
				$literalValue = self::NOT_STRING_LITERAL;

				while (!$this->tokens->does_match(","))
				{
					if ($this->tokens->does_match(")"))
					{
						return $literalValue;
					}

					$this->tokens->pop(); // Ignore comments
				}

				return $literalValue;
			}
		}

		if ($this->tokens->does_match(T_CONSTANT_ENCAPSED_STRING))
		{
			// Strings
			$token = $this->tokens->pop();
			return stripslashes(substr($token[1], 1, -1));
		}

		if ($this->tokens->does_match(T_STRING))
		{
			// Built-in string literals: null, false, true
			$token = $this->tokens->pop();
			$value = strtolower($token[1]);

			if (array_key_exists($value, self::$CONSTANTS))
			{
				return self::$CONSTANTS[$value];
			}

			throw new \Exception("Unexpected string literal " . $token[1]);
		}

		else if ($this->tokens->does_match(T_ARRAY) || $this->tokens->does_match('['))
		{
			$square_bracket_array = $this->tokens->does_match('[');
			return $this->parse_array($square_bracket_array);
		}

		// The rest...
		// We expect a number here
		$uminus = 1;

		if ($this->tokens->does_match("-"))
		{
			// Unary minus
			$this->tokens->force_match("-");
			$uminus = -1;
		}

		if ($this->tokens->does_match(T_LNUMBER))
		{
			// Long number
			$value = $this->tokens->pop();
			return $uminus * (int) $value[1];
		}

		if ($this->tokens->does_match(T_DNUMBER))
		{
			// Double number
			$value = $this->tokens->pop();
			return $uminus * (double) $value[1];
		}

		throw new \Exception("Unexpected value token");
	}

	public function ignore_comments()
	{
		if (!$this->tokens->done())
		{
			// Check for both short and long form comment blocks
			while (is_array($this->tokens->peek()) && count($this->tokens->peek()) > 1 && $this->is_comment_block($this->tokens->peek()[1]))
			{
				$this->tokens->pop(); // Ignore comments
			}
		}
	}

	public function is_comment_block($code)
	{
		// Short form
		$short = (substr($code, 0, 2) === "//");

		// Long form block
		$long = (substr($code, 0, 2) == '/*' && substr($code, -2) == '*/');

		return ($short || $long);
	}

	public function parse_array($square = false)
	{
		$found = 0;
		$result = array();

		if ($square)
		{
			$this->tokens->force_match("[");
		}

		else
		{
			$this->tokens->force_match(T_ARRAY);
			$this->tokens->force_match("(");
		}

		while (true)
		{
			if ($this->tokens->does_match(")") || $this->tokens->does_match("]"))
			{
				// Reached the end of the array
				if ($square)
				{
					$this->tokens->force_match("]");
				}

				else
				{
					$this->tokens->force_match(")");
				}

				// If we are done, just break immediately
				if ($this->tokens->done())
				{
					break;
				}

				if ($this->tokens->does_match(";"))
				{
					$this->tokens->force_match(";");
				}

				break;
			}

			if ($found > 0)
			{
				// We must see a comma following the first element
				$this->tokens->force_match(",");
				$this->ignore_comments();

				if ($square && $this->tokens->does_match("]"))
				{
					// end of the square bracket array
					$this->tokens->force_match("]");
					break;
				}

				if ($this->tokens->does_match(")"))
				{
					// reached the end of the array
					$this->tokens->force_match(")");
					break;
				}

			}

			$this->ignore_comments();

			if ($this->tokens->does_match(T_ARRAY) || $this->tokens->does_match('['))
			{
				$square_bracket_array = $this->tokens->does_match('[');

				// Nested array
				$result[] = $this->parse_array($square_bracket_array);
			}

			else if ($this->tokens->does_match(T_CONSTANT_ENCAPSED_STRING) || $this->tokens->does_match(T_LNUMBER))
			{
				// String
				$string = $this->parse_value();

				if ($this->tokens->does_match(T_DOUBLE_ARROW))
				{
					// Array key (key => value)
					$this->tokens->pop();
					$result[$string] = $this->parse_value();
				}

				else
				{
					// simple string
					$result[] = $string;
				}
			}

			else
			{
				$result[] = $this->parse_value();
			}

			++$found;
		}

		return $result;
	}
}
