<?php
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use TheCodingMachine\GraphQLite\SchemaFactory;
use TheCodingMachine\GraphQLite\Context\Context;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Mouf\Picotainer\Picotainer;
use GraphQL\Utils\SchemaPrinter;
use App\Controllers\MyController;

require_once __DIR__ . '/vendor/autoload.php';

// $cache is any PSR-16 compatible cache.
$cache = new Psr16Cache(new FilesystemAdapter());;

// $container is any PSR-11 compatible container which has
// been populated with your controller classes.
$container = new Picotainer([
        MyController::class => function() {
                return new MyController();
        },
]);

$factory = new SchemaFactory($cache, $container);
$factory->addControllerNamespace('App\\Controllers')
        ->addTypeNamespace('App');

$schema = $factory->createSchema();

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);
$query = $input['query'];
$variableValues = isset($input['variables']) ? $input['variables'] : null;

$result = GraphQL::executeQuery($schema, $query, null, new Context(), $variableValues);
$output = $result->toArray();

header('Content-Type: application/json');
echo json_encode($output) . "\n";

