<?php

declare(strict_types=1);

namespace TheCodingMachine\GraphQLite\Mappers;

class MappedClass
{
    /**
     * @var string
     */
    //private $className;

    /**
     * @var MappedClass|null
     */
    //private $parent;

    /** @var MappedClass[] */
    private $children = [];

    /*public function __construct(string $className)
    {
        $this->className = $className;
    }*/

    /**
     * @return string
     */
    /*public function getClassName(): string
    {
        return $this->className;
    }*/

    /**
     * @return MappedClass|null
     */
    /*public function getParent(): ?MappedClass
    {
        return $this->parent;
    }*/

    /**
     * @param MappedClass|null $parent
     */
    /*public function setParent(?MappedClass $parent): void
    {
        $this->parent = $parent;
    }*/

    /**
     * @return MappedClass[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChild(MappedClass $child): void
    {
        $this->children[] = $child;
    }
}
