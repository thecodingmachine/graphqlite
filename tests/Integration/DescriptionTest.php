<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Error\DebugFlag;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Utils\SchemaPrinter;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\DuplicateDescriptionOnTypeException;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\Description\Book;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionDuplicate\Book as DuplicateBook;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionItems\AudienceFieldMiddleware;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionItems\AudienceInputFieldMiddleware;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionItems\BookController;
use TheCodingMachine\GraphQLite\Fixtures\DescriptionLegacyEnum\Era;
use TheCodingMachine\GraphQLite\Fixtures\UndefinedDescription\UndefinedDescriptionController;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQLite\Utils\DescriptionResolver;

use function array_column;
use function assert;
use function substr_count;

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
    private function buildSchema(
        string $fixtureClass,
        bool $docblockDescriptions = true,
        bool $undefinedDescriptions = true,
        CacheInterface|null $cache = null,
    ): Schema
    {
        $factory = new SchemaFactory(
            $cache ?? new Psr16Cache(new ArrayAdapter()),
            new BasicAutoWiringContainer(new EmptyContainer()),
        );
        $factory->setAuthenticationService(new VoidAuthenticationService());
        $factory->setAuthorizationService(new VoidAuthorizationService());
        $factory->addNamespace((new ReflectionClass($fixtureClass))->getNamespaceName());
        $factory->setDocblockDescriptionsEnabled($docblockDescriptions);
        $factory->setUndefinedDescriptionsEnabled($undefinedDescriptions);
        $factory->addFieldMiddleware(new AudienceFieldMiddleware());
        $factory->addInputFieldMiddleware(new AudienceInputFieldMiddleware());

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

    public function testEnumValueAttributeProvidesCaseDescription(): void
    {
        $schema = $this->buildSchema(Book::class);

        $genreType = $schema->getType('Genre');
        $fictionValue = $genreType->getValue('Fiction');
        $this->assertSame('Fiction works including novels and short stories.', $fictionValue->description);
    }

    public function testEnumWithZeroEnumValueAttributesTriggersDeprecation(): void
    {
        // The Era fixture deliberately declares zero #[EnumValue] attributes, so it stays in
        // legacy mode (every case exposed) and the advisory fires. That is the scenario the
        // advisory targets; partial annotation on other enums (like Genre in the Description
        // namespace) is deliberately silent because it has already opted in.
        $this->expectUserDeprecationMessageMatches('/declares no #\[EnumValue\] attributes.*legacy mode/s');

        $schema = $this->buildSchema(Era::class);
        // Force enum resolution — types are lazy-mapped until referenced.
        $schema->getType('Era');
    }

    public function testEnumWithPartialEnumValueAttributesIsSilent(): void
    {
        // Genre has #[EnumValue] on Fiction and Poetry but not NonFiction. Partial annotation is
        // deliberately OK: leaving a case unannotated is the mechanism for hiding it from the
        // public schema after the future default flip, and therefore must not itself produce an
        // advisory. Asserting no deprecation here locks that contract in.
        $captured = [];
        set_error_handler(
            static function (int $errno, string $errstr) use (&$captured): bool {
                if ($errno === E_USER_DEPRECATED && str_contains($errstr, 'EnumValue')) {
                    $captured[] = $errstr;

                    return true;
                }

                return false;
            },
            E_USER_DEPRECATED,
        );

        try {
            $schema = $this->buildSchema(Book::class);
            $schema->getType('Genre');
        } finally {
            restore_error_handler();
        }

        $this->assertSame([], $captured, 'Partial #[EnumValue] annotation must not trigger the advisory notice.');
    }

    public function testUnannotatedCaseIsHiddenInOptInMode(): void
    {
        $schema = $this->buildSchema(Book::class);

        $genreType = $schema->getType('Genre');

        // Genre carries #[EnumValue] on Fiction and Poetry, which puts it in opt-in mode. The
        // NonFiction case has no #[EnumValue] attribute, so it is now hidden from the schema
        // entirely rather than falling back to its docblock description.
        $this->assertNull($genreType->getValue('NonFiction'));

        $exposedNames = array_map(static fn ($value) => $value->name, $genreType->getValues());
        $this->assertSame(['Fiction', 'Poetry'], $exposedNames);
    }

    public function testEnumValueAttributeProvidesDeprecationReason(): void
    {
        $schema = $this->buildSchema(Book::class);

        $genreType = $schema->getType('Genre');
        $poetryValue = $genreType->getValue('Poetry');
        $this->assertSame('Use Fiction::Verse instead.', $poetryValue->deprecationReason);
    }

    public function testDisablingDocblockFallbackKeepsExplicitDescriptionOnExposedCase(): void
    {
        $schema = $this->buildSchema(Book::class, docblockDescriptions: false);

        $genreType = $schema->getType('Genre');

        // Fiction has an explicit #[EnumValue] description — still present with the toggle off.
        $this->assertSame(
            'Fiction works including novels and short stories.',
            $genreType->getValue('Fiction')->description,
        );

        // NonFiction is unannotated, so opt-in mode hides it regardless of the docblock toggle.
        $this->assertNull($genreType->getValue('NonFiction'));
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

    public function testUndefinedDescriptionsAreAppendedByDefault(): void
    {
        $schema = $this->buildSchema(UndefinedDescriptionController::class);

        $this->assertSame('May be omitted; null is not accepted.', DescriptionResolver::UNDEFINED_REFUSES_NULL);
        $this->assertSame('May be omitted; null is accepted.', DescriptionResolver::UNDEFINED_ACCEPTS_NULL);
        $refuses = '- ' . DescriptionResolver::UNDEFINED_REFUSES_NULL;
        $accepts = '- ' . DescriptionResolver::UNDEFINED_ACCEPTS_NULL;

        $this->assertSame([
            'property' => $refuses,
            'nullableProperty' => $accepts,
            'describedProperty' => "Age in years.\n\n" . $refuses,
            'describedNullableProperty' => "Age in years.\n\n" . $accepts,
            // Not duplicated when the developer already wrote it
            'alreadyDescribedProperty' => 'Age in years. May be omitted; null is not accepted.',
            'plainProperty' => 'Age in years.',
            // A forced non-null type says it is required, so no sentence
            'nonNullProperty' => '',
            'promotedParameter' => $refuses,
            'nullablePromotedParameter' => $accepts,
            'describedPromotedParameter' => "Age in years.\n\n" . $refuses,
            'describedNullablePromotedParameter' => "Age in years.\n\n" . $accepts,
            'setter' => $refuses,
            'nullableSetter' => $accepts,
            'describedSetter' => "Age in years.\n\n" . $refuses,
            'describedNullableSetter' => "Age in years.\n\n" . $accepts,
            'nonNullSetter' => '',
        ], $this->introspectInputFieldDescriptions($schema));

        $this->assertSame([
            'argument' => $refuses,
            'nullableArgument' => $accepts,
            'describedArgument' => "Age in years.\n\n" . $refuses,
            'describedNullableArgument' => "Age in years.\n\n" . $accepts,
            'plainArgument' => null,
            'nonNullArgument' => null,
        ], $this->introspectArgumentDescriptions($schema));

        $printedInput = SchemaPrinter::printType($schema->getType('UndefinedDescriptionInput'));
        $this->assertStringContainsString(
            "  \"\"\"\n  Age in years.\n  \n  - May be omitted; null is not accepted.\n  \"\"\"\n  describedProperty: Int\n",
            $printedInput,
        );
        $this->assertStringContainsString("  \"- May be omitted; null is accepted.\"\n  nullableSetter: Int\n", $printedInput);
        $this->assertStringContainsString("  \"\"\n  nonNullProperty: Int!\n", $printedInput);
        $this->assertStringContainsString("  \"\"\n  nonNullSetter: Int!\n", $printedInput);
        $this->assertStringContainsString(
            "    plainArgument: Int = null\n    nonNullArgument: Int!\n",
            SchemaPrinter::printType($schema->getQueryType()),
        );
    }

    public function testUndefinedDescriptionsCanBeDisabled(): void
    {
        $schema = $this->buildSchema(UndefinedDescriptionController::class, undefinedDescriptions: false);

        $this->assertSame([
            'property' => '',
            'nullableProperty' => '',
            'describedProperty' => 'Age in years.',
            'describedNullableProperty' => 'Age in years.',
            'alreadyDescribedProperty' => 'Age in years. May be omitted; null is not accepted.',
            'plainProperty' => 'Age in years.',
            'nonNullProperty' => '',
            'promotedParameter' => '',
            'nullablePromotedParameter' => '',
            'describedPromotedParameter' => 'Age in years.',
            'describedNullablePromotedParameter' => 'Age in years.',
            'setter' => '',
            'nullableSetter' => '',
            'describedSetter' => 'Age in years.',
            'describedNullableSetter' => 'Age in years.',
            'nonNullSetter' => '',
        ], $this->introspectInputFieldDescriptions($schema));

        $this->assertSame([
            'argument' => null,
            'nullableArgument' => null,
            'describedArgument' => 'Age in years.',
            'describedNullableArgument' => 'Age in years.',
            'plainArgument' => null,
            'nonNullArgument' => null,
        ], $this->introspectArgumentDescriptions($schema));
    }

    public function testUndefinedDescriptionsSurviveDisabledDocblockFallback(): void
    {
        $schema = $this->buildSchema(UndefinedDescriptionController::class, docblockDescriptions: false);

        $refuses = '- ' . DescriptionResolver::UNDEFINED_REFUSES_NULL;
        $accepts = '- ' . DescriptionResolver::UNDEFINED_ACCEPTS_NULL;
        $this->assertSame([
            'property' => $refuses,
            'nullableProperty' => $accepts,
            'describedProperty' => $refuses,
            'describedNullableProperty' => $accepts,
            'alreadyDescribedProperty' => 'Age in years. May be omitted; null is not accepted.',
            'plainProperty' => null,
            'nonNullProperty' => null,
            'promotedParameter' => $refuses,
            'nullablePromotedParameter' => $accepts,
            'describedPromotedParameter' => "Age in years.\n\n" . $refuses,
            'describedNullablePromotedParameter' => "Age in years.\n\n" . $accepts,
            'setter' => $refuses,
            'nullableSetter' => $accepts,
            'describedSetter' => $refuses,
            'describedNullableSetter' => $accepts,
            'nonNullSetter' => null,
        ], $this->introspectInputFieldDescriptions($schema));

        $this->assertSame([
            'argument' => $refuses,
            'nullableArgument' => $accepts,
            // The @param description stays out of the schema with the docblock fallback off
            'describedArgument' => $refuses,
            'describedNullableArgument' => $accepts,
            'plainArgument' => null,
            'nonNullArgument' => null,
        ], $this->introspectArgumentDescriptions($schema));
    }

    public function testArgumentDocblockDescriptionsFollowTheToggle(): void
    {
        $enabled = $this->buildSchema(BookController::class);
        $this->assertSame(
            'Number of Books to return',
            $enabled->getQueryType()->getField('books')->getArg('first')->description,
        );

        $disabled = $this->buildSchema(BookController::class, docblockDescriptions: false);
        $this->assertNull($disabled->getQueryType()->getField('books')->getArg('first')->description);
    }

    public function testDescriptionItemsAreListedAfterTheDescription(): void
    {
        $schema = $this->buildSchema(BookController::class);
        $query = $schema->getQueryType();

        // Items keep the middleware pipe order: user middlewares, then #[Cost]
        $this->assertSame(
            "Paginated list of Books, optionally filtered\n"
                . "\n"
                . "- Audience: librarians\n"
                . "- Audience: members\n"
                . '- Cost: complexity = 5, multipliers = [first], defaultMultiplier = null',
            $query->getField('books')->description,
        );
        $this->assertSame(
            "Counts the Books on loan.\n\n- Cost: complexity = 2, multipliers = [], defaultMultiplier = null",
            $query->getField('loanedBookCount')->description,
        );
        $this->assertSame(
            "- Audience: librarians\n- Cost: complexity = 3, multipliers = [], defaultMultiplier = null",
            $query->getField('undescribedBooks')->description,
        );
        $this->assertSame(
            '- Cost: complexity = 4, multipliers = [], defaultMultiplier = null',
            $query->getField('emptyDescribedBooks')->description,
        );

        $input = $schema->getType('BookInput');
        assert($input instanceof InputObjectType);

        // The Undefined item comes first, since it is added before the input field middlewares run
        $this->assertSame(
            "Title of the Book.\n\n- May be omitted; null is not accepted.\n- Audience: librarians",
            $input->getField('title')->description,
        );
        $this->assertSame(
            "Publication date, if any\n\n- May be omitted; null is accepted.\n- Audience: librarians",
            $input->getField('publishedOn')->description,
        );
        $this->assertSame('- Audience: librarians', $input->getField('notes')->description);
    }

    public function testDescriptionItemsWithoutDocblockFallback(): void
    {
        $schema = $this->buildSchema(BookController::class, docblockDescriptions: false);
        $query = $schema->getQueryType();

        $this->assertSame(
            '- Cost: complexity = 2, multipliers = [], defaultMultiplier = null',
            $query->getField('loanedBookCount')->description,
        );
        $this->assertStringStartsWith(
            "Paginated list of Books, optionally filtered\n\n- Audience: librarians\n",
            $query->getField('books')->description,
        );

        $input = $schema->getType('BookInput');
        assert($input instanceof InputObjectType);
        $this->assertSame(
            "- May be omitted; null is not accepted.\n- Audience: librarians",
            $input->getField('title')->description,
        );
    }

    public function testDescriptionItemsAreNotRepeatedAcrossBuildsSharingACache(): void
    {
        $cache = new Psr16Cache(new ArrayAdapter());
        $descriptions = [];
        for ($build = 0; $build < 2; $build++) {
            $schema = $this->buildSchema(BookController::class, cache: $cache);
            $input = $schema->getType('BookInput');
            assert($input instanceof InputObjectType);
            $descriptions[] = [
                $schema->getQueryType()->getField('books')->description,
                $input->getField('title')->description,
            ];
        }

        $this->assertSame($descriptions[0], $descriptions[1]);
        $this->assertSame(1, substr_count($descriptions[1][0], 'Cost:'));
        $this->assertSame(1, substr_count($descriptions[1][1], 'May be omitted'));
    }

    /** @return array<string, string|null> */
    private function introspectInputFieldDescriptions(Schema $schema): array
    {
        $result = GraphQL::executeQuery(
            $schema,
            '{ __type(name: "UndefinedDescriptionInput") { inputFields { name description } } }',
        )->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);

        return array_column($result['data']['__type']['inputFields'], 'description', 'name');
    }

    /** @return array<string, string|null> */
    private function introspectArgumentDescriptions(Schema $schema): array
    {
        $result = GraphQL::executeQuery(
            $schema,
            '{ __type(name: "Query") { fields { name args { name description } } } }',
        )->toArray(DebugFlag::RETHROW_INTERNAL_EXCEPTIONS);

        foreach ($result['data']['__type']['fields'] as $field) {
            if ($field['name'] === 'undefinedDescriptionArguments') {
                return array_column($field['args'], 'description', 'name');
            }
        }

        $this->fail('The undefinedDescriptionArguments query is missing');
    }
}
