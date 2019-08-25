---
id: autowiring
title: Autowiring services
sidebar_label: Autowiring services
---

GraphQLite can automatically inject services in your fields/queries/mutations signatures.

Some of your fields may be computed. In order to compute these fields, you might need to call a service.

Most of the time, your `@Type` annotation will be put on a model. And models do not have access to services.
Hopefully, if you add a type-hinted service in your field's declaration, GraphQLite will automatically fill it with
the service instance.

## Sample

Let's assume you are running an international store. You have a `Product` class. Each product has many names (depending
on the language of the user).

```php
namespace App\Entities;

use TheCodingMachine\GraphQLite\Annotations\Autowire;
use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     * @Autowire(for="$translator")
     */
    public function getName(TranslatorInterface $translator): string
    {
        return $translator->trans('product_name_'.$this->id);
    }
}
```

When GraphQLite queries the name, it will automatically fetch the translator service.

<div class="alert alert-warning">As with most autowiring solutions, GraphQLite assumes that the service identifier
in the container is the fully qualified class name of the type-hint. So in the example above, GraphQLite will 
look for a service whose name is <code>Symfony\Component\Translation\TranslatorInterface</code>.</div>

## Best practices

It is a good idea to refrain from type-hinting on concrete implementations.
Most often, your field declaration will be in your model. If you add a type-hint on a service, you are binding your domain
with a particular service implementation. This makes your code tightly coupled and less testable.

<div class="alert alert-error">
Please don't do that:

<pre><code>    /**
     * @Field()
     */
    public function getName(MyTranslator $translator): string
    {
        // Your domain is suddenly tightly coupled to the MyTranslator class.
    }
</code></pre>
</div>

Instead, be sure to type-hint against an interface.

<div class="alert alert-success">
Do this instead:

<pre><code>    /**
     * @Field()
     */
    public function getName(TranslatorInterface $translator): string
    {
        // Good. You can switch translator implementation any time.
    }
</code></pre>
</div>

By type-hinting against an interface, your code remains testable and is decoupled from the service implementation.

## Fetching a service by name (discouraged!)

Optionally, you can specify the identifier of the service you want to fetch from the controller:

```php
/**
 * @Autowire(for="$translator", identifier="translator")
 */
```

<div class="alert alert-error">While GraphQLite offers the possibility to specify the name of the service to be
autowired, we would like to emphasize that this is <strong>highly discouraged</strong>. Hard-coding a container
identifier in the code of your class is akin to using the "service locator" pattern, which is known to be an
anti-pattern. Please refrain from doing this as much as possible.</div>

## Alternative solution

You may find yourself uncomfortable with the autowiring mechanism of GraphQLite. For instance maybe:

- Your service identifier in the container is not the fully qualified class name of the service (this is often true if you are not using a container supporting autowiring)
- You do not want to inject a service in a domain object
- You simply do not like the magic of injecting services in a method signature

If you do not want to use autowiring and if you still need to access services to compute a field, please read on 
the next chapter to learn [how to extend a type](extend_type).
