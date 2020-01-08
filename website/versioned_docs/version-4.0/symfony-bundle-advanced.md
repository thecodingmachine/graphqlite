---
id: version-4.0-symfony-bundle-advanced
title: Symfony bundle: advanced usage
sidebar_label: Symfony specific features
original_id: symfony-bundle-advanced
---

The Symfony bundle comes with a number of features to ease the integration of GraphQLite in Symfony.

## Login and logout

Out of the box, the GraphQLite bundle will expose a "login" and a "logout" mutation as well
as a "me" query (that returns the current user).

If you need to customize this behaviour, you can edit the "graphqlite.security" configuration key.

```yaml
graphqlite:
  security:
    enable_login: auto # Default setting
    enable_me: auto # Default setting
```

By default, GraphQLite will enable "login" and "logout" mutations and the "me" query if the following conditions are met:

- the "security" bundle is installed and configured (with a security provider and encoder)
- the "session" support is enabled (via the "framework.session.enabled" key).

```yaml
graphqlite:
  security:
    enable_login: on
```

By settings `enable_login=on`, you are stating that you explicitly want the login/logout mutations.
If one of the dependencies is missing, an exception is thrown (unlike in default mode where the mutations
are silently discarded).

```yaml
graphqlite:
  security:
    enable_login: off
```

Use the `enable_login=off` to disable the mutations.

```yaml
graphqlite:
  security:
    firewall_name: main # default value
```

By default, GraphQLite assumes that your firewall name is "main". This is the default value used in the
Symfony security bundle so it is likely the value you are using. If for some reason you want to use
another firewall, configure the name with `graphqlite.security.firewall_name`.

### Login using the "login" mutation

The mutation below will log-in a user:

```graphql
mutation login {
  login(userName:"foo", password:"bar") {
    userName
    roles
  }
}
```

### Get the current user with the "me" query

Retrieving the current user is easy with the "me" query:

```graphql
{
  me {
    userName
    roles
  }
}
```

In Symfony, user objects implement `Symfony\Component\Security\Core\User\UserInterface`.
This interface is automatically mapped to a type with 2 fields:

- `userName: String!`
- `roles: [String!]!`

If you want to get more fields, just add the `@Type` annotation to your user class:

```php
/**
 * @Type
 */
class User implements UserInterface
{
    /**
     * @Field
     */
    public function getEmail() : string
    {
        // ...
    }

}
```

You can now query this field using an [inline fragment](https://graphql.org/learn/queries/#inline-fragments):

```graphql
{
  me {
    userName
    roles
    ... on User {
      email
    }
  }
}
```

### Logout using the "logout" mutation

Use the "logout" mutation to log a user out

```graphql
mutation logout {
  logout
}
```

## Injecting the Request

You can inject the Symfony Request object in any query/mutation/field.

Most of the time, getting the request object is irrelevant. Indeed, it is GraphQLite's job to parse this request and
manage it for you. Sometimes yet, fetching the request can be needed. In those cases, simply type-hint on the request
in any parameter of your query/mutation/field.

```php
use Symfony\Component\HttpFoundation\Request;

/**
 * @Query
 */
public function getUser(int $id, Request $request): User
{
    // The $request object contains the Symfony Request.
}
```
