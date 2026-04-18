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
- `#[EnumValue]` — individual cases of an enum type (see [Enum value descriptions](#enum-value-descriptions))

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

## Enum value descriptions

Native PHP 8.1 enums mapped to GraphQL enum types get per-case metadata via the `#[EnumValue]`
attribute applied to individual cases:

```php
use TheCodingMachine\GraphQLite\Annotations\EnumValue;
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
enum Genre: string
{
    #[EnumValue(description: 'Fiction works including novels and short stories.')]
    case Fiction = 'fiction';

    #[EnumValue(deprecationReason: 'Use Fiction::Verse instead.')]
    case Poetry = 'poetry';

    /**
     * Works grounded in verifiable facts.
     */
    case NonFiction = 'non-fiction'; // no attribute — description comes from the docblock
}
```

The attribute name mirrors the GraphQL specification's term ("enum values", see
[spec §3.5.2](https://spec.graphql.org/October2021/#sec-Enum-Values)) and matches webonyx/graphql-php's
`EnumValueDefinition`. The underlying PHP construct is a `case`; the GraphQL element it produces
is an enum value.

`#[EnumValue]` accepts:

- `description` — schema description for this enum value. Omitting it falls back to the case
  docblock summary, subject to the same precedence rules as every other attribute's
  `description` argument. An explicit empty string `''` deliberately suppresses the docblock
  fallback.
- `deprecationReason` — published as the enum value's `deprecationReason` in the schema.
  Omitting it falls back to the `@deprecated` tag on the case docblock. An explicit empty string
  `''` deliberately clears any inherited `@deprecated` tag.

### Future migration: `#[EnumValue]` will become required per `#[Type]`-mapped enum

Today every case of a `#[Type]`-mapped enum is automatically exposed in the GraphQL schema. A
future major release will flip this to an opt-in model: **`#[Type]`-mapped enums will need at
least one `#[EnumValue]`-annotated case, and only annotated cases will participate in the
schema**, mirroring the way `#[Field]` opts individual methods and properties into an object
type.

The benefit is selective exposure — today there is no way to map a subset of a PHP enum into
GraphQL, which forces schema authors to split an enum into two or rename cases. Under the
opt-in model, an internal enum case can simply omit `#[EnumValue]` to stay out of the public
schema. **Partial annotation is intentional and deliberately silent**: leaving some cases
unannotated is the mechanism for hiding them from the public schema once the default flips,
not a migration oversight.

To surface the upcoming change, GraphQLite emits a PHP `E_USER_DEPRECATED` notice at schema
build time only when a `#[Type]`-mapped enum declares **zero** `#[EnumValue]` attributes
across its cases — the signal that the developer has not yet engaged with the opt-in model at
all. Annotating at least one case acknowledges the new model and silences the notice; after
that, any combination of annotated and unannotated cases is correct and intentional.

Adopters who aren't ready to decide which cases to expose can silence the advisory with a
bare `#[EnumValue]` on a single case — no description, no deprecation, just the acknowledgement:

```php
#[Type]
enum Size
{
    #[EnumValue] // acknowledges the opt-in migration; runtime behaviour unchanged
    case S;
    case M;
    case L;
}
```

**Runtime implication after the flip.** Cases without `#[EnumValue]` will not exist in the
GraphQL schema, so a resolver that returns such a case will trigger webonyx/graphql-php's
standard enum serialization error (the value is not listed in the enum type's `values`
config). This is the same spec-compliant behaviour that applies to any unknown enum value and
is the mechanism that makes selective exposure safe: internal cases cannot accidentally leak
via a resolver. Developers who want a case to remain returnable must keep `#[EnumValue]` on
it; omitting the attribute is a deliberate "do not expose this value" signal.

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
