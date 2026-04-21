<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use TheCodingMachine\GraphQLite\Annotations\Exceptions\DuplicateDescriptionOnTypeException;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Type as TypeAnnotation;
use TheCodingMachine\GraphQLite\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQLite\Reflection\DocBlock\DocBlockFactory;
use TheCodingMachine\GraphQLite\Types\MutableInterface;
use TheCodingMachine\GraphQLite\Types\MutableInterfaceType;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedInterfaceType;
use TheCodingMachine\GraphQLite\Types\TypeAnnotatedObjectType;
use TheCodingMachine\GraphQLite\Utils\DescriptionResolver;

use function assert;
use function interface_exists;

/**
 * This class is in charge of creating Webonyx GraphQL types from annotated objects that do not extend the
 * Webonyx ObjectType class.
 */
class TypeGenerator
{
    /**
     * Tracks every GraphQL type name for which an explicit description has already been contributed
     * via a #[Type] or #[ExtendType] attribute. Used to reject ambiguous multi-source descriptions
     * on the same type with a clear error.
     *
     * Keys are the resolved GraphQL type name, values are a human-readable source label (e.g. the
     * annotated class name) so the resulting exception points at both offending sources.
     *
     * @var array<string, string>
     */
    private array $explicitDescriptionSources = [];

    public function __construct(
        private AnnotationReader $annotationReader,
        private NamingStrategyInterface $namingStrategy,
        private TypeRegistry $typeRegistry,
        private ContainerInterface $container,
        private RecursiveTypeMapperInterface $recursiveTypeMapper,
        private FieldsBuilder $fieldsBuilder,
        private DocBlockFactory|null $docBlockFactory = null,
        private DescriptionResolver $descriptionResolver = new DescriptionResolver(true),
    )
    {
    }

    /**
     * @param class-string<object> $annotatedObjectClassName The FQCN of an object with a Type annotation.
     *
     * @return MutableInterface&(MutableInterfaceType|MutableObjectType)
     *
     * @throws ReflectionException
     */
    public function mapAnnotatedObject(string $annotatedObjectClassName): MutableInterface
    {
        $refTypeClass = new ReflectionClass($annotatedObjectClassName);

        $typeField = $this->annotationReader->getTypeAnnotation($refTypeClass);

        if ($typeField === null) {
            throw MissingAnnotationException::missingTypeException($annotatedObjectClassName);
        }

        // AnnotationReader::getTypeAnnotation only resolves #[Type] attributes, so the concrete
        // Type class is the only possible implementation of TypeInterface returned here. The
        // narrower type grants access to the Type-specific description API without widening
        // TypeInterface (which would be a BC break for external implementations).
        assert($typeField instanceof TypeAnnotation);

        $typeName = $this->namingStrategy->getOutputTypeName($refTypeClass->getName(), $typeField);

        if ($this->typeRegistry->hasType($typeName)) {
            return $this->typeRegistry->getMutableInterface($typeName);
        }

        if (! $typeField->isSelfType()) {
            if (! $refTypeClass->isInstantiable()) {
                throw new GraphQLRuntimeException('Class "' . $annotatedObjectClassName . '" annotated with @Type(class="' . $typeField->getClass() . '") must be instantiable.');
            }
            $annotatedObject = $this->container->get($annotatedObjectClassName);
            $isInterface = interface_exists($typeField->getClass());
        } else {
            $annotatedObject = null;
            $isInterface = $refTypeClass->isInterface();
        }

        $resolvedDescription = $this->descriptionResolver->resolve(
            $typeField->getDescription(),
            $this->extractClassDocblockSummary($refTypeClass),
        );

        if ($typeField->getDescription() !== null) {
            $this->explicitDescriptionSources[$typeName] = '#[Type] on ' . $refTypeClass->getName();
        }

        if ($isInterface) {
            $type = TypeAnnotatedInterfaceType::createFromAnnotatedClass(
                $typeName,
                $typeField->getClass(),
                $annotatedObject,
                $this->fieldsBuilder,
                $this->recursiveTypeMapper,
            );
        } else {
            $type = TypeAnnotatedObjectType::createFromAnnotatedClass(
                $typeName,
                $typeField->getClass(),
                $annotatedObject,
                $this->fieldsBuilder,
                $this->recursiveTypeMapper,
                ! $typeField->isDefault(),
            );
        }

        if ($resolvedDescription !== null) {
            $type->description = $resolvedDescription;
        }

        return $type;
    }

    /**
     * @param object $annotatedObject An object with a ExtendType annotation.
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     *
     * @throws ReflectionException
     */
    public function extendAnnotatedObject(object $annotatedObject, MutableInterface $type): void
    {
        $refTypeClass = new ReflectionClass($annotatedObject);

        $extendTypeAnnotation = $this->annotationReader->getExtendTypeAnnotation($refTypeClass);

        if ($extendTypeAnnotation === null) {
            throw MissingAnnotationException::missingExtendTypeException();
        }

        $typeName = $type->name;

        $this->applyExtendTypeDescription($refTypeClass, $extendTypeAnnotation, $type, $typeName);

        $type->addFields(function () use ($annotatedObject, $typeName) {
            return $this->fieldsBuilder->getFields($annotatedObject, $typeName);
        });
    }

    /**
     * Enforces the rule that a GraphQL type description may live on the base #[Type] OR on at most
     * one #[ExtendType], never both, and applies the #[ExtendType] description to the underlying
     * type when valid.
     *
     * @param ReflectionClass<object> $refTypeClass
     * @param MutableInterface&(MutableObjectType|MutableInterfaceType) $type
     */
    private function applyExtendTypeDescription(
        ReflectionClass $refTypeClass,
        ExtendType $extendTypeAnnotation,
        MutableInterface $type,
        string $typeName,
    ): void {
        $explicitDescription = $extendTypeAnnotation->getDescription();
        if ($explicitDescription === null) {
            return;
        }

        $sourceLabel = '#[ExtendType] on ' . $refTypeClass->getName();

        if (isset($this->explicitDescriptionSources[$typeName])) {
            $targetClass = $extendTypeAnnotation->getClass() ?? $refTypeClass->getName();
            throw DuplicateDescriptionOnTypeException::forType(
                $targetClass,
                [$this->explicitDescriptionSources[$typeName], $sourceLabel],
            );
        }

        $this->explicitDescriptionSources[$typeName] = $sourceLabel;
        $type->description = $explicitDescription;
    }

    /**
     * Returns the docblock summary for a class, or null when:
     *   - docblock fallback is disabled on the resolver,
     *   - no DocBlockFactory was injected,
     *   - the class has no docblock summary.
     *
     * @param ReflectionClass<object> $refTypeClass
     */
    private function extractClassDocblockSummary(ReflectionClass $refTypeClass): string|null
    {
        if (! $this->descriptionResolver->isDocblockFallbackEnabled() || $this->docBlockFactory === null) {
            return null;
        }

        $summary = $this->docBlockFactory->create($refTypeClass)->getSummary();

        return $summary === '' ? null : $summary;
    }
}
