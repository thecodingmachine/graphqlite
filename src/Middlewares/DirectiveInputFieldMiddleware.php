<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Middlewares;

use TheCodingMachine\GraphQLite\Directives\BehavioralInputFieldDirective;
use TheCodingMachine\GraphQLite\Directives\DirectiveAstBuilder;
use TheCodingMachine\GraphQLite\Directives\InputFieldDirective;
use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;

use function array_filter;
use function array_reverse;
use function array_values;

/**
 * Dispatches the {@see InputFieldDirective}s on an input field. The ones implementing
 * {@see BehavioralInputFieldDirective} run their `applyToInputField` hook; metadata-only directives
 * still get their `astNode` so they show up in SDL.
 *
 * @internal
 */
final readonly class DirectiveInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    public function __construct(private DirectiveAstBuilder $astBuilder)
    {
    }

    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): InputField|null
    {
        /** @var list<InputFieldDirective> $directives */
        $directives = $inputFieldDescriptor
            ->getMiddlewareAnnotations()
            ->getAnnotationsByType(InputFieldDirective::class);

        if ($directives === []) {
            return $inputFieldHandler->handle($inputFieldDescriptor);
        }

        $behavioralDirectives = array_values(array_filter(
            $directives,
            static fn (InputFieldDirective $directive): bool => $directive instanceof BehavioralInputFieldDirective,
        ));

        $handler = $inputFieldHandler;
        foreach (array_reverse($behavioralDirectives) as $directive) {
            $handler = new class ($directive, $handler) implements InputFieldHandlerInterface {
                public function __construct(
                    private readonly BehavioralInputFieldDirective $directive,
                    private readonly InputFieldHandlerInterface $next,
                ) {
                }

                public function handle(InputFieldDescriptor $inputFieldDescriptor): InputField|null
                {
                    return $this->directive->applyToInputField($inputFieldDescriptor, $this->next);
                }
            };
        }

        $result = $handler->handle($inputFieldDescriptor);
        if ($result === null) {
            return null;
        }

        $astNode = DirectiveAstBuilder::buildInputValueDefinitionNode(
            $result->name,
            $this->astBuilder->buildDirectiveNodes($directives),
        );

        // InputObjectType reconstructs each field from $field->config (see InputType.php:51),
        // so mutating only the astNode property would not survive. Update the config too.
        $result->astNode = $astNode;
        $result->config['astNode'] = $astNode;

        return $result;
    }
}
