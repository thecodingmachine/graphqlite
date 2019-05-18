---
id: authentication_authorization
title: Authentication and authorization
sidebar_label: Authentication and authorization
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

<div class="alert alert-info">By default, if a user tries to access an unauthorized query/mutation/field, an error is raised and the query fails.</div>

## Not throwing errors

If you do not want an error to be thrown when a user attempts to query a field/query/mutation he has no access to, you can use the `@FailWith` annotation.

The `@FailWith` annotation contains the value that will be returned for users with insufficient rights.

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

## Hiding fields / queries / mutations

By default, a user analysing the GraphQL schema can see all queries/mutations/types available.
Some will be available to him and some won't.

If you want to add an extra level of security (or if you want your schema to be kept secret to unauthorized users),
you can use the `@HideIfUnauthorized` annotation.

```php
class UserController
{
    /**
     * If a user is not logged or if the user has not the right "CAN_VIEW_USER_LIST",
     * the schema will NOT contain the "users" query at all (so trying to call the
     * "users" query will result in a GraphQL "query not found" error.
     *
     * @Query
     * @Logged
     * @Right("CAN_VIEW_USER_LIST")
     * @HideIfUnauthorized()
     * @return User[]
     */
    public function users(int $limit, int $offset): array
    {
        // ...
    }
}
```

While this is the most secured mode, it can have drawbacks when working with development tools
(you need to be logged as admin to fetch the complete schema).

<div class="alert alert-info">The "HideIfUnauthorized" mode was the default mode in GraphQLite 3 and is optionnal from GraphQLite 4+.</div>

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
