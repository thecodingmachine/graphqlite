---
id: version-4.0-queries
title: Queries
sidebar_label: Queries
original_id: queries
---

In GraphQLite, GraphQL queries are created by writing methods in *controller* classes.

Those classes must be in the controllers namespaces which has been defined when you configured GraphQLite.
For instance, in Symfony, the controllers namespace is `App\Controller` by default.

## Simple query

In a controller class, each query method must be annotated with the `@Query` annotation. For instance:

```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;

class MyController
{
    /**
     * @Query
     */
    public function hello(string $name): string
    {
        return 'Hello ' . $name;
    }
}
```

This query is equivalent to the following [GraphQL type language](https://graphql.org/learn/schema/#type-language):

```graphql
Type Query {
    hello(name: String!): String!
}
```

As you can see, GraphQLite will automatically do the mapping between PHP types and GraphQL types.

<div class="alert alert-warning"><strong>Heads up!</strong> If you are not using a framework with an autowiring container (like Symfony or Laravel), please be aware that the <code>MyController</code> class must exist in the container of your application. Furthermore, the identifier of the controller in the container MUST be the fully qualified class name of controller.</div>

## Testing the query

The default GraphQL endpoint is `/graphql`.

The easiest way to test a GraphQL endpoint is to use [GraphiQL](https://github.com/graphql/graphiql) or 
[Altair](https://altair.sirmuel.design/) clients (they are available as Chrome or Firefox plugins)

<div class="alert alert-info">
    If you are using the Symfony bundle, GraphiQL is also directly embedded.<br>
    Simply head to <code>http://[path-to-my-app]/graphiql</code>
</div>

Here a query using our simple *Hello World* example:

![](/img/query1.png)

## Query with a type

So far, we simply declared a query. But we did not yet declare a type.

Let's assume you want to return a product:

```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;

class ProductController
{
    /**
     * @Query
     */
    public function product(string $id): Product
    {
        // Some code that looks for a product and returns it.
    }
}
```

As the `Product` class is not a scalar type, you must tell GraphQLite how to handle it:

```php
namespace App\Entities;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @Field()
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }
}
```

The `@Type` annotation is used to inform GraphQLite that the `Product` class is a GraphQL type.

The `@Field` annotation is used to define the GraphQL fields. This annotation must be put on a **public method**.

The `Product` class must be in one of the *types* namespaces. As for *controller* classes, you configured this namespace when you installed 
GraphQLite. By default, in Symfony, the allowed types namespaces are `App\Entity` and `App\Types`.

This query is equivalent to the following [GraphQL type language](https://graphql.org/learn/schema/#type-language):

```graphql
Type Product {
    name: String!
    price: Float
}
```

<div class="alert alert-info">
    <p>If you are used to  <a href="https://en.wikipedia.org/wiki/Domain-driven_design">Domain driven design</a>, you probably
    realize that the <code>Product</code> class is part of your <i>domain</i>.</p>
    <p>GraphQL annotations are adding some serialization logic that is out of scope of the domain.
    These are <i>just</i> annotations and for most project, this is the fastest and easiest route.</p>
    <p>If you feel that GraphQL annotations do not belong to the domain, or if you cannot modify the class
    directly (maybe because it is part of a third party library), there is another way to create types without annotating
    the domain class. We will explore that in the next chapter.</p>
</div>
