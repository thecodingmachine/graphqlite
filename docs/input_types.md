---
id: input-types
title: Input types
sidebar_label: Input types
---

Let's admit you are developing an API that returns a list of cities around a location.

Your GraphQL query might look like this:

```php
class MyController
{
    /**
     * @Query
     * @return City[]
     */
    public function getCities(Location $location, float $radius): array
    {
        // Some code that returns an array of cities.
    }
}

// Class Location is a simple value-object.
class Location
{
    private $latitude;
    private $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }
}
```

If you try to run this code, you will get the following error:

```
CannotMapTypeException: cannot map class "Location" to a known GraphQL input type. Check your TypeMapper configuration.
```

You are running into this error because GraphQLite does not know how to handle the `Location` object.

In GraphQL, an object passed in parameter of a query or mutation (or any field) is called an **Input Type**.

In order to declare that type, in GraphQLite, we will declare a **Factory**.

A **Factory** is a method that takes in parameter all the fields of the input type and return an object.

Here is an example of factory:

```
class MyFactory
{
    /**
     * The Factory annotation will create automatically a LocationInput input type in GraphQL.    
     *
     * @Factory()
     */
    public function createLocation(float $latitude, float $longitude): Location
    {
        return new Location($latitude, $longitude);
    }
}
```

and now, you can run query like this:

```
mutation {
  getCities(location: {
              latitude: 45.0,
              longitude: 0.0,
            },
            radius: 42)
  {
    id,
    name
  }
}
```

- Factories must be declared with the **@Factory** annotation.
- The parameters of the factories are the field of the GraphQL input type

A few important things to notice:

- The container MUST contain the factory class. The identifier of the factory MUST be the fully qualified class name of the class that contains the factory.
  This is usually already the case if you are using a container with auto-wiring capabilities
- We recommend that you put the factories in the same directories as the types. 

### Specifying the input type name

The GraphQL input type name is derived from the return type of the factory.

Given the factory below, the return type is "Location", therefore, the GraphQL input type will be named "LocationInput".

```
/**
 * @Factory()
 */
public function createLocation(float $latitude, float $longitude): Location
{
    return new Location($latitude, $longitude);
}
```

In case you want to override the input type name, you can use the "name" attribute of the @Factory annotation:

```
/**
 * @Factory(name="MyNewInputName", default=true)
 */
```

Note that you need to add the "default" attribute is you want your factory to be used by default (more on this in 
the next chapter).

Unless you want to have several factories for the same PHP class, the input type name will be completely transparent 
to you, so there is no real reason to customize it.

### Forcing an input type

You can use the `@UseInputType` annotation to force an input type of a parameter.

Let's say you want to force a parameter to be of type "ID", you can use this:

```php
/**
 * @Factory()
 * @UseInputType(for="$id", inputType="ID!")
 */
public function getProductById(string $id): Product
{
    return $this->productRepository->findById($id);
}
```

### Declaring several input types for the same PHP class
<small>Available in GraphQLite 4.0+</small>

There are situations where a given PHP class might use one factory or another depending on the context.

This is often the case when your objects map database entities.
In these cases, you can use combine the use of `@UseInputType` and `@Factory` annotation to achieve your goal.

Here is an annotated sample:

```php
/**
 * This class contains 2 factories to create Product objects.
 * The "getProduct" method is used by default to map "Product" classes.
 * The "createProduct" method will generate another input type named "CreateProductInput"
 */
class ProductFactory
{
    // ...
    
    /**
     * This factory will be used by default to map "Product" classes.
     * @Factory(name="ProductRefInput", default=true)
     */
    public function getProduct(string $id): Product
    {
        return $this->productRepository->findById($id);
    }
    /**
     * We specify a name for this input type explicitly.
     * @Factory(name="CreateProductInput", default=false)
     */
    public function createProduct(string $name, string $type): Product
    {
        return new Product($name, $type);
    }
}

class ProductController
{
    /**
     * The "createProduct" factory will be used for this mutation.
     * 
     * @Mutation
     * @UseInputType(for="$product", inputType="CreateProductInput!")
     */
    public function saveProduct(Product $product): Product
    {
        // ...
    }
    
    /**
     * The default "getProduct" factory will be used for this query.
     *
     * @Query
     * @return Color[]
     */
    public function availableColors(Product $product): array
    {
        // ...
    }
}
```

### Ignoring some parameters
<small>Available in GraphQLite 4.0+</small>

GraphQLite will automatically map all your parameters to an input type.
But sometimes, you might want to avoid exposing some of those parameters.

Image your `getProductById` has an additional `lazyLoad` parameter. This parameter is interesting when you call
directly the function in PHP because you can have some level of optimisation on your code. But it is not something that 
you want to expose in the GraphQL API. Let's hide it! 

```php
/**
 * @Factory()
 * @HideParameter(for="$lazyLoad")
 */
public function getProductById(string $id, bool $lazyLoad = true): Product
{
    return $this->productRepository->findById($id, $lazyLoad);
}
```

With the `@HideParameter` annotation, you can choose to remove from the GraphQL schema any argument.

To be able to hide an argument, the argument must have a default value.
