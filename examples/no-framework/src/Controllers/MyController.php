<?php
namespace App\Controllers;

use TheCodingMachine\GraphQLite\Annotations\Query;

class MyController
{
    #[Query]
    public function hello(string $name): string
    {
        return 'Hello '.$name;
    }
}

