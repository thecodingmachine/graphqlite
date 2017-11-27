<?php

namespace TheCodingMachine\GraphQL\Controllers\Reflection;

use PHPUnit\Framework\TestCase;

class CommentParserTest extends TestCase
{
    public function testParse()
    {
        $commentParser = new CommentParser(<<<EOF
/**
 * Foo
 * Bar
 *
 * @param string \$id yop
 */
EOF
        );

        $this->assertSame("Foo\nBar", $commentParser->getComment());
    }
}
