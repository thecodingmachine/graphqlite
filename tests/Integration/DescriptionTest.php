<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\DuplicateDescriptionOnTypeException;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\Description\Book;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionDuplicate\Book as DuplicateBook;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

/**
 * End-to-end verification of the explicit-description attribute + SchemaFactory docblock toggle
 * introduced to address upstream issues #453 and #740.
 *
 * Covers:
 *   - Explicit descriptions on #[Query], #[Mutation], #[Type], #[ExtendType], #[Factory], and
 *     native PHP 8.1 enums annotated with #[Type] — they must land in the generated schema.
 *   - Legitimate consumer use case: #[ExtendType] providing the description when the base
 *     #[Type] did not (no exception).
 *   - Duplicate description conflict detection: throws DuplicateDescriptionOnTypeException
 *     when #[Type] and #[ExtendType] both carry a description for the same class.
 *   - Backwards compatibility: docblock summaries continue to populate descriptions by default.
 *   - Security opt-out: setDocblockDescriptionsEnabled(false) suppresses every docblock
 *     fallback path, preventing internal developer docblocks from leaking to API consumers.
 */
class DescriptionTest extends TestCase
{
    /**
     * Builds a schema over the namespace that contains the given fixture class. Callers reference
     * fixtures via `::class` so the IDE can navigate + refactor safely, instead of hard-coding a
     * namespace string that drifts silently when fixtures are moved.
     *
     * @param class-string $fixtureClass Any class from the fixture namespace to build over.
     */
    private function buildSchema(string $fixtureClass, bool $docblockDescriptions = true): Schema
    {
        $factory = new SchemaFactory(
            new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(new EmptyContainer()),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());
        $factory->addNamespace((new ReflectionClass($fixtureClass))->getNamespaceName());
        $factory->setDocblockDescriptionsEnabled($docblockDescriptions);

        return $factory->createSchema();
    }

    public function testExplicitDescriptionOnQueryOverridesDocblock(): void
    {
        $schema = $this->buildSchema(Book::class);

        $bookField = $schema->getQueryType()->getField('book');
        $this->assertSame('Fetch a single library book.', $bookField->description);
    }

    public function testExplicitDescriptionOnMutation(): void
    {
        $schema = $this->buildSchema(Book::class);

        $mutationField = $schema->getMutationType()->getField('borrowBook');
        $this->assertSame('Borrow a book from the library.', $mutationField->description);
    }

    public function testExplicitDescriptionOnType(): void
    {
        $schema = $this->buildSchema(Book::class);

        $bookType = $schema->getType('Book');
        $this->assertSame('A library book available for checkout.', $bookType->description);
    }

    public function testExplicitDescriptionOnFieldOverridesDocblock(): void
    {
        $schema = $this->buildSchema(Book::class);

        $titleField = $schema->getType('Book')->getField('title');
        $this->assertSame('The book title as it appears on the cover.', $titleField->description);
    }

    public function testExplicitDescriptionOnNativeEnumViaType(): void
    {
        $schema = $this->buildSchema(Book::class);

        $genreType = $schema->getType('Genre');
        $this->assertSame('Editorial classification of a book.', $genreType->description);
    }

    public function testExtendTypeSuppliesDescriptionWhenBaseTypeHasNone(): void
    {
        $schema = $this->buildSchema(Book::class);

        $authorType = $schema->getType('Author');
        $this->assertSame('A person who writes books.', $authorType->description);
    }

    public function testDocblockFallbackProvidesFieldDescriptionByDefault(): void
    {
        $schema = $this->buildSchema(Book::class);

        $authorNameField = $schema->getType('Author')->getField('name');
        $this->assertNotNull($authorNameField->description);
        $this->assertStringContainsString(
            'Docblock summary that should populate the field description via the fallback path.',
            $authorNameField->description,
        );
    }

    public function testDisablingDocblockFallbackSuppressesFieldDescription(): void
    {
        $schema = $this->buildSchema(
            Book::class,
            docblockDescriptions: false,
        );

        // The `author` query has no explicit description; its description came from the docblock.
        // With the toggle off, that docblock must not leak into the public schema.
        $authorQuery = $schema->getQueryType()->getField('author');
        $this->assertTrue(
            $authorQuery->description === null || $authorQuery->description === '',
            'Expected no description when docblock fallback is disabled, got: ' . var_export($authorQuery->description, true),
        );

        // The `name` field on Author also relied on docblock — it too must be suppressed.
        $nameField = $schema->getType('Author')->getField('name');
        $this->assertTrue(
            $nameField->description === null || $nameField->description === '',
            'Expected no description when docblock fallback is disabled, got: ' . var_export($nameField->description, true),
        );

        // Explicit descriptions still land in the schema because they do not rely on the toggle.
        $bookQuery = $schema->getQueryType()->getField('book');
        $this->assertSame('Fetch a single library book.', $bookQuery->description);
    }

    public function testDuplicateDescriptionAcrossTypeAndExtendTypeThrows(): void
    {
        try {
            $schema = $this->buildSchema(DuplicateBook::class);

            // Force resolution — extensions are processed lazily when the target type is first touched.
            $schema->getQueryType()->getField('book');
            $schema->getType('DuplicateBook');

            $this->fail('Expected DuplicateDescriptionOnTypeException to be thrown.');
        } catch (DuplicateDescriptionOnTypeException $exception) {
            // The message must name both offending sources so the user can jump straight to the fix.
            $this->assertStringContainsString('#[Type] on', $exception->getMessage());
            $this->assertStringContainsString('#[ExtendType] on', $exception->getMessage());
            $this->assertStringContainsString('DescriptionDuplicate\\Book', $exception->getMessage());
            $this->assertStringContainsString('DescriptionDuplicate\\BookExtension', $exception->getMessage());
        }
    }
}
