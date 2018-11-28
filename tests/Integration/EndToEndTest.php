<?php

namespace TheCodingMachine\GraphQL\Controllers\Integration;

use function class_exists;
use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use Exception;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\InputType;
use Mouf\Picotainer\Picotainer;
use PhpParser\Comment\Doc;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Type;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Cache\Simple\NullCache;
use TheCodingMachine\GraphQL\Controllers\AnnotationReader;
use TheCodingMachine\GraphQL\Controllers\Fixtures\Integration\Models\Contact;
use TheCodingMachine\GraphQL\Controllers\GlobControllerQueryProvider;
use TheCodingMachine\GraphQL\Controllers\HydratorInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\GlobTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapper;
use TheCodingMachine\GraphQL\Controllers\Mappers\RecursiveTypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\Mappers\TypeMapperInterface;
use TheCodingMachine\GraphQL\Controllers\QueryProviderInterface;
use TheCodingMachine\GraphQL\Controllers\Registry\Registry;
use TheCodingMachine\GraphQL\Controllers\Registry\RegistryInterface;
use TheCodingMachine\GraphQL\Controllers\Schema;
use TheCodingMachine\GraphQL\Controllers\Security\AuthenticationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\AuthorizationServiceInterface;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthenticationService;
use TheCodingMachine\GraphQL\Controllers\Security\VoidAuthorizationService;
use TheCodingMachine\GraphQL\Controllers\TypeGenerator;

class EndToEndTest /*extends TestCase*/
{
    /**
     * @var ContainerInterface
     */
    private $mainContainer;

    public function setUp()
    {
        $this->mainContainer = new Picotainer([
            Schema::class => function(ContainerInterface $container) {
                return new Schema($container->get(QueryProviderInterface::class), $container->get(RegistryInterface::class));
            },
            QueryProviderInterface::class => function(ContainerInterface $container) {
                return new GlobControllerQueryProvider('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Integration\\Controllers', $container->get(RegistryInterface::class),
                    $container->get('autowiringContainer'), new NullCache());
            },
            RegistryInterface::class => function(ContainerInterface $container) {
                return new Registry($container->get('autowiringContainer'),
                    $container->get(AuthorizationServiceInterface::class),
                    $container->get(AuthenticationServiceInterface::class),
                    new DoctrineAnnotationReader(),
                    $container->get(RecursiveTypeMapperInterface::class),
                    $container->get(HydratorInterface::class)
                    );
            },
            'autowiringContainer' => function(ContainerInterface $container) {
                return new class implements ContainerInterface {
                    public function get($id)
                    {
                        if (!$this->has($id)) {
                            throw new class('Class not found') extends Exception implements NotFoundExceptionInterface {
                            };
                        }
                        return new $id();
                    }

                    public function has($id)
                    {
                        return class_exists($id);
                    }
                };
            },
            AuthorizationServiceInterface::class => function(ContainerInterface $container) {
                return new VoidAuthorizationService();
            },
            AuthenticationServiceInterface::class => function(ContainerInterface $container) {
                return new VoidAuthenticationService();
            },
            RecursiveTypeMapperInterface::class => function(ContainerInterface $container) {
                return new RecursiveTypeMapper($container->get(TypeMapperInterface::class));
            },
            TypeMapperInterface::class => function(ContainerInterface $container) {
                return new GlobTypeMapper('TheCodingMachine\\GraphQL\\Controllers\\Fixtures\\Integration\\Types',
                    $container->get(TypeGenerator::class),
                    $container->get('autowiringContainer'),
                    $container->get(AnnotationReader::class),
                    new NullCache()
                    );
            },
            TypeGenerator::class => function(ContainerInterface $container) {
                return new TypeGenerator();
            },
            AnnotationReader::class => function(ContainerInterface $container) {
                return new AnnotationReader(new DoctrineAnnotationReader());
            },
            HydratorInterface::class => function(ContainerInterface $container) {
                return new class implements HydratorInterface
                {
                    public function hydrate(array $data, InputType $type)
                    {
                        return new Contact($data['name']);
                    }
                };
            }
        ]);
        // Fixing the loop
        $this->mainContainer->get(TypeGenerator::class)->setRegistry($this->mainContainer->get(RegistryInterface::class));
    }

    public function testEndToEnd()
    {
        /**
         * @var Schema $schema
         */
        $schema = $this->mainContainer->get(Schema::class);
        $queryString = '
        query {
            getContacts {
                name
            }
        }
        ';

        $result = GraphQL::executeQuery(
            $schema,
            $queryString
        );

        var_dump($result);
    }

}
