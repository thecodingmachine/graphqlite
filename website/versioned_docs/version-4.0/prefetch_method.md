---
id: version-4.0-prefetch-method
title: Prefetching records
sidebar_label: Prefetching records
original_id: prefetch-method
---

## The problem

GraphQL naive implementations often suffer from the "N+1" problem.

Consider a request where a user attached to a post must be returned:

```graphql
{
    posts {
        id
        user {
            id
        }
    }
}
```

A naive implementation will do this:

- 1 query to fetch the list of posts
- 1 query per post to fetch the user

Assuming we have "N" posts, we will make "N+1" queries.

There are several ways to fix this problem. 
Assuming you are using a relational database, one solution is to try to look 
ahead and perform only one query with a JOIN between "posts" and "users".
This method is described in the ["analyzing the query plan" documentation](query_plan.md).

But this can be difficult to implement. This is also only useful for relational databases. If your data comes from a 
NoSQL database or from the cache, this will not help.

Instead, GraphQLite offers an easier to implement solution: the ability to fetch all fields from a given type at once.

## The "prefetch" method

```php
/**
 * @Type
 */
class PostType {
    /**
     * @Field(prefetchMethod="prefetchUsers")
     * @param Post $post
     * @param mixed $prefetchedUsers
     * @return User
     */
    public function getUser(Post $post, $prefetchedUsers): User
    {
        // This method will receive the $prefetchedUsers as second argument. This is the return value of the "prefetchUsers" method below.
        // Using this prefetched list, it should be easy to map it to the post
    }

    /**
     * @param Post[] $posts
     * @return mixed
     */
    public function prefetchUsers(iterable $posts)
    {
        // This function is called only once per GraphQL request
        // with the list of posts. You can fetch the list of users
        // associated with this posts in a single request,
        // for instance using a "IN" query in SQL or a multi-fetch
        // in your cache back-end.
    }
}
```

When the "prefetchMethod" attribute is detected in the "@Field" annotation, the method is called automatically.
The first argument of the method is an array of instances of the main type.
The "prefetchMethod" can return absolutely anything (mixed). The return value will be passed as the second parameter of the "@Field" annotated method.

## Input arguments

Field arguments can be set either on the @Field annotated method OR/AND on the prefetchMethod.

For instance:

```php
/**
 * @Type
 */
class PostType {
    /**
     * @Field(prefetchMethod="prefetchComments")
     * @param Post $post
     * @param mixed $prefetchedComments
     * @return Comment[]
     */
    public function getComments(Post $post, $prefetchedComments): array
    {
        // ...
    }

    /**
     * @param Post[] $posts
     * @return mixed
     */
    public function prefetchComments(iterable $posts, bool $hideSpam, int $filterByScore)
    {
        // Parameters passed after the first parameter (hideSpam, filterByScore...) are automatically exposed 
        // as GraphQL arguments for the "comments" field.
    }
}
```

The prefetch method MUST be in the same class as the @Field-annotated method and MUST be public.
