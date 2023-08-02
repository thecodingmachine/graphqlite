<?php

namespace TheCodingMachine\GraphQLite\Server\PersistedQuery;

use GraphQL\Server\RequestError;
use TheCodingMachine\GraphQLite\Exceptions\GraphQLExceptionInterface;
use Throwable;

class PersistedQueryError extends RequestError implements GraphQLExceptionInterface
{
	/**
	 * @param string $code
	 */
	public function __construct(
		string $message,
		protected $code,
		Throwable $previous = null
	) {
		parent::__construct($message, 0, $previous);
	}

    public static function notSupported(): self
    {
        // See https://github.com/apollographql/apollo-client/blob/fc450f227522c5311375a6b59ec767ac45f151c7/src/link/persisted-queries/index.ts#L73
        return new self('Persisted queries are not supported by this server.', code: 'PERSISTED_QUERY_NOT_SUPPORTED');
    }

    public static function notFound(): self
    {
        // See https://github.com/apollographql/apollo-client/blob/fc450f227522c5311375a6b59ec767ac45f151c7/src/link/persisted-queries/index.ts#L73
        return new self('Persisted query by that ID was not found and "query" was omitted.', code: 'PERSISTED_QUERY_NOT_FOUND');
    }

	public static function idInvalid(): self
	{
        // This isn't part of an Apollo spec, but it's still nice to have.
		return new self('Persisted query by that ID doesnt match the provided query; you are likely using a wrong hashing method.', code: 'PERSISTED_QUERY_ID_INVALID');
	}

    public function getExtensions(): array
    {
        return [
            'code' => $this->code,
        ];
    }
}
