---
id: descriptions
title: Schema descriptions
sidebar_label: Descriptions
---

Every schema element that a GraphQL client can see — operations, types, fields, arguments, input
types, enum types — carries a human-readable **description** that GraphiQL and other tooling
surface to API consumers. GraphQLite lets you control that description in two complementary ways:
an explicit argument on the PHP attribute, or a PHP docblock summary used as a fallback.

## Setting an explicit description

Every schema-defining attribute accepts a `description` argument:

```php
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type(description: 'A library book available for checkout.')]
class Book
{
    #[Field(description: 'The book title as it appears on the cover.')]
    public function getTitle(): string { /* ... */ }
}

class LibraryController
{
    #[Query(description: 'Fetch a single library book.')]
    public function book(): Book { /* ... */ }
}
```

The attributes that accept `description`:

- `#[Query]`, `#[Mutation]`, `#[Subscription]` — operations
- `#[Type]`, `#[ExtendType]` — object and enum types
- `#[Factory]` — input types produced by factories
- `#[Field]`, `#[Input]`, `#[SourceField]`, `#[MagicField]` — fields on output and input types

## Docblock fallback

When an attribute does not specify a `description`, GraphQLite falls back to the corresponding PHP
docblock summary:

```php
/**
 * Fetch a single library book.
 */
#[Query]
public function book(): Book { /* ... */ }
```

Both examples above produce the same GraphQL description (`Fetch a single library book.`) in the
generated schema. Docblock fallback is enabled by default for backwards compatibility.

## Precedence

At every schema element, GraphQLite resolves the description from the first source that provides
one:

1. **Explicit `description: '…'`** on the attribute — always wins.
2. **Explicit `description: ''`** (empty string) — also wins; deliberately publishes an empty
   description and suppresses the docblock fallback at that site. Use this when an internal
   docblock exists but no public description is desired.
3. **Docblock summary** — used when the attribute did not provide a `description` and docblock
   fallback is enabled on the `SchemaFactory`.
4. Otherwise the schema description is empty.

## Disabling the docblock fallback

Docblocks double as developer-facing notes (implementation reminders, `@see` references, TODOs).
Publishing them verbatim to the public schema can accidentally leak internal context. Disable the
fallback on the `SchemaFactory` to guarantee that only descriptions explicitly written for API
consumers ever reach the schema:

```php
$factory = new SchemaFactory($cache, $container);
$factory->setDocblockDescriptionsEnabled(false);
```

When disabled, every schema element that lacked an explicit `description` on its attribute will
have no description at all. To publish an empty description for a specific element without
disabling the whole fallback, pass an empty string:

```php
#[Query(description: '')]
public function internalOnly(): Foo { /* ... */ }
```

## Description uniqueness on `#[ExtendType]`

A GraphQL type has exactly one description, so GraphQLite enforces that the description for a
given type is declared in exactly one place. Valid configurations:

- `#[Type(description: '…')]` alone — the base type owns the description.
- `#[Type]` with no description plus one `#[ExtendType(description: '…')]` — the extension owns
  it. This is useful when an application describes a third-party library's type that did not ship
  with a description.
- Neither declares a description — the description falls back to the class docblock summary when
  docblock descriptions are enabled, otherwise it is empty.

If a `description` is declared on both the `#[Type]` and any `#[ExtendType]`, or on more than one
`#[ExtendType]` targeting the same class, schema construction fails fast with a
`TheCodingMachine\GraphQLite\Annotations\Exceptions\DuplicateDescriptionOnTypeException`. The
exception message names every offending source so the conflict can be resolved without guesswork.
