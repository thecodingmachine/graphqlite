---
id: laravel-package-advanced
title: Laravel package: advanced usage
sidebar_label: Laravel specific features
---

The Laravel package comes with a number of features to ease the integration of GraphQLite in Laravel.

## Support for Laravel validation rules

The GraphQLite Laravel package comes with a special `@Validate` annotation to use Laravel validation rules in your 
input types.

```php
use TheCodingMachine\GraphQLite\Laravel\Annotations\Validate;

class MyController
{
    /**
     * @Mutation
     * @Validate(for="$email", rule="email|unique:users")
     * @Validate(for="$password", rule="gte:8")
     */
    public function createUser(string $email, string $password): User
    {
        // ...
    }
}
```

You can use the `@Validate` annotation in any query / mutation / field / factory / decorator.

If a validation fails to pass, the message will be printed in the "errors" section and you will get a HTTP 400 status code:

```json
{
    "errors": [
        {
            "message": "The email must be a valid email address.",
            "extensions": {
                "argument": "email",
                "category": "Validate"
            }
        },
        {
            "message": "The password must be greater than or equal 8 characters.",
            "extensions": {
                "argument": "password",
                "category": "Validate"
            }
        }
    ]
}
```

You can use any validation rule described in [the Laravel documentation](https://laravel.com/docs/6.x/validation#available-validation-rules)

## Support for pagination

In your query, if you explicitly return an object that extends the `Illuminate\Pagination\LengthAwarePaginator` class,
the query result will be wrapped in a "paginator" type.

```php
class MyController
{
    /**
     * @Query
     * @return Product[]
     */
    public function products(): Illuminate\Pagination\LengthAwarePaginator
    {
        return Product::paginate(15);
    }
}
```

Notice that:

- the method return type MUST BE `Illuminate\Pagination\LengthAwarePaginator` or a class extending `Illuminate\Pagination\LengthAwarePaginator`
- you MUST add a `@return` statement to help GraphQLite find the type of the list

Once this is done, you can get plenty of useful information about this page:

```
products {
    items {      # The items for the selected page
        id
        name
    }
    totalCount   # The total count of items.
    lastPage     # Get the page number of the last available page.
    firstItem    # Get the "index" of the first item being paginated.
    lastItem     # Get the "index" of the last item being paginated.
    hasMorePages # Determine if there are more items in the data source.
    perPage      # Get the number of items shown per page.
    hasPages     # Determine if there are enough items to split into multiple pages.
    currentPage  # Determine the current page being paginated.
    isEmpty      # Determine if the list of items is empty or not.
    isNotEmpty   # Determine if the list of items is not empty.
}
```


<div class="alert alert-warning">Be sure to type hint on the class (<code>Illuminate\Pagination\LengthAwarePaginator</code>)
and not on the interface (<code>Illuminate\Contracts\Pagination\LengthAwarePaginator</code>). The interface
itself is not iterable (it does not extend <code>Traversable</code>) and therefore, GraphQLite will refuse to
iterate over it.</div>

### Simple paginator

Note: if you are using `simplePaginate` instead of `paginate`, you can type hint on the `Illuminate\Pagination\Paginator` class.

```php
class MyController
{
    /**
     * @Query
     * @return Product[]
     */
    public function products(): Illuminate\Pagination\Paginator
    {
        return Product::simplePaginate(15);
    }
}
```

The behaviour will be exactly the same except you will be missing the `totalCount` and `lastPage` fields.

## Using GraphQLite with Eloquent efficiently

In GraphQLite, you are supposed to put a `@Field` annotation on each getter.

Eloquent uses PHP magic properties to expose your database records.
Because Eloquent relies on magic properties, it is quite rare for an Eloquent model to have proper getters and setters.

So we need to find a workaround. GraphQLite comes with a `@MagicField` annotation to help you
working with magic properties.

```php
/**
 * @Type()
 * @MagicField(name="id" outputType="ID!")
 * @MagicField(name="name" phpType="string")
 * @MagicField(name="categories" phpType="Category[]")
 */
class Product extends Model
{
}
```

Please note that since the properties are "magic", they don't have a type. Therefore,
you need to pass either the "outputType" attribute with the GraphQL type matching the property,
or the "phpType" attribute with the PHP type matching the property.

### Pitfalls to avoid with Eloquent

When designing relationships in Eloquent, you write a method to expose that relationship this way:

```php
class User extends Model
{
    /**
     * Get the phone record associated with the user.
     */
    public function phone()
    {
        return $this->hasOne('App\Phone');
    }
}
```

It would be tempting to put a `@Field` annotation on the `phone()` method, but this will not work. Indeed,
the `phone()` method does not return a `App\Phone` object. It is the `phone` magic property that returns it.

In short:

<div class="alert alert-error">This does not work:
<pre><code class="hljs css language-php">
class User extends Model
{
    /**
     * @Field
     */
    public function phone()
    {
        return $this->hasOne('App\Phone');
    }
}</code></pre>
</div>

<div class="alert alert-success">This works:
<pre><code class="hljs css language-php">
/**
 * @MagicField(name="phone", phpType="App\\Phone")
 */
class User extends Model
{
    public function phone()
    {
        return $this->hasOne('App\Phone');
    }
}</code></pre>
</div>
