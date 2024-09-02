<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Cache;

use ReflectionClass;

use function array_merge;
use function Safe\filemtime;

class FilesSnapshot
{
    /** @param array<string, int> $dependencies */
    private function __construct(
        private readonly array $dependencies,
    )
    {
    }

    /**
     * @param list<string> $files
     */
    public static function for(array $files): self
    {
        $dependencies = [];

        foreach (array_unique($files) as $file) {
            $dependencies[$file] = filemtime($file);
        }

        return new self($dependencies);
    }

    public static function forClass(ReflectionClass $class, bool $withInheritance = false): self
    {
        return self::for(
            self::dependencies($class, $withInheritance),
        );
    }

    public static function alwaysUnchanged(): self
    {
        return new self([]);
    }

    /** @return list<string> */
    private static function dependencies(ReflectionClass $class, bool $withInheritance = false): array
    {
        $filename = $class->getFileName();

        // Internal classes are treated as always the same, e.g. you'll have to drop the cache between PHP versions.
        if ($filename === false) {
            return [];
        }

        $files = [$filename];

        if (! $withInheritance) {
            return $files;
        }

        if ($class->getParentClass() !== false) {
            $files = [...$files, ...self::dependencies($class->getParentClass(), $withInheritance)];
        }

        foreach ($class->getTraits() as $trait) {
            $files = [...$files, ...self::dependencies($trait, $withInheritance)];
        }

        foreach ($class->getInterfaces() as $interface) {
            $files = [...$files, ...self::dependencies($interface, $withInheritance)];
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
