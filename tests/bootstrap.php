<?php declare(strict_types = 1);

use Tester\Environment;
use WebChemistry\SimpleJson\SimpleJsonParser;

require __DIR__ . '/../vendor/autoload.php';

Environment::setup();

/**
 * @return array<string|int, int|float|string|bool|mixed[]>
 */
function successful(string $json): array
{
	return SimpleJsonParser::parse($json);
}

/**
 * @return array<string|int, int|float|string|bool|mixed[]>
 */
function parse(string $json): array
{
	return SimpleJsonParser::parse($json);
}
