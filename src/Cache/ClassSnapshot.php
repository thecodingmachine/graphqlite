<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use ReflectionClass;

use function array_merge;
use function Safe\filemtime;

class ClassSnapshot
{
    /** @param array<string, int> $dependencies */
    public function __construct(
        private readonly array $dependencies,
    )
    {
    }

    public static function fromReflection(ReflectionClass $class, bool $useInheritance = false): self
    {
        return new self(
            self::dependencies($class, $useInheritance),
        );
    }

    /** @return array<string, int> */
    private static function dependencies(ReflectionClass $class, bool $useInheritance = false): array
    {
        $filename = $class->getFileName();

        // Internal classes are treated as always the same, e.g. you'll have to drop the cache between PHP versions.
        if ($filename === false) {
            return [];
        }

        $files = [$filename => filemtime($filename)];

        if (! $useInheritance) {
            return [];
        }

        if ($class->getParentClass() !== false) {
            $files = array_merge($files, self::dependencies($class->getParentClass()));
        }

        foreach ($class->getTraits() as $trait) {
            $files = array_merge($files, self::dependencies($trait));
        }

        foreach ($class->getInterfaces() as $interface) {
            $files = array_merge($files, self::dependencies($interface));
        }

        return $files;
    }

    public function changed(): bool
    {
        foreach ($this->dependencies as $filename => $modificationTime) {
            if ($modificationTime !== filemtime($filename)) {
                return true;
            }
        }

        return false;
    }
}
