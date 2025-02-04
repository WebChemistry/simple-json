<?php declare(strict_types = 1);

namespace WebChemistry\SimpleJson;

use Doctrine\Common\Lexer\Token;
use LogicException;
use WebChemistry\SimpleJson\Exception\SimpleJsonSyntaxError;
use WebChemistry\SimpleJson\Exception\SimpleJsonUnexpectedEnd;

final class SimpleJsonParser
{

	private const KEYWORD_MAX_LENGTH = 5;
	private const VALUE = '<value>';
	private const END = '<end>';

	private const KEYWORDS = [
		'false' => [false],
		'FALSE' => [false],
		'true' => [true],
		'TRUE' => [true],
		'null' => [null],
		'NULL' => [null],
	];

	private SimpleJsonLexer $lexer;

	public function __construct(string $string)
	{
		$this->lexer = new SimpleJsonLexer();
		$this->lexer->setInput($string);
	}

	/**
	 * @return mixed[]
	 * @throws SimpleJsonSyntaxError
	 */
	private function consumeObject(): array
	{
		$this->lexer->moveNext();

		if ($this->getTokenType() !== SimpleJsonLexer::T_OPEN_BRACE) {
			$this->syntaxError(['[', '{'], $this->getTokenValue());
		}

		$isList = $this->getTokenValue() === '[';
		$endBrace = $isList ? ']' : '}';

		$nextKey = -1;
		$values = [];

		while (true) {
			if (!$this->hasLookahead()) {
				$this->syntaxError([$endBrace, self::VALUE]);
			}

			if ($this->getLookaheadType() === SimpleJsonLexer::T_CLOSE_BRACE) {
				$this->lexer->moveNext();

				break;
			}

			$key = $this->consumeObjectValue();

			if (!$this->hasLookahead()) {
				$this->syntaxError([$endBrace, ',']);
			}

			$type = $this->getLookaheadType();

			if ($type === SimpleJsonLexer::T_COMMA) {
				$values[++$nextKey] = $key;

				$this->lexer->moveNext();
			} elseif ($type === SimpleJsonLexer::T_COLON) {
				if ($isList) {
					throw new SimpleJsonSyntaxError('Lists cannot have keys.');
				}

				$this->lexer->moveNext();

				if (is_float($key)) {
					throw new SimpleJsonSyntaxError('Objects cannot have float as keys.');
				}

				if (is_array($key)) {
					throw new SimpleJsonSyntaxError('Objects cannot have object as key.');
				}

				$values[$key] = $this->consumeObjectValue();

				if (is_int($key)) {
					$nextKey = $key;
				}

				if (!$this->hasLookahead()) {
					$this->syntaxError([',', $endBrace]);
				}

				if ($this->getLookaheadType() === SimpleJsonLexer::T_COMMA) {
					$this->lexer->moveNext();
				}
			} elseif ($type === SimpleJsonLexer::T_CLOSE_BRACE) {
				$values[++$nextKey] = $key;

				$this->lexer->moveNext();

				break;
			} else {
				$this->syntaxError(array_filter([',', $isList ? null : ':', $endBrace]), $this->getLookaheadValue());
			}
		}

		return $values;
	}

	/**
	 * @return string|int|float|bool|mixed[]|null
	 * @throws SimpleJsonSyntaxError
	 */
	private function consumeObjectValue(): string|int|float|bool|array|null
	{
		if (!$this->hasLookahead()) {
			$this->syntaxError([self::VALUE]);
		}

		if ($this->getLookaheadType() === SimpleJsonLexer::T_OPEN_BRACE) {
			return $this->consumeObject();
		}

		$this->lexer->moveNext();

		return match ($this->getTokenType()) {
			SimpleJsonLexer::T_STRING,
			SimpleJsonLexer::T_FLOAT,
			SimpleJsonLexer::T_INT,
			SimpleJsonLexer::T_UNKNOWN => $this->getParsedTokenValue(),
			default => $this->syntaxError([self::VALUE], $this->getTokenValue()),
		};
	}

	/**
	 * @param string[] $expecting
	 * @return never
	 * @throws SimpleJsonSyntaxError
	 */
	private function syntaxError(array $expecting, ?string $got = null): void
	{
		throw SimpleJsonSyntaxError::create($expecting, $got);
	}

	/**
	 * @return mixed[]
	 * @throws SimpleJsonSyntaxError
	 */
	private function process(): array
	{
		$this->lexer->moveNext();

		$array = $this->consumeObject();

		if ($this->hasLookahead()) {
			$this->syntaxError([self::END], $this->getLookaheadValue());
		}

		return $array;
	}

	/**
	 * @phpstan-impure
	 */
	private function getTokenType(): int
	{
		return $this->getTypeFromToken($this->lexer->token) ?? throw new SimpleJsonUnexpectedEnd();
	}

	/**
	 * @phpstan-impure
	 */
	private function getTokenValue(): string
	{
		return $this->getValueFromToken($this->lexer->token) ?? throw new SimpleJsonUnexpectedEnd();
	}

	/**
	 * @phpstan-impure
	 */
	private function getLookaheadType(): int
	{
		return $this->getTypeFromToken($this->lexer->lookahead) ?? throw new SimpleJsonUnexpectedEnd();
	}

	/**
	 * @phpstan-impure
	 */
	private function getLookaheadValue(): string
	{
		return $this->getValueFromToken($this->lexer->lookahead) ?? throw new SimpleJsonUnexpectedEnd();
	}

	/**
	 * @param Token<int, string>|mixed[]|null $token
	 */
	private function getValueFromToken(Token|array|null $token): ?string
	{
		if (!$token) {
			return null;
		}

		if (is_array($token)) { // backward compatibility
			if (!isset($token['value'])) {
				return null;
			}

			if (!is_scalar($token['value'])) {
				throw new LogicException('Token value must be scalar.');
			}

			return (string) $token['value'];
		}

		return $token->value;
	}

	/**
	 * @param Token<int, string>|mixed[]|null $token
	 */
	private function getTypeFromToken(Token|array|null $token): ?int
	{
		if (!$token) {
			return null;
		}

		if (is_array($token)) { // backward compatibility
			if (!isset($token['type'])) {
				return null;
			}

			if (!is_scalar($token['type'])) {
				throw new LogicException('Token type must be scalar.');
			}

			return (int) $token['type'];
		}

		return $token->type;
	}

	/**
	 * @phpstan-impure
	 */
	private function hasLookahead(): bool
	{
		return $this->lexer->lookahead !== null;
	}

	private function getParsedTokenValue(): string|int|float|bool|null
	{
		$value = $this->getTokenValue();
		$type = $this->getTokenType();

		if ($type === SimpleJsonLexer::T_UNKNOWN && strlen($value) <= self::KEYWORD_MAX_LENGTH) {
			if (isset(self::KEYWORDS[$value])) {
				return self::KEYWORDS[$value][0];
			}
		}

		return match ($type) {
			SimpleJsonLexer::T_INT => (int) $value,
			SimpleJsonLexer::T_FLOAT => (float) $value,
			default => $value,
		};
	}

	/**
	 * @param string $string
	 * @return mixed[]
	 * @throws SimpleJsonSyntaxError
	 */
	public static function parse(string $string): array
	{
		if (!$string) {
			return [];
		}

		return (new self($string))->process();
	}

}
