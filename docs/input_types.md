# Input types

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

You are running into this error because GraphQL-Controllers does not know how to handle the `Location` object.

In GraphQL, an object passed in parameter of a query or mutation (or any field) is called an **Input Type**.

In order to declare that type, in GraphQL-Controllers, we will declare a **Factory**.

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
    public function createLongitude(float $latitude, float $longitude): Location
    {
        return new Location($latitude, $longitude);
    }
}
```

and now, you can run query like this:

```
TODO
```

- Factories must be declared with the **@Factory** annotation.
- The parameters of the factories are the field of the GraphQL input type

A few important things to notice:

- The container MUST contain the factory class. The identifier of the factory MUST be the fully qualified class name of the class that contains the factory.
  This is usually already the case if you are using a container with auto-wiring capabilities
- We recommend that you put the factories in the same directories as the types. 


TODO: how to specify input type name
- The return value is used for the name of the Input Type
