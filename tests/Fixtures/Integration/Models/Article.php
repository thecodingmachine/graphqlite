<?php

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Models;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Input;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Undefined;


#[Type, Input]
class Article extends Post
{


    #[Field(for: "Article")]
    public int $id = 2;

    #[Field]
    public ?string $magazine = null;

    #[Field(for: 'Article')]
    public function localizedTitle(string|null|Undefined $locale): string
    {
        if ($locale === Undefined::VALUE) {
            $locale = 'en_US';
        }

        return $locale ? "Localized ($locale): $this->title" : $this->title;
    }
}
