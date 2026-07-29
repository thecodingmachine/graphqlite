<?php

namespace TheCodingMachine\GraphQLite\Middlewares;

use Closure;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\Type;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use ReflectionMethod;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotationInterface;
use TheCodingMachine\GraphQLite\Annotations\MiddlewareAnnotations;
use TheCodingMachine\GraphQLite\Annotations\Security;
use TheCodingMachine\GraphQLite\CallableResolver;
use TheCodingMachine\GraphQLite\Containers\EmptyContainer;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;
use TheCodingMachine\GraphQLite\Security\SecurityExpressionLanguageProvider;
use TheCodingMachine\GraphQLite\Security\SecurityRuleContext;
use TheCodingMachine\GraphQLite\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQLite\Security\VoidAuthorizationService;

use function class_exists;

/**
 * Covers what #[Security] does at schema build (linting expressions, resolving rules) and what the
 * resulting resolver does at request time, for rule forms that cannot be written in a fixture
 * attribute on the supported PHP floor.
 */
class SecurityFieldMiddlewareTest extends TestCase
{
    public function testMalformedExpressionIsRejectedWhileTheSchemaIsBuilt(): void
    {
        $this->expectException(BadExpressionInSecurityException::class);
        $this->expectExceptionMessage('is not valid');

        $this->process([new Security('this is not valid expression language')]);
    }

    /**
     * Linting must not gate on GraphQLite's own two functions. Anything the consumer registered on
     * the ExpressionLanguage they supplied has to be accepted too.
     */
    public function testExpressionMayCallAFunctionRegisteredByTheConsumer(): void
    {
        $language = $this->language();
        $language->register('is_owner', static fn () => 'true', static fn (array $v) => true);

        $field = $this->process([new Security('is_owner(this)')], $language);

        self::assertNotNull($field);
    }

    public function testUnknownFunctionIsStillRejected(): void
    {
        $this->expectException(BadExpressionInSecurityException::class);
        $this->expectExceptionMessage('does not exist');

        $this->process([new Security('no_such_function(this)')]);
    }

    /**
     * Pins the lint name list against getVariables(). If a variable is added to the bag without
     * being added to VARIABLE_NAMES, expressions using it would lint as unknown and this fails.
     */
    #[DataProvider('providedVariableNames')]
    public function testExpressionCanReferenceEveryProvidedVariable(string $variable): void
    {
        $field = $this->process([new Security($variable . ' == null')]);

        self::assertNotNull($field);
    }

    public static function providedVariableNames(): iterable
    {
        yield 'user' => ['user'];
        yield 'this' => ['this'];
        yield 'authorizationService' => ['authorizationService'];
        yield 'authenticationService' => ['authenticationService'];
    }

    /**
     * Linting initialises the parser, and ExpressionLanguage refuses register() afterwards. The
     * middleware lints a clone so a consumer holding the real instance can still register on it,
     * exactly as they could before expressions were checked at build time.
     */
    public function testBuildingTheSchemaLeavesTheExpressionLanguageRegisterable(): void
    {
        $language = $this->language();

        $this->process([new Security("is_granted('FOO')")], $language);

        $language->register('registered_afterwards', static fn () => 'true', static fn (array $v) => true);

        self::assertTrue($language->evaluate('registered_afterwards()', []));
    }

    /**
     * A Closure is a supported rule form. First-class callable syntax (`Rules::allow(...)`) is how
     * you write one in an attribute on PHP 8.5, but it cannot appear in a fixture here because the
     * floor is 8.2 — and it does not need to. What reaches the middleware is a Closure either way,
     * and Closure::fromCallable() produces one indistinguishable from what FCC yields.
     */
    public function testClosureRuleGrantsAccess(): void
    {
        $field = $this->process([new Security(rule: static fn (SecurityRuleContext $context): bool => true)]);

        self::assertNotNull($field);
        self::assertSame('resolved', ($field->resolveFn)(null));
    }

