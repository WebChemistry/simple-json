<?php declare(strict_types = 1);

use Tester\Assert;
use WebChemistry\SimpleJson\Exception\SimpleJsonSyntaxError;

require __DIR__ . '/bootstrap.php';

/**
 * @param string[] $expecting
 */
function syntaxError(string $json, array $expecting = [], ?string $got = null, ?string $expectingMessage = null): void
{
	$expectingMessage ??= (SimpleJsonSyntaxError::create($expecting, $got))->getMessage();

	Assert::exception(fn () => parse($json), SimpleJsonSyntaxError::class, $expectingMessage);
}

$value = '<value>';
$end = '<end>';

syntaxError('{foo', ['}', ',']);
syntaxError('[foo', [']', ',']);
syntaxError('{foo,', ['}', $value]);
syntaxError('foo', ['[', '{'], 'foo');
syntaxError('{foo bar', [',', ':', '}'], 'bar');
syntaxError('{foo:', [$value]);
syntaxError('{ bar } foo', [$end], 'foo');

syntaxError('{ 42.42: bar }', expectingMessage: 'Objects cannot have float as keys.');
syntaxError('[ foo: bar ]', expectingMessage: 'Lists cannot have keys.');
syntaxError('{ { bar: foo }: { foo: bar } }', expectingMessage: 'Objects cannot have object as key.');
