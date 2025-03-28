<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Http;

use DateInterval;
use GraphQL\Error\DebugFlag;
use GraphQL\Server\ServerConfig;
use GraphQL\Type\Schema;
use GraphQL\Validator\DocumentValidator;
use GraphQL\Validator\Rules\QueryComplexity;
use GraphQL\Validator\Rules\ValidationRule;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\SimpleCache\CacheInterface;
use TheCodingMachine\GraphQLite\Context\Context;
use TheCodingMachine\GraphQLite\Exceptions\WebonyxErrorHandler;
use TheCodingMachine\GraphQLite\GraphQLRuntimeException;
use TheCodingMachine\GraphQLite\Server\PersistedQuery\CachePersistedQueryLoader;
use TheCodingMachine\GraphQLite\Server\PersistedQuery\NotSupportedPersistedQueryLoader;

use function class_exists;
use function is_callable;

/**
 * A factory generating a PSR-15 middleware tailored for GraphQLite.
 *
 * @phpstan-import-type PersistedQueryLoader from ServerConfig
 */
class Psr15GraphQLMiddlewareBuilder
{
    private string $url = '/graphql';
    private ServerConfig $config;

    private ResponseFactoryInterface|null $responseFactory = null;

    private StreamFactoryInterface|null $streamFactory = null;

    private HttpCodeDeciderInterface $httpCodeDecider;

    /** @var ValidationRule[] */
    private array $addedValidationRules = [];

    public function __construct(Schema $schema)
    {
        $this->config = new ServerConfig();
        $this->config->setSchema($schema);
        $this->config->setDebugFlag(DebugFlag::RETHROW_UNSAFE_EXCEPTIONS);
        $this->config->setErrorFormatter([WebonyxErrorHandler::class, 'errorFormatter']);
        /** @phpstan-ignore argument.type */
        $this->config->setErrorsHandler([WebonyxErrorHandler::class, 'errorHandler']);
        $this->config->setContext(new Context());
        $this->config->setPersistedQueryLoader(new NotSupportedPersistedQueryLoader());
        $this->httpCodeDecider = new HttpCodeDecider();
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getConfig(): ServerConfig
    {
        return $this->config;
    }

    public function setConfig(ServerConfig $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function setResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function setHttpCodeDecider(HttpCodeDeciderInterface $httpCodeDecider): self
    {
        $this->httpCodeDecider = $httpCodeDecider;

        return $this;
    }

    public function useAutomaticPersistedQueries(CacheInterface $cache, DateInterval|null $ttl = null): self
    {
        $this->config->setPersistedQueryLoader(new CachePersistedQueryLoader($cache, $ttl));

        return $this;
    }

    public function limitQueryComplexity(int $complexity): self
    {
        return $this->addValidationRule(new QueryComplexity($complexity));
    }

    public function addValidationRule(ValidationRule $rule): self
    {
        $this->addedValidationRules[] = $rule;

        return $this;
    }

    public function createMiddleware(): MiddlewareInterface
    {
        if ($this->responseFactory === null && ! class_exists(ResponseFactory::class)) {
            throw new GraphQLRuntimeException('You need to set a ResponseFactory to use the Psr15GraphQLMiddlewareBuilder. Call Psr15GraphQLMiddlewareBuilder::setResponseFactory or try installing zend-diactoros: composer require zendframework/zend-diactoros'); // @codeCoverageIgnore
        }
        $this->responseFactory = $this->responseFactory ?: new ResponseFactory();

        if ($this->streamFactory === null && ! class_exists(StreamFactory::class)) {
            throw new GraphQLRuntimeException('You need to set a StreamFactory to use the Psr15GraphQLMiddlewareBuilder. Call Psr15GraphQLMiddlewareBuilder::setStreamFactory or try installing zend-diactoros: composer require zendframework/zend-diactoros'); // @codeCoverageIgnore
        }
        $this->streamFactory = $this->streamFactory ?: new StreamFactory();

        // If getValidationRules() is null in the config, DocumentValidator will default to DocumentValidator::allRules().
        // So if we only added given rule, all of the default rules would not be validated, so we must also provide them.
        $originalValidationRules = $this->config->getValidationRules() ?? DocumentValidator::allRules();

        $this->config->setValidationRules(function (...$args) use ($originalValidationRules) {
            if (is_callable($originalValidationRules)) {
                $originalValidationRules = $originalValidationRules(...$args);
            }

            return [
                ...$originalValidationRules,
                ...$this->addedValidationRules,
            ];
        });

        return new WebonyxGraphqlMiddleware($this->config, $this->responseFactory, $this->streamFactory, $this->httpCodeDecider, $this->url);
    }
}
