<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionItems;

use GraphQL\Type\Definition\FieldDefinition;
use TheCodingMachine\GraphQLite\Middlewares\FieldHandlerInterface;
use TheCodingMachine\GraphQLite\Middlewares\FieldMiddlewareInterface;
use TheCodingMachine\GraphQLite\QueryFieldDescriptor;

class AudienceFieldMiddleware implements FieldMiddlewareInterface
{
    public function process(QueryFieldDescriptor $queryFieldDescriptor, FieldHandlerInterface $fieldHandler): FieldDefinition|null
    {
        foreach ($queryFieldDescriptor->getMiddlewareAnnotations()->getAnnotationsByType(Audience::class) as $audience) {
            $queryFieldDescriptor = $queryFieldDescriptor->withAddedDescriptionItem('Audience: ' . $audience->name);
        }

        return $fieldHandler->handle($queryFieldDescriptor);
    }
}
