<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite;

use GraphQL\Type\Definition\FieldDefinition;
use Psr\Container\ContainerInterface;
use TheCodingMachine\GraphQLite\Mappers\DuplicateMappingException;

use function array_filter;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function array_merge;
use function array_sum;
use function array_values;
use function assert;
use function count;
use function reset;
use function sort;

/**
 * A query provider that looks into all controllers of your application to fetch queries.
 */
class AggregateControllerQueryProvider implements QueryProviderInterface
{
    /** @var iterable<string> */
    private $controllers;
    /** @var ContainerInterface */
    private $controllersContainer;
    /** @var FieldsBuilder */
    private $fieldsBuilder;

    /**
     * @param iterable<string>   $controllers          A list of controllers name in the container.
     */
    public function __construct(iterable $controllers, FieldsBuilder $fieldsBuilder, ClassResolver $classResolver)
    {
        $this->controllers          = $controllers;
        $this->fieldsBuilder        = $fieldsBuilder;
        $this->controllersContainer = $classResolver;
    }

    /**
     * @return FieldDefinition[]
     */
    public function getQueries(): array
    {
        $queryList = [];

        foreach ($this->controllersContainer->__invoke($this->controllers) as $controllerName => $real) {
            $queryList[$controllerName] = $this->fieldsBuilder->getQueries($real);
        }

        return $this->flattenList($queryList);
    }

    /**
     * @return FieldDefinition[]
     */
    public function getMutations(): array
    {
        $mutationList = [];

        foreach ($this->controllersContainer->__invoke($this->controllers) as $controllerName => $real) {
            $mutationList[$controllerName] = $this->fieldsBuilder->getMutations($real);
        }

        return $this->flattenList($mutationList);
    }

    /**
     * @param array<string, array<string, FieldDefinition>> $list
     *
     * @return array<string, FieldDefinition>
     */
    private function flattenList(array $list): array
    {
        if (empty($list)) {
            return [];
        }

        $flattenedList = array_merge(...array_values($list));

        // Quick check: are there duplicates? If so, the count of the flattenedList is != from the sum of the count of lists.
        if (count($flattenedList) === array_sum(array_map('count', $list))) {
            return $flattenedList;
        }

        // We have an issue, let's detect the duplicate
        $duplicates = array_intersect_key(...array_values($list));
        // Let's display an error from the first one.
        $firstDuplicate = reset($duplicates);
        assert($firstDuplicate instanceof FieldDefinition);

        $duplicateName = $firstDuplicate->name;

        $classes = array_keys(array_filter($list, static function (array $fields) use ($duplicateName) {
            return isset($fields[$duplicateName]);
        }));
        sort($classes);

        throw DuplicateMappingException::createForQueryInTwoControllers($classes[0], $classes[1], $duplicateName);
    }
}
