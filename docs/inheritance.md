---
id: inheritance
title: Inheritance and interfaces
sidebar_label: Inheritance and interfaces
---

## Modeling inheritance

Some of your entities may extend other entities. GraphQLite will do its best to represent this hierarchy of objects in GraphQL using interfaces.

Let's say you have two classes, `Contact` and `User` (which extends `Contact`):

```php
/**
 * @Type
 */
class Contact
{
    // ...
}

/**
 * @Type
 */
class User extends Contact
{
    // ...
}
```

Now, let's assume you have a query that returns a contact:

```php
class ContactController
{
    /**
     * @Query()
     */
    public function getContact(): Contact
    {
        // ...
    }
}
```

When writing your GraphQL query, you are able to use fragments to retrieve fields from the `User` type:

```graphql
contact {
    name
    ... User {
       email
    }
}
``` 

Written in [GraphQL type language](https://graphql.org/learn/schema/#type-language), the representation of types
would look like this:

```graphql
interface ContactInterface {
    // List of fields declared in Contact class
}

type Contact implements ContactInterface {
    // List of fields declared in Contact class
}

type User implements ContactInterface {
    // List of fields declared in Contact and User classes
}
```

Behind the scene, GraphQLite will detect that the `Contact` class is extended by the `User` class. 
Because the class is extended, a GraphQL `ContactInterface` interface is created dynamically.

The GraphQL `User` type will also automatically implement this `ContactInterface`. The interface contains all the fields
available in the `Contact` type.

## Mapping interfaces

If you want to create a pure GraphQL interface, you can also add a `@Type` annotation on a PHP interface.

```php
/**
 * @Type
 */
interface UserInterface
{
    /**
     * @Field
     */
    public function getUserName(): string;
}
```

This will automatically create a GraphQL interface whose description is:

```graphql
interface UserInterface {
    userName: String!
}
```

Note: You don't have to explicitly put a @Type annotation on the class implementing the interface (though this
is usually a good idea).

```php
/**
 * Look, this class has no @Type annotation
 */
class User implements UserInterface
{
    /**
     * ... and getUserName has no @Field annotation
     */
    public function getUserName(): string;
}
```

```php
class UserController
{
    /**
     * @Query()
     */
    public function getUser(): UserInterface // This will work!
    {
        // ...
    }
}
```

<div class="alert alert-info">If GraphQLite cannot find a proper GraphQL Object type implementing an interface, it
will create an object type "on the fly".</div>

In the example above, because the `User` class has no `@Type` annotations, GraphQLite will
create a `UserImpl` type that implements `UserInterface`.

```graphql
interface UserInterface {
    userName: String!
}

type UserImpl implements UserInterface {
    userName: String!
}
```

