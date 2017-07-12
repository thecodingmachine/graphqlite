<?php


namespace TheCodingMachine\GraphQL\Controllers\Fixtures;


use TheCodingMachine\GraphQL\Controllers\Annotations\Mutation;
use TheCodingMachine\GraphQL\Controllers\Annotations\Query;

class TestController
{
    /**
     * @Query()
     * @param int $int
     * @param null|string $string
     * @param TestObject[] $list
     * @return TestObject
     */
    public function test(int $int, ?string $string, array $list): TestObject
    {
        $str = '';
        foreach ($list as $test) {
            if (!$test instanceof TestObject) {
                throw new \RuntimeException('TestObject instance expected.');
            }
            $str .= $test->getTest();
        }
        return new TestObject($string.$int.$str);
    }

    /**
     * @Mutation()
     * @param TestObject $testObject
     * @return TestObject
     */
    public function mutation(TestObject $testObject): TestObject
    {
        return $testObject;
    }
}
