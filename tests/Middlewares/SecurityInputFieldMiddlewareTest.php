<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\CallableResolver;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Parameters\SourceParameter;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContextInterface;
use TheCodingMachine\GraphQLite\Security\SecurityRuleMessageInterface;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

/**
 * Covers what #[Security] does on an input field.
 *
 * Input fields go through their own middleware, which duplicates the field middleware rather than
 * sharing with it, so anything the two are meant to agree on has to be pinned on both. Here that is
 * how a denial's message is chosen: from the attribute, from a rule stating its own, or from the
 * default.
 */
class SecurityInputFieldMiddlewareTest extends TestCase
{
    public function testRuleGrantsAccess(): void
    {
        $field = $this->process([new Security(rule: static fn (SecurityRuleContext $context): bool => true)]);

        self::assertNotNull($field);
        self::assertSame('resolved', $this->resolveField($field));
    }

    /** A rule stating its own message is denied with it, with nothing written in the attribute. */
    public function testRuleSuppliesTheRefusalMessageWhenTheAttributeGivesNone(): void
    {
        $field = $this->process([new Security(rule: $this->refusingRule('Page size must be at most 100.'))]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Page size must be at most 100.');
        $this->resolveField($field);
    }

    /** A message on the attribute is about this input field specifically, so it outranks the rule's. */
    public function testExplicitMessageWinsOverTheRuleSuppliedOne(): void
    {
        $field = $this->process([
            new Security(rule: $this->refusingRule('From the rule'), message: 'From the attribute'),
        ]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('From the attribute');
        $this->resolveField($field);
    }

    /** Including when what the attribute wrote happens to be the default word for word. */
    public function testExplicitMessageWinsEvenWhenItReadsLikeTheDefault(): void
    {
        $field = $this->process([
            new Security(rule: $this->refusingRule('From the rule'), message: 'Access denied.'),
        ]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Access denied.');
        $this->resolveField($field);
    }

    /** Opt in: a rule that does not implement the contract is denied exactly as it always was. */
    public function testRuleWithoutTheContractIsDeniedWithTheDefaultMessage(): void
    {
        $field = $this->process([new Security(rule: static fn (SecurityRuleContext $context): bool => false)]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Access denied.');
        $this->resolveField($field);
    }

    /** An expression has no object to ask, so it keeps the default too. */
    public function testExpressionIsDeniedWithTheDefaultMessage(): void
    {
        $field = $this->process([new Security('user != null')]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Access denied.');
        $this->resolveField($field);
    }

    /** A rule that refuses everything, stating $message as the reason. */
    private function refusingRule(string $message): SecurityRuleMessageInterface
    {
        return new class ($message) implements SecurityRuleMessageInterface {
            public function __construct(private readonly string $message)
            {
            }

            public function __invoke(SecurityRuleContextInterface $context): bool
            {
                return false;
            }

            public function getRefusalMessage(): string
            {
                return $this->message;
            }
        };
    }

    /** @param MiddlewareAnnotationInterface[] $annotations */
    private function process(array $annotations): InputField|null
    {
        $descriptor = new InputFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            resolver: static fn (): string => 'resolved',
            originalResolver: new ServiceResolver([new VoidAuthorizationService(), 'isAllowed']),
            middlewareAnnotations: new MiddlewareAnnotations($annotations),
        );

        $language = new ExpressionLanguage();
        $language->registerProvider(new SecurityExpressionLanguageProvider());

        $middleware = new SecurityInputFieldMiddleware(
            $language,
            new VoidAuthenticationService(),
            new VoidAuthorizationService(),
            new CallableResolver(new EmptyContainer()),
        );

        return $middleware->process($descriptor, new class implements InputFieldHandlerInterface {
            public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
            {
                return new InputField(
                    name: $inputFieldDescriptor->getName(),
                    type: $inputFieldDescriptor->getType(),
                    arguments: ['foo' => new SourceParameter()],
                    originalResolver: $inputFieldDescriptor->getOriginalResolver(),
                    resolver: $inputFieldDescriptor->getResolver(),
                    forConstructorHydration: false,
                    description: null,
                    isUpdate: false,
                    hasDefaultValue: false,
                    defaultValue: null,
                );
            }
        });
    }

    private function resolveField(InputField $field): mixed
    {
        return $field->getResolve()(new stdClass(), [], null, $this->createStub(ResolveInfo::class));
    }
}
