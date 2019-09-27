<?php
/**
 * Class tokens
 * To manage tokens
 * Modified by battye, originally from: http://jsteemann.github.io/blog/2015/06/16/parsing-php-arrays-with-php/
 */

namespace battye\array_parser;

use battye\array_parser\exception\parser_exception;

class tokens
{
	private $tokens;

	/**
	 * tokens constructor.
	 * @param $code
	 * @param bool $key_values_only
	 * @throws parser_exception
	 */
	public function __construct($code, $key_values_only = false)
	{
		// Add the array keyword if we've only got keys and values
		if ($key_values_only)
		{
			$code = sprintf('array(%s)', $code);
		}

		// Construct PHP code from string and tokenize it
		$tokens = token_get_all("<?php " . $code);

		// Kick out whitespace tokens
		$this->tokens = array_filter($tokens, function ($token) {
			return (!is_array($token) || $token[0] !== T_WHITESPACE);
		});

		// Remove start token (<?php)
		$this->pop();
	}

	/**
	 * Finished parsing tokens
	 * @return bool
	 */
	public function done()
	{
		return count($this->tokens) === 0;
	}

	/**
	 * Consume the token and return it
	 * @return mixed
	 * @throws parser_exception
	 */
	public function pop()
	{
		if ($this->done())
		{
			throw new parser_exception('Already at the end of tokens.');
		}

		return array_shift($this->tokens);
	}

	/**
	 * Return next token, don't consume it
	 * @return mixed
	 * @throws parser_exception
	 */
	public function peek()
	{
		if ($this->done())
		{
			throw new parser_exception('Already at the end of tokens.');
		}

		return $this->tokens[0];
	}

	/**
	 * Check if the next token matches what is expected
	 * @param $what
	 * @return bool
	 * @throws parser_exception
	 */
	public function does_match($what)
	{
		$token = $this->peek();

		if (is_string($what) && !is_array($token) && $token === $what)
		{
			return true;
		}

		if (is_int($what) && is_array($token) && $token[0] === $what)
		{
			return true;
		}

		return false;
	}

	/**
	 * Pop the token if it is a match
	 * @param $what
	 * @throws parser_exception
	 */
	public function force_match($what)
	{
		if (!$this->does_match($what))
		{
			if (is_int($what))
			{
				throw new parser_exception('Unexpected token - expecting ' . token_name($what));
			}
			
			throw new parser_exception('Unexpected token - expecting ' . $what);
		}

		// Consume the token
		$this->pop();
	}
}