    public function testClosureRuleDeniesAccess(): void
    {
        $field = $this->process([
            new Security(rule: static fn (SecurityRuleContext $context): bool => false, message: 'Nope'),
        ]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        $this->expectExceptionMessage('Nope');
        ($field->resolveFn)(null);
    }

    /** The exact shape first-class callable syntax produces. */
    public function testClosureFromACallableActsAsARule(): void
    {
        $field = $this->process([new Security(rule: Closure::fromCallable([self::class, 'denyEverything']))]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        ($field->resolveFn)(null);
    }

    public function testClosureRuleIsHandedTheSecurityRuleContext(): void
    {
        $seen = null;

        $field = $this->process([
            new Security(rule: static function (SecurityRuleContext $context) use (&$seen): bool {
                $seen = $context;

                return true;
            }),
        ]);

        ($field->resolveFn)(null);

        self::assertInstanceOf(SecurityRuleContext::class, $seen);
    }

    /**
     * First-class callable syntax — `Rules::allow(...)` — written directly in the attribute.
     *
     * The class is built with eval() on purpose. FCC in a constant expression is a COMPILE-time
     * fatal before PHP 8.5, and every .php file under tests/ gets compiled during integration runs
     * because SchemaFactory falls back to ComposerFinder, which walks Composer's autoload paths.
     * #[RequiresPhp] gates execution, not compilation, so a normal fixture file would kill the 8.2,
     * 8.3 and 8.4 cells no matter how it was annotated. eval() defers the compile until after this
     * guard has already decided to run.
     */
    #[RequiresPhp('>= 8.5')]
    public function testFirstClassCallableSyntaxActsAsARule(): void
    {
        if (! class_exists('FirstClassCallableRuleProbe', false)) {
            eval(<<<'PROBE'
                class FirstClassCallableRuleProbe
                {
                    #[\TheCodingMachine\GraphQLite\Annotations\Security(
                        rule: \TheCodingMachine\GraphQLite\Middlewares\SecurityFieldMiddlewareTest::denyEverything(...),
                    )]
                    public function guarded(): string
                    {
                        return 'never';
                    }
                }
                PROBE);
        }

        $rule = (new ReflectionMethod('FirstClassCallableRuleProbe', 'guarded'))
            ->getAttributes(Security::class)[0]
            ->newInstance()
            ->getRule();

        // FCC yields a genuine Closure that still knows the method it came from.
        self::assertInstanceOf(Closure::class, $rule);
        self::assertSame('denyEverything', (new ReflectionFunction($rule))->getName());

        $field = $this->process([new Security(rule: $rule)]);

        self::assertNotNull($field);

        $this->expectException(MissingAuthorizationException::class);
        ($field->resolveFn)(null);
    }

    public static function denyEverything(SecurityRuleContext $context): bool
    {
        return false;
    }

    /** @param MiddlewareAnnotationInterface[] $annotations */
    private function process(array $annotations, ExpressionLanguage|null $language = null): FieldDefinition|null
    {
        $resolver = static fn (): string => 'resolved';

        $descriptor = new QueryFieldDescriptor(
            name: 'foo',
            type: Type::string(),
            resolver: $resolver,
            originalResolver: new ServiceResolver([new VoidAuthorizationService(), 'isAllowed']),
            middlewareAnnotations: new MiddlewareAnnotations($annotations),
        );

        $middleware = new SecurityFieldMiddleware(
            $language ?? $this->language(),
            new VoidAuthenticationService(),
            new VoidAuthorizationService(),
            new CallableResolver(new EmptyContainer()),
        );

        return $middleware->process($descriptor, new class implements FieldHandlerInterface {
            public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
            {
                return new FieldDefinition([
                    'name' => $fieldDescriptor->getName(),
                    'resolve' => $fieldDescriptor->getResolver(),
                ]);
            }
        });
    }

    private function language(): ExpressionLanguage
    {
        $language = new ExpressionLanguage();
        $language->registerProvider(new SecurityExpressionLanguageProvider());

        return $language;
    }
}
