<?php


namespace TheCodingMachine\GraphQLite\Fixtures\Integration\Types;

use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Post;
use TheCodingMachine\GraphQLite\Annotations\Prefetch;
use function array_search;
use function strtoupper;
use TheCodingMachine\GraphQLite\Annotations\ExtendType;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\SourceField;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\HideParameter;
use TheCodingMachine\GraphQLite\Annotations\UseInputType;

/**
 * @ExtendType(class=Contact::class)
 * @SourceField(name="name", phpType="string")
 * @SourceField(name="deprecatedName", phpType="string")
 * @SourceField(name="birthDate")
 * @SourceField(name="manager")
 * @SourceField(name="relations")
 * @SourceField(name="injectServiceFromExternal", annotations={@Autowire(for="testService", identifier="testService"), @HideParameter(for="testSkip"), @UseInputType(for="$id", inputType="String")})
 */
class ContactType
{
    /**
     * @Field()
     */
    public function customField(Contact $contact, string $prefix): string
    {
        return $prefix.' '.strtoupper($contact->getName());
    }

    /**
     * @Field()
     */
    public function repeatName(Contact $contact, #[Prefetch('prefetchContacts')] $data, string $suffix): string
    {
        $index = array_search($contact, $data['contacts'], true);
        if ($index === false) {
            throw new \RuntimeException('Index not found');
        }
        return $data['prefix'].$data['contacts'][$index]->getName().$suffix;
    }

    public static function prefetchContacts(iterable $contacts, string $prefix)
    {
        return [
            'contacts' => $contacts,
            'prefix' => $prefix
        ];
    }

    /**
     * @Field(prefetchMethod="prefetchPosts")
     * @return Post[]|null
     */
    public function getPosts($contact, $posts): ?array
    {
        return $posts[$contact->getName()] ?? null;
    }

    public function prefetchPosts(iterable $contacts): array
    {
        $posts = [];
        foreach ($contacts as $contact) {
            $contactPost = array_filter(
                $this->getContactPosts(),
                fn(Post $post) => $post->author?->getName() === $contact->getName()
            );

            if ([] === $contactPost) {
                continue;
            }

            $posts[$contact->getName()] = $contactPost;
        }

        return $posts;
    }

    private function getContactPosts(): array
    {
        return [
            $this->generatePost('First Joe post', '1', new Contact('Joe')),
            $this->generatePost('First Bill post', '2', new Contact('Bill')),
            $this->generatePost('First Kate post', '3', new Contact('Kate')),
        ];
    }

    private function generatePost(
        string $title,
        string $id,
        Contact $author,
    ): Post {
        $post = new Post($title);
        $post->id = $id;
        $post->author = $author;
        return $post;
    }
}
