---
id: fine-grained-security
title: Fine grained security
sidebar_label: Fine grained security
---

If the [`@Logged` and `@Right` annotations](authentication_authorization.md#logged-and-right-annotations) are not 
granular enough for your needs, you can use the advanced `@Security` annotation.

Using the `@Security` annotation, you can write an *expression* that can contain custom logic. For instance:

- Check that a user can access a given resource
- Check that a user has one right or another right
- ...

## Using the @Security annotation

The `@Security` annotation is very flexible: it allows you to pass an expression that can contains custom logic:

```php
use TheCodingMachine\GraphQLite\Annotations\Security;

// ...

/**
 * @Query
 * @Security("is_granted('ROLE_ADMIN') or is_granted('POST_SHOW', post)")
 */
public function getPost(Post $post): array
{
    // ...
}
```

The *expression* defined in the `@Security` annotation must conform to [Symfony's Expression Language syntax](https://symfony.com/doc/4.4/components/expression_language/syntax.html)

<div class="alert alert-info">
    If you are a Symfony user, you might already be used to the <code>@Security</code> annotation. Most of the inspiration
    of this annotation comes from Symfony. Warning though! GraphQLite's <code>@Security</code> annotation and 
    Symfony's <code>@Security</code> annotation are slightly different. Especially, the two annotations do not live
    in the same namespace!
</div>

## The `is_granted` function

Use the `is_granted` function to check if a user has a special right.

```php
@Security("is_granted('ROLE_ADMIN')")
```

is similar to

```php
@Right("ROLE_ADMIN")
```

In addition, the `is_granted` function accepts a second optional parameter: the "scope" of the right.

```php
/**
 * @Query
 * @Security("is_granted('POST_SHOW', post)")
 */
public function getPost(Post $post): array
{
    // ...
}
```

In the example above, the `getPost` method can be called only if the logged user has the 'POST_SHOW' permission on the
`$post` object. You can notice that the `$post` object comes from the parameters.

## Accessing method parameters

All parameters passed to the method can be accessed in the `@Security` expression.

```php
/**
 * @Query
 * @Security("startDate < endDate", statusCode=400, message="End date must be after start date")
 */
public function getPosts(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
{
    // ...
}
```

In the example above, we tweak a bit the Security annotation purpose to do simple input validation.

## Setting HTTP code and error message

You can use the `statusCode` and `message` attributes to set the HTTP code and GraphQL error message.

```php
/**
 * @Query
 * @Security("is_granted('POST_SHOW', post)", statusCode=404, message="Post not found (let's pretend the post does not exists!)")
 */
public function getPost(Post $post): array
{
    // ...
}
```

Note: since a single GraphQL call contain many errors, 2 errors might have conflicting HTTP status code.
The resulting status code is up to the GraphQL middleware you use. Most of the time, the status code with the 
higher error code will be returned.

## Setting a default value

If you do not want an error to be thrown when the security condition is not met, you can use the `failWith` attribute
to set a default value.

```php
/**
 * @Field
 * @Security("is_granted('CAN_SEE_MARGIN', this)", failWith=null)
 */
public function getMargin(): float
{
    // ...
}
```

The `failWith` attribute behaves just like the [`@FailWith` annotation](authentication_authorization.md#not-throwing-errors)
but for a given `@Security` annotation.

You cannot use the `failWith` attribute along `statusCode` or `message` attributes.

## Accessing the user

You can use the `user` variable to access the currently logged user.
You can use the `is_logged()` function to check if a user is logged or not.

```php
/**
 * @Query
 * @Security("is_logged() && user.age > 18")
 */
public function getNSFWImages(): array
{
    // ...
}
```

## Accessing the current object

You can use the `this` variable to access any (public) property / method of the current class.

```php
class Post {
    /**
     * @Query
     * @Security("this.canAccess(post, user)")
     */
    public function getPost(Post $post): array
    {
        // ...
    }
   
    public function canAccess(Post $post, User $user): bool
    {
        // Some custom logic here
    }
}
```

## Available scope

The `@Security` annotation can be used in any query, mutation or field, so anywhere you have a `@Query`, `@Mutation`
or `@Field` annotation.

## How to restrict access to a given resource

The `is_granted` method can be used to restrict access to a specific resource.

```php
@Security("is_granted('POST_SHOW', post)")
```

If you are wondering how to configure these fine-grained permissions, this is not something that GraphQLite handles 
itself. Instead, this depends on the framework you are using.

If you are using Symfony, you will [create a custom voter](https://symfony.com/doc/current/security/voters.html).

If you are using Laravel, you will [create a Gate or a Policy](https://laravel.com/docs/6.x/authorization).

If you are using another framework, you need to know that the `is_granted` function simply forwards the call to 
the `isAllowed` method of the configured `AuthorizationSerice`. See [Connecting GraphQLite to your framework's security module
](implementing-security.md) for more details
