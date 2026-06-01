<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Directives\BehavioralInputObjectTypeDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveAstBuilder;
use TheCodingMachine\GraphQLite\InputObjectTypeDescriptor;
use TheCodingMachine\GraphQLite\Types\MutableInputObjectType;

use function array_filter;
use function array_reverse;
use function array_values;

/**
 * Dispatches every {@see \TheCodingMachine\GraphQLite\Directives\InputObjectTypeDirective} declared
 * on a class with `#[Input]` (or via `#[Factory]`). Only the subset implementing
 * {@see BehavioralInputObjectTypeDirective} runs its `applyToInputObjectType` hook; metadata-only
 * directives still get an `astNode` for SDL emission.
 *
 * @internal
 */
final readonly class DirectiveInputObjectTypeMiddleware implements InputObjectTypeMiddlewareInterface
{
    public function __construct(private DirectiveAstBuilder $astBuilder)
    {
    }

    public function process(InputObjectTypeDescriptor $descriptor, InputObjectTypeHandlerInterface $next): MutableInputObjectType
    {
        $directives = $descriptor->getDirectives();

        if ($directives === []) {
            return $next->handle($descriptor);
        }

        $behavioralDirectives = array_values(array_filter(
            $directives,
            static fn ($directive): bool => $directive instanceof BehavioralInputObjectTypeDirective,
        ));

        $handler = $next;
        foreach (array_reverse($behavioralDirectives) as $directive) {
            $handler = new class ($directive, $handler) implements InputObjectTypeHandlerInterface {
                public function __construct(
                    private readonly BehavioralInputObjectTypeDirective $directive,
                    private readonly InputObjectTypeHandlerInterface $next,
                ) {
                }

                public function handle(InputObjectTypeDescriptor $descriptor): MutableInputObjectType
                {
                    return $this->directive->applyToInputObjectType($descriptor, $this->next);
                }
            };
        }

        $type = $handler->handle($descriptor);

        $type->astNode = DirectiveAstBuilder::buildInputObjectTypeDefinitionNode(
            $type->name,
            $this->astBuilder->buildDirectiveNodes($directives),
        );

        return $type;
    }
}
