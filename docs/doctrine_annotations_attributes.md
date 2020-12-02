---
id: doctrine-annotations-attributes
title: Doctrine annotations VS PHP8 attributes
sidebar_label: Annotations VS Attributes
---

GraphQLite is heavily relying on the concept of annotations (also called attributes in PHP 8+).

## Doctrine annotations

<div class="alert alert-warning"><strong>Deprecated!</strong> Doctrine annotations are deprecated in favor of native PHP 8 attributes. Support will be dropped in GraphQLite 5.0</div>

Historically, attributes were not available in PHP and PHP developers had to "trick" PHP to get annotation support.
This was the purpose of the [doctrine/annotation](https://www.doctrine-project.org/projects/doctrine-annotations/en/latest/index.html) library.

Using Doctrine annotations, you write annotations in your docblocks:

```php
use TheCodingMachine\GraphQLite\Annotations\Type;

/**
 * @Type
 */
class MyType
{
}
```

Please note that:

- The annotation is added in a **docblock** (a comment starting with "`/**`")
- The `Type` part is actually a class. It must be declared in the `use` statements at the top of your file.


<div class="alert alert-info"><strong>Heads up!</strong>
Some IDEs provide support for Doctrine annotations:

<ul>
<li>PhpStorm via the <a href="PHP Annotations Plugin">https://plugins.jetbrains.com/plugin/7320-php-annotations</a></li>
<li>Eclipse via the <a href="Symfony2 Plugin">https://marketplace.eclipse.org/content/symfony-plugin</a></li>
<li>Netbeans has native support</li>
</ul>

We strongly recommend using an IDE that has Doctrine annotations support.
</div>

## PHP 8 attributes

Starting with PHP 8, PHP got native annotations support. They are actually called "attributes" in the PHP world.

The same code can be written this way:

```php
use TheCodingMachine\GraphQLite\Annotations\Type;

#[Type]
class MyType
{
}
```

GraphQLite v4.1+ has support for PHP 8 attributes.

The Doctrine annotation class and the PHP 8 attribute class is **the same** (so you will be using the same `use` statement at the top of your file).

They support the same attributes too.

A few notable differences:

- PHP 8 attributes do not support nested attributes (unlike Doctrine annotations). This means there is no equivalent to the `annotations` attribute of `@MagicField` and `@SourceField`.
- PHP 8 attributes can be written at the parameter level. Any attribute targeting a "parameter" must be written at the parameter level.

Let's take an example with the [`#Autowire` attribute](autowiring.md):

**PHP 7+**
```
/**
 * @Field
 * @Autowire(for="$productRepository")
 */
public function getProduct(ProductRepository $productRepository) : Product {
    //...
} 
``` 

**PHP 8**
```
#[Field]
public function getProduct(#[Autowire] ProductRepository $productRepository) : Product {
    //...
} 
``` 

