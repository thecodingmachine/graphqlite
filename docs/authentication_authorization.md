---
id: authentication_authorization
title: Authentication and authorization
sidebar_label: Authentication and authorization
---

You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries / mutations or fields
reserved to some users.

GraphQLite offers some control over what a user can do with your API based on authentication (whether the user
is logged or not) or authorization (what rights the user have).

<div class="alert alert-info"><strong>Heads up!</strong> GraphQLite does not handle authentication or 
authorization. It is up to you (or your framework) to handle that. [GraphQLite plugs into your framework
to fetch whether you are authenticated or not and the list of your rights.](#connectframework)
</div>

## The @Logged and @Right annotations

GraphQLite exposes 2 annotations (`@Logged` and `@Right`) that you can use to restrict access to a resource.

```php
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Logged;
use TheCodingMachine\GraphQLite\Annotations\Right;

class UserController
{
    /**
     * @Query
     * @Logged
     * @Right("CAN_VIEW_USER_LIST")
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        //
    }
}
```

In the example above, the query "users" will only be available if the user making the query is logged AND if we
has the "CAN_VIEW_USER_LIST" right.

<div class="alert alert-warning">The field will NOT be part of the GraphQL schema if the current user is not logged or has not the requested right.</div>

This is a good thing as unprivileged users will *not even be aware of the existence* of the fields they are not supposed to see.

## Constant schemas

By default, the schema will vary based on who is connected. This can be a problem with some GraphQL clients as the schema 
is changing from one user to another.

If you want to keep a constant schema, you can use the `@FailWith` annotation that contains the value that
will be returned for user with insufficient rights.

```php
class UserController
{
    /**
     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",
     * the value returned will be "null"
     *
     * @Query
     * @Logged
     * @Right("CAN_VIEW_USER_LIST")
     * @FailWith(null)
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        //
    }
}
```

In the example above, if the user is not logged, or if he does not have the "CAN_VIEW_USER_LIST" right, the user
will still be able to see the "users" query, but any call to this query will return `null`.

## Allowed scopes for @Logged and @Right annotations

`@Logged` and `@Right` annotations can be used next to:

- `@Query` annotations
- `@Mutation` annotations
- `@Field` annotations

You can therefore decide in a GraphQL type who can see what:

```php
namespace App\Entities;

use TheCodingMachine\GraphQLite\Annotations\Field;
use TheCodingMachine\GraphQLite\Annotations\Type;
use TheCodingMachine\GraphQLite\Annotations\Right;

/**
 * @Type()
 */
class Product
{
    // ...

    /**
     * @Field()
     * @Right("CAN_SEE_STOCK")
     */
    public function getStock(): int
    {
        return $this->stock;
    }
}
```

<a name="connectframework"></a>
## Connecting GraphQLite to your framework's security module

GraphQLite needs to know if a user is logged or not, and what rights it has.
But this is specific of the framework you use.

To plug GraphQLite to your framework's security, you will have to provide 2 classes implementing the 
`TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface`
and the `TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface`.

<div class="alert alert-info"><strong>Symfony users:</strong> The GraphQLite bundle comes with the classes linking 
GraphQLite to the security bundle. So you don't have to do anything.
</div>

These 2 interfaces act as adapters between GraphQLite and your framework:

```php
interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged
     *
     * @return bool
     */
    public function isLogged(): bool;
}
``` 

```php
interface AuthorizationServiceInterface
{
    /**
     * Returns true if the "current" user has access to the right "$right"
     *
     * @param string $right
     * @return bool
     */
    public function isAllowed(string $right): bool;
}
```
