<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Directives\BehavioralObjectTypeDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveAstBuilder;
use TheCodingMachine\GraphQLite\ObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableObjectType;

use function array_filter;
use function array_reverse;
use function array_values;

/**
 * Dispatches the {@see \TheCodingMachine\GraphQLite\Directives\ObjectTypeDirective}s on a `#[Type]`
 * class. The ones implementing {@see BehavioralObjectTypeDirective} run their `applyToObjectType`
 * hook; metadata-only directives still get an `astNode` for SDL.
 *
 * @internal
 */
final readonly class DirectiveObjectTypeMiddleware implements ObjectTypeMiddlewareInterface
{
    public function __construct(private DirectiveAstBuilder $astBuilder)
    {
    }

    public function process(ObjectTypeDescriptor $descriptor, ObjectTypeHandlerInterface $next): MutableObjectType
    {
        $directives = $descriptor->getDirectives();

        if ($directives === []) {
            return $next->handle($descriptor);
        }

        $behavioralDirectives = array_values(array_filter(
            $directives,
            static fn ($directive): bool => $directive instanceof BehavioralObjectTypeDirective,
        ));

        $handler = $next;
        foreach (array_reverse($behavioralDirectives) as $directive) {
            $handler = new class ($directive, $handler) implements ObjectTypeHandlerInterface {
                public function __construct(
                    private readonly BehavioralObjectTypeDirective $directive,
                    private readonly ObjectTypeHandlerInterface $next,
                ) {
                }

                public function handle(ObjectTypeDescriptor $descriptor): MutableObjectType
                {
                    return $this->directive->applyToObjectType($descriptor, $this->next);
                }
            };
        }

        $type = $handler->handle($descriptor);

        $type->astNode = DirectiveAstBuilder::buildObjectTypeDefinitionNode(
            $type->name,
            $this->astBuilder->buildDirectiveNodes($directives),
        );

        return $type;
    }
}
