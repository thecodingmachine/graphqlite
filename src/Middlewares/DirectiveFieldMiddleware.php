<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Directives\BehavioralFieldDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveAstBuilder;
use TheCodingMachine\GraphQLite\Directives\FieldDirective;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

use function array_filter;
use function array_reverse;
use function array_values;

/**
 * Dispatches the {@see FieldDirective}s on a field. The ones implementing
 * {@see BehavioralFieldDirective} run their `applyToField` hook; metadata-only directives don't run
 * anything but still get their `astNode` so they show up in SDL.
 *
 * @internal
 */
final readonly class DirectiveFieldMiddleware implements FieldMiddlewareInterface
{
    public function __construct(private DirectiveAstBuilder $astBuilder)
    {
    }

    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): FieldDefinition|null
    {
        /** @var list<FieldDirective> $directives */
        $directives = $queryFieldDescriptor
            ->getMiddlewareAnnotations()
            ->getAnnotationsByType(FieldDirective::class);

        if ($directives === []) {
            return $fieldHandler->handle($queryFieldDescriptor);
        }

        $behavioralDirectives = array_values(array_filter(
            $directives,
            static fn (FieldDirective $directive): bool => $directive instanceof BehavioralFieldDirective,
        ));

        $handler = $fieldHandler;
        foreach (array_reverse($behavioralDirectives) as $directive) {
            $handler = new class ($directive, $handler) implements FieldHandlerInterface {
                public function __construct(
                    private readonly BehavioralFieldDirective $directive,
                    private readonly FieldHandlerInterface $next,
                ) {
                }

                public function handle(QueryFieldDescriptor $fieldDescriptor): FieldDefinition|null
                {
                    return $this->directive->applyToField($fieldDescriptor, $this->next);
                }
            };
        }

        $result = $handler->handle($queryFieldDescriptor);
        if ($result === null) {
            return null;
        }

        $result->astNode = DirectiveAstBuilder::buildFieldDefinitionNode(
            $result->name,
            $this->astBuilder->buildDirectiveNodes($directives),
        );

        return $result;
    }
}
