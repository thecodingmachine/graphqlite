<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Fixtures\Description;

use TheCodingMachine\GraphQLite\Annotations\Factory;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use TheCodingMachine\GraphQLite\Annotations\Query;

class LibraryController
{
    /**
     * Implementation-only note: this docblock must be overridden by the #[Query] description.
     */
    #[Query(description: 'Fetch a single library book.')]
    public function book(): Book
    {
        return new Book('The Great Gatsby');
    }

    /**
     * Docblock-only description used as the fallback source when no explicit description is set.
     */
    #[Query]
    public function author(): Author
    {
        return new Author('F. Scott Fitzgerald');
    }

    /**
     * Implementation note — should be replaced by the explicit mutation description.
     */
    #[Mutation(description: 'Borrow a book from the library.')]
    public function borrowBook(string $title): Book
    {
        return new Book($title);
    }

    /**
     * @Factory for {@see BookSearchCriteria}. The docblock text must be ignored when the
     * explicit factory description is provided.
     */
    #[Factory(description: 'Search criteria for finding books in the library catalog.')]
    public function bookSearchCriteria(string $title): BookSearchCriteria
    {
        return new BookSearchCriteria($title);
    }

    #[Query]
    public function genre(): Genre
    {
        return Genre::Fiction;
    }
}
