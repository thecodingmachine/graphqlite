<?php

namespace TheCodingMachine\GraphQLite\Cache;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use TheCodingMachine\GraphQLite\Cache\FilesSnapshot;
use TheCodingMachine\GraphQLite\Fixtures\Types\FooType;

#[CoversClass(FilesSnapshot::class)]
class FilesSnapshotTest extends TestCase
{
    #[TestWith([true])]
    #[TestWith([false])]
    public function testTracksChangesInClass(bool $withInheritance): void
    {
        $fooReflection = new \ReflectionClass(FooType::class);
        $snapshot = FilesSnapshot::forClass($fooReflection, $withInheritance);

        self::assertFalse($snapshot->changed());

        // Make sure it serializes properly
        /** @var FilesSnapshot $snapshot */
        $snapshot = unserialize(serialize($snapshot));

        self::assertFalse($snapshot->changed());

        $this->touch($fooReflection->getFileName());

        self::assertTrue($snapshot->changed());
    }

    public function testDoesNotTrackChangesInSuperTypesWithoutUsingInheritance(): void
    {
        $fooReflection = new \ReflectionClass(FooType::class);
        $snapshot = FilesSnapshot::forClass($fooReflection);

        self::assertFalse($snapshot->changed());

        $this->touch($fooReflection->getParentClass()->getFileName());

        self::assertFalse($snapshot->changed());
    }

    public function testTracksChangesInSuperTypesUsingInheritance(): void
    {
        $fooReflection = new \ReflectionClass(FooType::class);
        $snapshot = FilesSnapshot::forClass($fooReflection, true);

        self::assertFalse($snapshot->changed());

        $this->touch($fooReflection->getParentClass()->getFileName());

        self::assertTrue($snapshot->changed());
    }

    public function testTracksChangesInFile(): void
    {
        $fileName = (new \ReflectionClass(FooType::class))->getFileName();
        $snapshot = FilesSnapshot::for([$fileName]);

        self::assertFalse($snapshot->changed());

        $this->touch($fileName);

        self::assertTrue($snapshot->changed());
    }

    private function touch(string $fileName): void
    {
        \Safe\touch($fileName, \Safe\filemtime($fileName) + 1);
        clearstatcache();
    }
}