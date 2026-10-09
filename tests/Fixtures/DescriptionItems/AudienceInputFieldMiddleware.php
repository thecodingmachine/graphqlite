<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DescriptionItems;

use TheCodingMachine\GraphQLite\InputField;
use TheCodingMachine\GraphQLite\InputFieldDescriptor;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldHandlerInterface;
use TheCodingMachine\GraphQLite\Middlewares\InputFieldMiddlewareInterface;

class AudienceInputFieldMiddleware implements InputFieldMiddlewareInterface
{
    public function process(InputFieldDescriptor $inputFieldDescriptor, InputFieldHandlerInterface $inputFieldHandler): InputField|null
    {
        foreach ($inputFieldDescriptor->getMiddlewareAnnotations()->getAnnotationsByType(Audience::class) as $audience) {
            $inputFieldDescriptor = $inputFieldDescriptor->withAddedDescriptionItem('Audience: ' . $audience->name);
        }

        return $inputFieldHandler->handle($inputFieldDescriptor);
    }
}
