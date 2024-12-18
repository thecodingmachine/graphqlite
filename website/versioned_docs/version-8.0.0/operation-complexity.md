---
id: operation-complexity
title: Operation complexity
sidebar_label: Operation complexity
---

At some point you may find yourself receiving queries with an insane amount of requested
fields or items, all at once. Usually, it's not a good thing, so you may want to somehow
limit the amount of requests or their individual complexity. 

## Query depth

The simplest way to limit complexity is to limit the max query depth. `webonyx/graphql-php`,
which GraphQLite relies on, [has this built in](https://webonyx.github.io/graphql-php/security/#limiting-query-depth). 
To use it, you may use `addValidationRule` when building your PSR15 middleware:

```php
$builder = new Psr15GraphQLMiddlewareBuilder($schema);

$builder->addValidationRule(new \GraphQL\Validator\Rules\QueryDepth(7));
```

Although this works for simple cases, this doesn't prevent requesting an excessive amount
of fields on the depth of under 7, nor does it prevent requesting too many nodes in paginated lists.
This is where automatic query complexity comes to save us.

## Static request analysis

The operation complexity analyzer is a useful tool to make your API secure. The operation
complexity analyzer assigns by default every field a complexity of `1`. The complexity of
all fields in one of the operations of a GraphQL request is not allowed to be greater
than the maximum permitted operation complexity.

This sounds fairly simple at first, but the more you think about this, the more you 
wonder if that is so. Does every field have the same complexity?

In a data graph, not every field is the same. We have fields that fetch data that are 
more expensive than fields that just complete already resolved data.

```graphql
type Query {
    books(take: Int = 10): [Book]
}

type Book {
    title
    author: Author
}

type Author {
    name
}
```

In the above example executing the `books` field on the `Query` type might go to the 
database and fetch the `Book`. This means that the cost of the `books` field is 
probably higher than the cost of the `title` field. The cost of the title field 
might be the impact on the memory and to the transport. For `title`, the default 
cost of `1` os OK. But for `books`, we might want to go with a higher cost of `10` 
since we are getting a list of books from our database.

Moreover, we have the field `author` on the book, which might go to the database 
as well to fetch the `Author` object. Since we are only fetching a single item here, 
we might want to apply a cost of `5` to this field.

```php
class Controller {
    /**
    * @return Book[]
     */
    #[Query]
    #[Cost(complexity: 10)]
    public function books(int $take = 10): array {}
}

#[Type]
class Book {
    #[Field]
    public string $title;
    
    #[Field]
    #[Cost(complexity: 5)]
    public Author $author;
}

#[Type]
class Author {
    #[Field]
    public string $name;
}
```

If we run the following query against our data graph, we will come up with the cost of `11`.

```graphql
query {
    books {
        title
    }
}
```

When drilling in further, a cost of `17` occurs.

```graphql
query {
    books {
        title
        author {
            name
        }
    }
}
```

This kind of analysis is entirely static and could just be done by inspecting the 
query syntax tree. The impact on the overall execution performance is very low. 
But with this static approach, we do have a very rough idea of the performance. 
Is it correct to apply always a cost of `10` even though we might get one or one 
hundred books back?

## Full request analysis

The operation complexity analyzer can also take arguments into account when analyzing operation complexity.

If we look at our data graph, we can see that the `books` field actually has an argument 
that defines how many books are returned. The `take` argument, in this case, specifies 
the maximum books that the field will return.

When measuring the field\`s impact, we can take the argument `take` into account as a 
multiplier of our cost. This means we might want to lower the cost to `5` since now we 
get a more fine-grained cost calculation by multiplying the complexity 
of the field with the `take` argument.

```php
class Controller {
    /**
    * @return Book[]
     */
    #[Query]
    #[Cost(complexity: 5, multipliers: ['take'], defaultMultiplier: 200)]
    public function books(?int $take = 10): array {}
}

#[Type]
class Book {
    #[Field]
    public string $title;
    
    #[Field]
    #[Cost(complexity: 5)]
    public Author $author;
}

#[Type]
class Author {
    #[Field]
    public string $name;
}
```

With the multiplier in place, we now get a cost of `60` for the request since the multiplier 
is applied to the books field and the child fields' cost. If multiple multipliers are specified,
the cost will be multiplied by each of the fields.

Cost calculation: `10 * (5 + 1)`

```graphql
query {
    books {
        title
    }
}
```

When drilling in further, the cost will go up to `240` since we are now pulling twice as much books and also their authors.

Cost calculation: `20 * (5 + 1 + 5 + 1)`

```graphql
query {
    books(take: 20) {
        title
        author {
            name
        }
    }
}
```

Notice the nullable `$take` parameter. This might come in handy if `take: null` means "get all items",
but that would also mean that the overall complexity would only be `1 + 5 + 1 + 5 + 1 = 11`,
when in fact that would be a very costly query to execute. 

If all of the multiplier fields are either `null` or missing (and don't have default values),
`defaultMultiplier` is used:

Cost calculation: `200 * (5 + 1 + 5 + 1)`

```graphql
query {
    books(take: null) {
        title
        author {
            name
        }
    }
}
```

## Setup

As with query depth, automatic query complexity is configured through PSR15 middleware:

```php
$builder = new Psr15GraphQLMiddlewareBuilder($schema);

// Total query cost cannot exceed 1000 points
$builder->limitQueryComplexity(1000);
```

Beware that introspection queries would also be limited in complexity. A full introspection
query sits at around `107` points, so we recommend a minimum of `150` for query complexity limit.
