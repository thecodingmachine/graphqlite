---
id: my-first-query
title: Writing your first query
sidebar_label: My first query
---

## Creating a controller

In GraphQL-Controllers, GraphQL queries are creating by writing methods in "controller" classes.
Each query method must be annotated with the `@Query` annotation.

Here is a sample of a "hello world" query:

```php
namespace App\Controllers;

use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class MyController
{
    /**
     * @Query
     */
    public function hello(string $name): string
    {
        return 'Hello '.$name;
    }
}
```

- The `MyController` class does not need to extend any base class. For GraphQL-Controllers, a controller is simply a
  simple class.
- The query method is annotated with a `@Query` annotation
- The `MyController` class must be in the controllers namespace. You configured this namespace when you installed 
GraphqlControllers. By default, in Symfony, the controllers namespace is `App\Controller`.
  
<div class="alert alert-warning"><strong>Heads up!</strong> The <code>MyController</code> class must exist in the container of your 
application and the container identifier MUST be the fully qualified class name.</div> 

<div class="alert alert-info">If you are using the Symfony bundle (or a framework with autowiring like Laravel), this 
is usually not an issue as the container will automatically create the controller entry if you do not explicitly 
declare it.</div>

## Testing the query

By default, the GraphQL endpoint is "/graphql".

The easiest way to test a GraphQL endpoint is to use [GraphiQL](https://github.com/graphql/graphiql) or 
[Altair](https://altair.sirmuel.design/) test clients.

These clients come with Chrome and Firefox plugins.

<div class="alert alert-info"><strong>Symfony users:</strong> If you are using the Symfony bundle, GraphiQL is also directly embedded.
Simply head to <code>http://[path-to-my-app]/graphiql</code></div>

You can now perform a test query and get the answer:

<table style="width:100%; display: table">
<tr>
<td style="width:50%">
<strong>Query</strong>
<pre><code>{
  products {
    name
  }
}</code></pre>
</td>
<td style="width:50%">
<strong>Answer</strong>
<pre><code class="hljs css language-json">{
  "data": {
    "products": [
      {
        "name": "Mouf"
      }
    ]
  }
}</code></pre>
</td>
</tr>
</table>


![](../img/query1.png)

Internally, GraphQL-Controllers created a query and added it to its internal type system.

If you are already used to GraphQL, you could represent this in "[Type language](https://graphql.org/learn/schema/#type-language)"
like this:

```graphql
Type Query {
    hello(name: String!): String!
}
```

But with GraphQL-Controllers you don't need to use the *Type language* at all. The philosophy of GraphQL-Controllers
is that you do PHP code, you put annotations and GraphQL-Controllers is creating the GraphQL types for you. That means
less boilerplate code!

Internally, GraphQL-Controllers will do the mapping between PHP types and GraphQL types.

## Creating your first type

So far, we simply declared a query. But we did not yet declare a type.

Let's assume you want to return a product:

```php
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;
use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;

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

If you try to run a GraphQL query on this code, you immediately will face an error message:

<div class="alert alert-error">
<code>For return type of ProductController::product, cannot map class "Product" to a known GraphQL type. Check your TypeMapper configuration.</code>
</div>

This error tells you that your class `Product` is not a valid GraphQL type. To create a GraphQL type, you must add 
an annotation to this class.

```php
TODO
```

TODO @Type, @Field

<div class="alert alert-info"><strong>Heads up!</strong> If you are used to 
<a href="https://en.wikipedia.org/wiki/Domain-driven_design">Domain driven design</a>, you probably
realize that the <code>Product</code> class is part of your "domain". GraphQL annotations are adding some serialization logic 
that is out of scope of the domain. These are "just" annotations and for most project, this is the fastest and 
easiest route. If you feel that GraphQL annotations do not belong to the domain, or if you cannot modify the class
directly (maybe because it is part of a third party library), there is another way to create types without annotating
the domain class. We will explore that in the next chapter.
</div>







TODO: working with annotations.
Doctrine annotations => do not forget the "use".
Advice: use a plugin for your IDE 
Eclipse: https://marketplace.eclipse.org/content/doctrine-plugin
PHPStorm: https://plugins.jetbrains.com/plugin/7320-php-annotations
