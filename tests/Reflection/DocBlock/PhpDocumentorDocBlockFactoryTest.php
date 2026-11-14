<?php

namespace TheCodingMachine\GraphQLite\Tests\Reflection\DocBlock;

use TheCodingMachine\GraphQLite\Reflection\DocBlock\PhpDocumentorDocBlockFactory;
use phpDocumentor\Reflection\DocBlock\Tags\Param;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestController;
use TheCodingMachine\GraphQLite\Tests\Fixtures\TestObject;

#[CoversClass(PhpDocumentorDocBlockFactory::class)]
class PhpDocumentorDocBlockFactoryTest extends TestCase
{
    public function testCreatesDocBlock(): void
    {
        $docBlockFactory = PhpDocumentorDocBlockFactory::default();

        $refMethod = (new ReflectionMethod(TestController::class, 'test'));
        $docBlock = $docBlockFactory->create($refMethod);

        $this->assertCount(1, $docBlock->getTagsByName('param'));

        /** @var Param $paramTag */
        $paramTag = $docBlock->getTagsByName('param')[0];

        $this->assertEquals(
            new Array_(
                new Object_(new Fqsen('\\' . TestObject::class))
            ),
            $paramTag->getType(),
        );
    }

    public function testCreatesContext(): void
    {
        $docBlockFactory = PhpDocumentorDocBlockFactory::default();

        $refMethod = (new ReflectionMethod(TestController::class, 'test'));
        $context = $docBlockFactory->createContext($refMethod);

        $this->assertSame('TheCodingMachine\GraphQLite\Tests\Fixtures', $context->getNamespace());
    }
}
