---
id: version-3.0-inheritance
title: Inheritance and interfaces
sidebar_label: Inheritance and interfaces
original_id: inheritance
---

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

<div class="alert alert-warning">
Right now, there is no way to explicitly declare a GraphQL interface using GraphQLite.<br>
GraphQLite automatically declares interfaces when it sees an inheritance relationship between to classes that
are GraphQL types.
</div>
