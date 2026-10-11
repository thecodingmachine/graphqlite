<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Validator\Rules;

use GraphQL\Error\Error;
use GraphQL\Executor\Values;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\Node;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Type\Schema;
use GraphQL\Validator\QueryValidationContext;
use GraphQL\Validator\Rules\QueryComplexity as WebonyxQueryComplexity;

use function is_callable;

/**
 * Limits operation complexity, leaving invalid variables to the executor
 *
 * Pricing a field by its arguments coerces the operation's variables during validation, and webonyx
 * throws a single error joining every coercion message, without locations. When the variables really
 * fail coercion, this rule reports nothing, so the executor refuses them with one located error per
 * variable. Any other error is rethrown, so it can't disable the limit.
 */
final class QueryComplexity extends WebonyxQueryComplexity
{
    /** @inheritDoc */
    public function getVisitor(QueryValidationContext $context): array
    {
        $visitor = parent::getVisitor($context);
        $documentVisitor = $visitor[NodeKind::DOCUMENT] ?? null;
        if ($documentVisitor === null || is_callable($documentVisitor)) {
            return $visitor;
        }

        $leave = $documentVisitor['leave'] ?? null;
        if ($leave === null) {
            return $visitor;
        }

        $documentVisitor['leave'] = function (Node $node, mixed ...$arguments) use ($leave, $context): void {
            try {
                $leave($node, ...$arguments);
            } catch (Error $error) {
                if (! $node instanceof DocumentNode || ! $this->variablesFailCoercion($context->getSchema(), $node)) {
                    throw $error;
                }
            }
        };
        $visitor[NodeKind::DOCUMENT] = $documentVisitor;

        return $visitor;
    }

    /**
     * Whether the executor will refuse the variables, whichever operation of the document it runs
     *
     * The operation name isn't known during validation, so every operation's own variable
     * definitions must fail coercion.
     */
    private function variablesFailCoercion(Schema $schema, DocumentNode $document): bool
    {
        $failing = false;
        foreach ($document->definitions as $definition) {
            if (! $definition instanceof OperationDefinitionNode) {
                continue;
            }

            [$errors] = Values::getVariableValues($schema, $definition->variableDefinitions, $this->getRawVariableValues());
            if ($errors === null) {
                return false;
            }

            $failing = true;
        }

        return $failing;
    }
}
