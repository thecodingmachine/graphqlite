<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Directives;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputObjectTypeDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Utils\AST;
use ReflectionClass;

/**
 * Builds the AST nodes that make directive applications visible in printed SDL.
 *
 * webonyx renders directive applications via `astNode->directives` on the parent definition node
 * (FieldDefinitionNode, InputValueDefinitionNode, ObjectTypeDefinitionNode,
 * InputObjectTypeDefinitionNode). GraphQLite does not populate `astNode` anywhere else today, so
 * this builder constructs the minimal node graph required for SDL output to include each applied
 * directive, with its arguments encoded via {@see AST::astFromValue}.
 *
 * @internal
 */
final class DirectiveAstBuilder
{
    public function __construct(private readonly DirectiveRegistry $registry)
    {
    }

    /**
     * @param list<DirectiveInterface> $directives Directive instances applied to this element.
     *
     * @return list<DirectiveNode>
     */
    public function buildDirectiveNodes(array $directives): array
    {
        $nodes = [];
        foreach ($directives as $directive) {
            $definition = $this->registry->definitionFor($directive::class);
            if ($definition === null) {
                continue;
            }
            $nodes[] = $this->buildDirectiveNode($directive, $definition);
        }
        return $nodes;
    }

    private function buildDirectiveNode(DirectiveInterface $directive, DirectiveDefinition $definition): DirectiveNode
    {
        $arguments = $this->registry->argumentsFor($directive::class);
        $reflection = new ReflectionClass($directive);

        $argumentNodes = [];
        foreach ($arguments as $argument) {
            if (! $reflection->hasProperty($argument->name)) {
                continue;
            }
            $property = $reflection->getProperty($argument->name);
            $value = $property->getValue($directive);

            $argumentNodes[] = new ArgumentNode([
                'name' => new NameNode(['value' => $argument->name]),
                'value' => AST::astFromValue($value, $argument->type),
            ]);
        }

        return new DirectiveNode([
            'name' => new NameNode(['value' => $definition->name]),
            'arguments' => new NodeList($argumentNodes),
        ]);
    }

    /** @param list<DirectiveNode> $directiveNodes */
    public static function buildFieldDefinitionNode(string $name, array $directiveNodes): FieldDefinitionNode
    {
        return new FieldDefinitionNode([
            'name' => new NameNode(['value' => $name]),
            'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Unknown'])]),
            'arguments' => new NodeList([]),
            'directives' => new NodeList($directiveNodes),
        ]);
    }

    /** @param list<DirectiveNode> $directiveNodes */
    public static function buildInputValueDefinitionNode(string $name, array $directiveNodes): InputValueDefinitionNode
    {
        return new InputValueDefinitionNode([
            'name' => new NameNode(['value' => $name]),
            'type' => new NamedTypeNode(['name' => new NameNode(['value' => 'Unknown'])]),
            'directives' => new NodeList($directiveNodes),
        ]);
    }

    /** @param list<DirectiveNode> $directiveNodes */
    public static function buildObjectTypeDefinitionNode(string $name, array $directiveNodes): ObjectTypeDefinitionNode
    {
        return new ObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $name]),
            'interfaces' => new NodeList([]),
            'directives' => new NodeList($directiveNodes),
            'fields' => new NodeList([]),
        ]);
    }

    /** @param list<DirectiveNode> $directiveNodes */
    public static function buildInputObjectTypeDefinitionNode(string $name, array $directiveNodes): InputObjectTypeDefinitionNode
    {
        return new InputObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $name]),
            'directives' => new NodeList($directiveNodes),
            'fields' => new NodeList([]),
        ]);
    }
}
