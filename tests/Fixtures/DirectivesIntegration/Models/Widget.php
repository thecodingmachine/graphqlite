<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\AuditDirective;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\TaggedDirective;
use TheCodingMachine\GraphQLite\Fixtures\DirectivesIntegration\Directives\UppercaseDirective;

#[Type]
#[TaggedDirective(name: 'primary')]
final class Widget
{
    public function __construct(public string $label)
    {
    }

    #[Field]
    #[UppercaseDirective]
    #[AuditDirective(reason: 'pii')]
    #[AuditDirective(reason: 'compliance')]
    public function getLabel(): string
    {
        return $this->label;
    }
}
