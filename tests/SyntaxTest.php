<?php declare(strict_types = 1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

// test ending commas
Assert::same(['foo'], parse('{ foo, }'));
Assert::same(['foo'], parse('[ foo, ]'));
Assert::same([['foo'], ['bar']], parse('{ {foo}, {bar}, }'));
