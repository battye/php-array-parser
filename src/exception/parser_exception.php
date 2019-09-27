<?php
/**
 * Class parser_exception
 * Created by battye
 */

namespace battye\array_parser\exception;

class parser_exception extends \Exception
{
	/**
	 * Use the parser_exception constructor to make the message a little nicer.
	 * @param $message
	 */
	public function __construct($message)
	{
		parent::__construct('[php-array-parser] ' . $message);
	}
}
