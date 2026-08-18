<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Integration;

use GraphQL\Type\Definition\EnumType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;
use TheCodingMachine\GraphQLite\Containers\BasicAutoWiringContainer;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\Fixtures\EnumExposure\PublishStatus;
use TheCodingMachine\GraphQLite\Fixtures\EnumExposureLegacy\Weekday;
use TheCodingMachine\GraphQLite\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

use function array_map;

/**
 * End-to-end verification of the #[EnumValue] per-case exposure toggle.
 *
 * The attribute is the opt-in signal for a case reaching the schema:
 *   - An enum with a MIX of annotated and unannotated cases is in opt-in mode; only the annotated
 *     cases are exposed and every unannotated case is hidden from the SDL.
 *   - A fully-unannotated enum stays in legacy mode; every case is exposed and docblock summaries
 *     continue to populate case descriptions.
 */
class EnumExposureTest extends TestCase
{
    /** @param class-string $fixtureClass Any class from the fixture namespace to build over. */
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

    public function testMixedEnumExposesOnlyAnnotatedCases(): void
    {
        $schema = $this->buildSchema(PublishStatus::class);

        $enum = $schema->getType('PublishStatus');
        $this->assertInstanceOf(EnumType::class, $enum);

        $exposedNames = array_map(static fn ($value) => $value->name, $enum->getValues());
        $this->assertSame(['Published', 'Scheduled'], $exposedNames);

        // Annotated cases reach the schema, unannotated internal cases are hidden.
        $this->assertNotNull($enum->getValue('Published'));
        $this->assertNotNull($enum->getValue('Scheduled'));
        $this->assertNull($enum->getValue('Draft'));
        $this->assertNull($enum->getValue('Archived'));

        // Metadata wiring stays intact for exposed cases.
        $this->assertSame('Visible to everyone.', $enum->getValue('Published')->description);
    }

    public function testFullyUnannotatedEnumExposesEveryCase(): void
    {
        // A fully-unannotated enum stays in legacy mode; resolving it emits the opt-in advisory.
        $this->expectUserDeprecationMessageMatches('/declares no #\[EnumValue\] attributes/');

        $schema = $this->buildSchema(Weekday::class);

        $enum = $schema->getType('Weekday');
        $this->assertInstanceOf(EnumType::class, $enum);

        $exposedNames = array_map(static fn ($value) => $value->name, $enum->getValues());
        $this->assertSame(['Monday', 'Tuesday', 'Wednesday'], $exposedNames);

        // Docblock fallback still populates case descriptions in legacy mode.
        $this->assertSame('The first working day of the week.', $enum->getValue('Monday')->description);
    }

    public function testLegacyEnumStillSuppressesDocblockCaseDescriptionWhenDisabled(): void
    {
        // Docblock fallback off: every case is still exposed, but the docblock-derived description
        // is dropped.
        $this->expectUserDeprecationMessageMatches('/declares no #\[EnumValue\] attributes/');

        $schema = $this->buildSchema(Weekday::class, docblockDescriptions: false);

        $enum = $schema->getType('Weekday');
        $this->assertInstanceOf(EnumType::class, $enum);

        $this->assertCount(3, $enum->getValues());
        $this->assertNull($enum->getValue('Monday')->description);
    }
}
