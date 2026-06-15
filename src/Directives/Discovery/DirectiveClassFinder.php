<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives\Discovery;

use ReflectionClass;
use TheCodingMachine\GraphQLite\Directives\TypeSystemDirective;
use TheCodingMachine\GraphQLite\Discovery\Cache\ClassFinderComputedCache;
use TheCodingMachine\GraphQLite\Discovery\ClassFinder;

use function array_reduce;

/**
 * Finds the classes in the configured namespaces that implement {@see TypeSystemDirective}.
 *
 * Same pattern as {@see \TheCodingMachine\GraphQLite\Mappers\ClassFinderTypeMapper}: map each class
 * from {@see ClassFinder} to a {@see GlobDirectivesCache} entry (or null), then collect the FQCNs.
 * {@see ClassFinderComputedCache} handles cache invalidation in dev mode.
 *
 * @internal
 */
final class DirectiveClassFinder
{
    /** @var list<class-string<TypeSystemDirective>>|null */
    private array|null $cache = null;

    public function __construct(
        private readonly ClassFinder $classFinder,
        private readonly ClassFinderComputedCache $classFinderComputedCache,
    ) {
    }

    /** @return list<class-string<TypeSystemDirective>> */
    public function findDirectives(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        /** @var list<class-string<TypeSystemDirective>> $result */
        $result = $this->classFinderComputedCache->compute(
            $this->classFinder,
            'customDirectives',
            static function (ReflectionClass $refClass): GlobDirectivesCache|null {
                if ($refClass->isAbstract() || $refClass->isInterface() || $refClass->isEnum()) {
                    return null;
                }

                if (! $refClass->implementsInterface(TypeSystemDirective::class)) {
                    return null;
                }

                /** @var class-string<TypeSystemDirective> $directiveClass */
                $directiveClass = $refClass->getName();
                return new GlobDirectivesCache($directiveClass);
            },
            static fn (array $entries): array => array_reduce(
                $entries,
                static function (array $carry, GlobDirectivesCache|null $entry): array {
                    if ($entry === null) {
                        return $carry;
                    }
                    $carry[] = $entry->directiveClass;
                    return $carry;
                },
                [],
            ),
        );

        $this->cache = $result;
        return $result;
    }
}
