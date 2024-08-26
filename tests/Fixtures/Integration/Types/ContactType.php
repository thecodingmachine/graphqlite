<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use RuntimeException;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;

use function array_filter;
use function array_search;
use function strtoupper;

#[ExtendType(class: Contact::class)]
#[SourceField(name: 'name', phpType: 'string')]
#[SourceField(name: 'deprecatedName', phpType: 'string')]
#[SourceField(name: 'birthDate')]
#[SourceField(name: 'manager')]
#[SourceField(name: 'relations')]
#[SourceField(name: 'injectServiceFromExternal', annotations: [new Autowire(for: 'testService', identifier: 'testService'), new HideParameter(for: 'testSkip'), new UseInputType(for: '$id', inputType: 'String')])]
class ContactType
{
    #[Field]
    public function customField(Contact $contact, string $prefix): string
    {
        return $prefix . ' ' . strtoupper($contact->getName());
    }

    #[Field]
    public function repeatName(Contact $contact, #[Prefetch('prefetchContacts')]
    $data, string $suffix,): string
    {
        $index = array_search($contact, $data['contacts'], true);
        if ($index === false) {
            throw new RuntimeException('Index not found');
        }
        return $data['prefix'] . $data['contacts'][$index]->getName() . $suffix;
    }

    public static function prefetchContacts(iterable $contacts, string $prefix)
    {
        return [
            'contacts' => $contacts,
            'prefix' => $prefix,
        ];
    }

    /** @return Post[]|null */
    #[Field]
    public function getPosts(
        Contact $contact,
        #[Prefetch('prefetchPosts')]
        $posts,
    ): array|null {
        return $posts[$contact->getName()] ?? null;
    }

    public static function prefetchPosts(iterable $contacts): array
    {
        $posts = [];
        foreach ($contacts as $contact) {
            $contactPost = array_filter(
                self::getContactPosts(),
                static fn (Post $post) => $post->author?->getName() === $contact->getName(),
            );

            if (! $contactPost) {
                continue;
            }

            $posts[$contact->getName()] = $contactPost;
        }

        return $posts;
    }

    private static function getContactPosts(): array
    {
        return [
            self::generatePost('First Joe post', 1, new Contact('Joe')),
            self::generatePost('First Bill post', 2, new Contact('Bill')),
            self::generatePost('First Kate post', 3, new Contact('Kate')),
        ];
    }

    private static function generatePost(
        string $title,
        int $id,
        Contact $author,
    ): Post {
        $post = new Post($title);
        $post->id = $id;
        $post->author = $author;
        return $post;
    }
}
