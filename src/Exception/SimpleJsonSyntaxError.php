<?php declare(strict_types = 1);

namespace WebChemistry\SimpleJson\Exception;

use Exception;

final class SimpleJsonSyntaxError extends Exception
{

	/**
	 * @param string[] $expecting
	 */
	public static function create(array $expecting, ?string $got = null): self
	{
		return new self(sprintf(
			'Unexpected %s. Expecting %s',
			$got === null ? 'end of string' : sprintf('"%s"', $got),
			implode(', ', array_map(
				fn (string $expect) => str_starts_with($expect, '<') ? $expect : sprintf('"%s"', $expect),
				$expecting
			))
		));
	}

}
