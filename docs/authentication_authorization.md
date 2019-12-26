---
id: authentication_authorization
title: Authentication and authorization
sidebar_label: Authentication and authorization
---

You might not want to expose your GraphQL API to anyone. Or you might want to keep some queries/mutations or fields
reserved to some users.

GraphQLite offers some control over what a user can do with your API. You can restrict access to resources:
 
- based on authentication using the [`@Logged` annotation](#logged-and-right-annotations) (restrict access to logged users)
- based on authorization using the [`@Right` annotation](#logged-and-right-annotations) (restrict access to logged users with certain rights).
- based on fine-grained authorization using the [`@Security` annotation](fine-grained-security.md) (restrict access for some given resources to some users).

<div class="alert alert-info">
GraphQLite does not have its own security mechanism.
Unless you're using our Symfony Bundle or our Laravel package, it is up to you to connect this feature to your framework's security mechanism.<br>
See <a href="implementing-security.md">Connecting GraphQLite to your framework's security module</a>.
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

## Injecting the current user as a parameter

Use the `@InjectUser` annotation to get an instance of the current user logged in.

```php
namespace App\Controller;

use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\InjectUser;

class ProductController
{
    /**
     * @Query
     * @InjectUser(for="$user") 
     * @return Product
     */
    public function product(int $id, User $user): Product
    {
        // ...
    }
}
```

The `@InjectUser` annotation can be used next to:

* `@Query` annotations
* `@Mutation` annotations
* `@Field` annotations

The object injected as the current user depends on your framework. It is in fact the object returned by the 
["authentication service" configured in GraphQLite](implementing-security.md).

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
