---
id: version-3.0-authentication_authorization
title: Authentication and authorization
sidebar_label: Authentication and authorization
original_id: authentication_authorization
---

You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields
reserved to some users.

GraphQLite offers some control over what a user can do with your API based on authentication (whether the user
is logged or not) or authorization (what rights the user have).

<div class="alert alert-info">
GraphQLite does not have its own security mechanism.
Unless you're using our Symfony Bundle, it is up to you to connect this feature to your framework's security mechanism.<br>
See <a href="#connecting-graphqlite-to-your-framework-s-security-module">Connecting GraphQLite to your framework's security module</a>.
</div>

## `@Logged` and `@Right` annotations

GraphQLite exposes two annotations (`@Logged` and `@Right`) that you can use to restrict access to a resource.

```php
namespace App\Controller;

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
        // ...
    }
}
```

In the example above, the query `users` will only be available if the user making the query is logged AND if he
has the `CAN_VIEW_USER_LIST` right.

`@Logged` and `@Right` annotations can be used next to:

* `@Query` annotations
* `@Mutation` annotations
* `@Field` annotations

<div class="alert alert-info">The query/mutation/field will NOT be part of the GraphQL schema if the current user is not logged or has not the requested right.</div>

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
     * the value returned will be "null".
     *
     * @Query
     * @Logged
     * @Right("CAN_VIEW_USER_LIST")
     * @FailWith(null)
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        // ...
    }
}
```

## Connecting GraphQLite to your framework's security module

<div class="alert alert-info">
    This step is NOT necessary for user using GraphQLite through the Symfony Bundle
</div>

GraphQLite needs to know if a user is logged or not, and what rights it has.
But this is specific of the framework you use.

To plug GraphQLite to your framework's security mechanism, you will have to provide two classes implementing: 

* `TheCodingMachine\GraphQLite\Security\AuthenticationServiceInterface`
* `TheCodingMachine\GraphQLite\Security\AuthorizationServiceInterface`

Those two interfaces act as adapters between GraphQLite and your framework:

```php
interface AuthenticationServiceInterface
{
    /**
     * Returns true if the "current" user is logged.
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
     * Returns true if the "current" user has access to the right "$right".
     *
     * @param string $right
     * @return bool
     */
    public function isAllowed(string $right): bool;
}
```
