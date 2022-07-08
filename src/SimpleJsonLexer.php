<?php declare(strict_types = 1);

namespace WebChemistry\SimpleJson;

use Doctrine\Common\Lexer\AbstractLexer;

final class SimpleJsonLexer extends AbstractLexer
{

	public const T_INT = 0;
	public const T_FLOAT = 1;
	public const T_STRING = 2;
	public const T_OPEN_BRACE = 3;
	public const T_CLOSE_BRACE = 4;
	public const T_COMMA = 5;
	public const T_COLON = 6;
	public const T_UNKNOWN = 9999;

	protected function getCatchablePatterns()
	{
		return [
			'[a-zA-Z_][\w?]+', // identifiers
			'(?:[0-9]+(?:[\.][0-9]+)?)', // numbers
			"'(?:[^'])*'", // quoted strings
			'"(?:[^"])*"', // quoted strings
		];
	}

	protected function getNonCatchablePatterns()
	{
		return ['\s+', '(.)'];
	}

	/**
	 * @param string $value
	 */
	protected function getType(&$value)
	{
		if (is_numeric($value)) {
			if (str_contains($value, '.')) {
				return self::T_FLOAT;
			}

			return self::T_INT;
		}

		if (str_starts_with($value, "'")) {
			$value = substr($value, 1, -1);

			return self::T_STRING;
		}

		if (str_starts_with($value, '"')) {
			$value = substr($value, 1, -1);

			return self::T_STRING;
		}

		return match ($value) {
			'{', '[' => self::T_OPEN_BRACE,
			'}', ']' => self::T_CLOSE_BRACE,
			',' => self::T_COMMA,
			':' => self::T_COLON,
			default => self::T_UNKNOWN,
		};
	}

}
