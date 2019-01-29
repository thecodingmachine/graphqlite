---
id: inheritance
title: Inheritance and interfaces
sidebar_label: Inheritance and interfaces
---

Some of your entities may extend other entities. GraphQLite will do its best to represent this hierarchy of objects in GraphQL using interfaces.

Let's say you have 2 classes: `Contact` and `User` (which extends `Contact`)

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

Both classes are also declared as GraphQL types (using the `@Type` annotation).

Now, let's assume you have a query that returns a contact:

```
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

When writing a GraphQL query, you can query using fragments:

```graphql
contact {
    name
    ... User {
       email
    }
}
``` 

Behind the scene, GraphQLite will detect that the `Contact` class is extended by the `User` class. Because the
class is extended, a GraphQL `ContactInterface` interface is created dynamically. You don't have to do anything.
The GraphQL `User` type will automatically implement this `ContactInterface`. The interface contains all the fields
available in the `Contact` type.

Written in "[GraphQL type language](https://graphql.org/learn/schema/#type-language)", the representation of types
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

<div class="alert alert-warning">Right now, there is no way to explicitly declare a GraphQL interface using GraphQLite.
GraphQLite automatically declares interfaces when it sees an inheritance relationship between to classes that
are GraphQL types.
</div>
