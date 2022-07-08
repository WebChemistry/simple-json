<?php declare(strict_types = 1);

namespace WebChemistry\SimpleJson\Exception;

use LogicException;
use Throwable;

final class SimpleJsonUnexpectedEnd extends LogicException
{

	public function __construct(string $message = 'Unexpected end of string.', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}
