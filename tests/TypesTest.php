<?php declare(strict_types = 1);

use Tester\Assert;

require __DIR__ . '/bootstrap.php';

Assert::same(['string'], successful('{ string }'));
Assert::same(['string'], successful('{ "string" }'));
Assert::same(['string'], successful("{ 'string' }"));
Assert::same([42], successful('{ 42 }'));
Assert::same([true], successful('{ true }'));
Assert::same([true], successful('{ TRUE }'));
Assert::same([false], successful('{ false }'));
Assert::same([false], successful('{ FALSE }'));
Assert::same([null], successful('{ null }'));
Assert::same([null], successful('{ NULL }'));
Assert::same(['False'], successful('{ False }'));
Assert::same([[]], successful('{ {} }'));
Assert::same([[]], successful('{ [] }'));
