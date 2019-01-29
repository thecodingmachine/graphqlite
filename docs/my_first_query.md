---
id: my-first-query
title: Writing your first query
sidebar_label: My first query
---

## Creating a controller

In GraphQLite, GraphQL queries are creating by writing methods in "controller" classes.
Each query method must be annotated with the `@Query` annotation.

Here is a sample of a "hello world" query:

```php
namespace App\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;

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

- The `MyController` class does not need to extend any base class. For GraphQLite, a controller can be any
  class.
- The query method is annotated with a `@Query` annotation
- The `MyController` class must be in the controllers namespace. You configured this namespace when you installed 
GraphQLite. By default, in Symfony, the controllers namespace is `App\Controller`.
  
<div class="alert alert-warning"><strong>Heads up!</strong> The <code>MyController</code> class must exist in the container of your 
application and the container identifier MUST be the fully qualified class name.<br/><br/>
If you are using the Symfony bundle (or a framework with autowiring like Laravel), this 
is usually not an issue as the container will automatically create the controller entry if you do not explicitly 
declare it.</div> 

## Testing the query

By default, the GraphQL endpoint is "/graphql". You can send HTTP requests to this endpoint and get responses.

The easiest way to test a GraphQL endpoint is to use [GraphiQL](https://github.com/graphql/graphiql) or 
[Altair](https://altair.sirmuel.design/) test clients.

These clients are available as Chrome or Firefox plugins.

<div class="alert alert-info"><strong>Symfony users:</strong> If you are using the Symfony bundle, GraphiQL is also directly embedded.
Simply head to <code>http://[path-to-my-app]/graphiql</code></div>

You can now perform a test query and get the answer:

<table style="width:100%; display: table">
<tr>
<td style="width:50%">
<strong>Query</strong>
<pre><code>{
  hello(name: "David")
}</code></pre>
</td>
<td style="width:50%">
<strong>Answer</strong>
<pre><code class="hljs css language-json">{
  "data": {
    "hello": "Hello David"
}</code></pre>
</td>
</tr>
</table>


![](../img/query1.png)

Internally, GraphQLite created a query and added it to its internal type system.

If you are already used to GraphQL, you could represent this in "[Type language](https://graphql.org/learn/schema/#type-language)"
like this:

```graphql
Type Query {
    hello(name: String!): String!
}
```

But with GraphQLite you don't need to use the *Type language* at all. The philosophy of GraphQLite
is that you do PHP code, you put annotations and GraphQLite is creating the GraphQL types for you. That means
less boilerplate code!

Internally, GraphQLite will do the mapping between PHP types and GraphQL types.

## Creating your first type

So far, we simply declared a query. But we did not yet declare a type.

Let's assume you want to return a product:

```php
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

If you try to run a GraphQL query on this code, you immediately will face an error message:

<div class="alert alert-error">
<code>For return type of ProductController::product, cannot map class "Product" to a known GraphQL type. Check your TypeMapper configuration.</code>
</div>

This error tells you that your class `Product` is not a valid GraphQL type. To create a GraphQL type, you must add 
an annotation to this class.

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
The `@Field` annotation is used to define the GraphQL fields.

The `Product` class must be in the types namespace. You configured this namespace when you installed 
GraphQLite. By default, in Symfony, the types namespace is any namespace starting with `App\` so you can
put a type anywhere in your application code.

<div class="alert alert-info"><strong>Heads up!</strong> The <code>@Field</code> annotation must be put on a 
<strong>public method</strong>.
You cannot annotate a property (unlike Doctrine ORM where you annotate only properties).
</div>


If you are already used to GraphQL, the "[Type language](https://graphql.org/learn/schema/#type-language)"
representation for this type is:

```graphql
Type Product {
    name: String!
    price: Float
}
```

We are now ready to run our test query:

<table style="width:100%; display: table">
<tr>
<td style="width:50%">
<strong>Query</strong>
<pre><code>{
  product(id: 42) {
    name
  }
}</code></pre>
</td>
<td style="width:50%">
<strong>Answer</strong>
<pre><code class="hljs css language-json">{
  "data": {
    "product": {
        "name": "Mouf"
    }
  }
}</code></pre>
</td>
</tr>
</table>


<div class="alert alert-info"><strong>Heads up!</strong> If you are used to 
<a href="https://en.wikipedia.org/wiki/Domain-driven_design">Domain driven design</a>, you probably
realize that the <code>Product</code> class is part of your "domain". GraphQL annotations are adding some serialization logic 
that is out of scope of the domain. These are "just" annotations and for most project, this is the fastest and 
easiest route. If you feel that GraphQL annotations do not belong to the domain, or if you cannot modify the class
directly (maybe because it is part of a third party library), there is another way to create types without annotating
the domain class. We will explore that in the next chapter.
</div>

## Working with annotations

If you have never worked with annotations before, here are a few things you should know:

- PHP has no native support for annotations, so annotations are added via a third-party library called Doctrine Annotations.
- Annotations must be declared in PHP Docblocks. A Docblock lives at the top of a class/method and must start with "/**"
- Annotations are namespaced. You must not forget the "use" statement at the beginning of each file using an annotation.
  For instance:
  ```php
  use TheCodingMachine\GraphQLite\Annotations\Query;
  ```
- Doctrine Annotations are hugely popular and used in many other libraries. They are widely supported in PHP IDEs.
  We highly recommend you add support for Doctrine annotations in your favorite IDE:
   - use [*"PHP Annotations"* if you use PHPStorm](https://plugins.jetbrains.com/plugin/7320-php-annotations)
   - use [*"Doctrine plugin"* if you use Eclipse](https://marketplace.eclipse.org/content/doctrine-plugin)
   - Netbeans has native support
   - ...
    
