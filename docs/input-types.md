---
id: input-types
title: Input types
sidebar_label: Input types
---

Let's assume you are developing an API that returns a list of cities around a location.

Your GraphQL query might look like this:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
class MyController
{
    /**
     * @return City[]
     */
    #[Query]
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
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->

If you try to run this code, you will get the following error:

```
CannotMapTypeException: cannot map class "Location" to a known GraphQL input type. Check your TypeMapper configuration.
```

You are running into this error because GraphQLite does not know how to handle the `Location` object.

In GraphQL, an object passed in parameter of a query or mutation (or any field) is called an **Input Type**.

There are two ways for declaring that type, in GraphQLite: using **Factory** or annotating the class with `@Input`.

## Factory

A **Factory** is a method that takes in parameter all the fields of the input type and return an object.

Here is an example of factory:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```
class MyFactory
{
    /**
     * The Factory annotation will create automatically a LocationInput input type in GraphQL.    
     */
    #[Factory]
    public function createLocation(float $latitude, float $longitude): Location
    {
        return new Location($latitude, $longitude);
    }
}
```
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->

and now, you can run query like this:

```
query {
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

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```
#[Factory]
public function createLocation(float $latitude, float $longitude): Location
{
    return new Location($latitude, $longitude);
}
```
<!--PHP 7+-->
```
/**
 * @Factory()
 */
public function createLocation(float $latitude, float $longitude): Location
{
    return new Location($latitude, $longitude);
}
```
<!--END_DOCUSAURUS_CODE_TABS-->

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

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[Factory]
#[UseInputType(for: "$id", inputType:"ID!")]
public function getProductById(string $id): Product
{
    return $this->productRepository->findById($id);
}
```
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->

### Declaring several input types for the same PHP class
<small>Available in GraphQLite 4.0+</small>

There are situations where a given PHP class might use one factory or another depending on the context.

This is often the case when your objects map database entities.
In these cases, you can use combine the use of `@UseInputType` and `@Factory` annotation to achieve your goal.

Here is an annotated sample:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
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
     */
    #[Factory(name: "ProductRefInput", default: true)]
    public function getProduct(string $id): Product
    {
        return $this->productRepository->findById($id);
    }
    /**
     * We specify a name for this input type explicitly.
     */
    #[Factory(name: "CreateProductInput", default: false)]
    public function createProduct(string $name, string $type): Product
    {
        return new Product($name, $type);
    }
}

class ProductController
{
    /**
     * The "createProduct" factory will be used for this mutation.
     */
    #[Mutation]
    #[UseInputType(for: "$product", inputType: "CreateProductInput!")]
    public function saveProduct(Product $product): Product
    {
        // ...
    }
    
    /**
     * The default "getProduct" factory will be used for this query.
     *
     * @return Color[]
     */
    #[Query]
    public function availableColors(Product $product): array
    {
        // ...
    }
}
```
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->

### Ignoring some parameters
<small>Available in GraphQLite 4.0+</small>

GraphQLite will automatically map all your parameters to an input type.
But sometimes, you might want to avoid exposing some of those parameters.

Image your `getProductById` has an additional `lazyLoad` parameter. This parameter is interesting when you call
directly the function in PHP because you can have some level of optimisation on your code. But it is not something that 
you want to expose in the GraphQL API. Let's hide it! 

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[Factory]
public function getProductById(
        string $id, 
        #[HideParameter]
        bool $lazyLoad = true
    ): Product
{
    return $this->productRepository->findById($id, $lazyLoad);
}
```
<!--PHP 7+-->
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
<!--END_DOCUSAURUS_CODE_TABS-->

With the `@HideParameter` annotation, you can choose to remove from the GraphQL schema any argument.

To be able to hide an argument, the argument must have a default value.

## @Input Annotation

Let's transform `Location` class into an input type by adding `@Input` annotation to it and `@Field` annotation to corresponding properties:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[Input]
class Location
{

    #[Field]
    private float $latitude;
    
    #[Field]
    private float $longitude;

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
<!--PHP 7+-->
```php
/**
 * @Input
 */
class Location
{
   
    /**
     * @Field
     * @var float 
     */
    private $latitude;
    
    /**
     * @Field 
     * @var float 
     */
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
<!--END_DOCUSAURUS_CODE_TABS-->

Now if you call `getCities()` query you can pass the location input in the same way as with factories.
The `Location` object will be automatically instantiated with provided `latitude` / `longitude` and passed to the controller as a parameter.

There are some important things to notice:

- `@Field` annotation is recognized only on properties for Input Type.
- There are 3 ways for fields to be resolved:
  - Via constructor if corresponding properties are mentioned as parameters with the same names - exactly as in the example above.
  - If properties are public, they will be just set without any additional effort.
  - For private or protected properties implemented public setter is required (if they are not set via constructor). For example `setLatitude(float $latitude)`.

### Multiple input types per one class

Simple usage of `@Input` annotation on a class creates an GraphQl input named by class name + "Input" suffix if a class name does not end with it already.
You can add multiple `@Input` annotations to the same class, give them different names and link different fields.
Consider the following example:

<!--DOCUSAURUS_CODE_TABS-->
<!--PHP 8+-->
```php
#[Input(name: 'CreateUserInput', default: true)]
#[Input(name: 'UpdateUserInput', update: true)]
class UserInput
{

    #[Field]
    public string $username;

    #[Field(for: 'CreateUserInput')]
    public string $email;

    #[Field(for: 'CreateUserInput', inputType: 'String!')]
    #[Field(for: 'UpdateUserInput', inputType: 'String')]
    public string $password;
    
    #[Field]
    public ?int $age;
}
```
<!--PHP 7+-->
```php
/**
 * @Input(name="CreateUserInput", default=true)
 * @Input(name="UpdateUserInput", update=true)
 */
class UserInput
{

    /**
     * @Field()
     * @var string
     */
    public $username;

    /**
     * @Field(for="CreateUserInput")
     * @var string
     */
    public string $email;

    /**
     * @Field(for="CreateUserInput", inputType="String!")
     * @Field(for="UpdateUserInput", inputType="String") 
     * @var string|null
     */
    public $password;
    
    /**
     * @Field() 
     * @var int|null
     */
    public $age;
}
```
<!--END_DOCUSAURUS_CODE_TABS-->

There are 2 input types created for just one class: `CreateUserInput` and `UpdateUserInput`. A few notes:
- `CreateUserInput` input will be used by default for this class.
- Field `username` is created for both input types, and it is required because the property type is not nullable.
- Field `email` will appear only for `CreateUserInput` input.
- Field `password` will appear for both. For `CreateUserInput` it'll be the required field and for `UpdateUserInput` optional.
- Field `age` is optional for both input types.

Note that `update: true` argument for `UpdateUserInput`. It should be used when input type is used for a partial update,
It makes all fields optional and removes all default values from thus prevents setting default values via setters or directly to public properties.
In example above if you use the class as `UpdateUserInput` and set only `username` the other ones will be ignored.
In PHP 7 they will be set to `null`, while in PHP 8 they will be in not initialized state - this can be used as a trick
to check if user actually passed a value for a certain field.
