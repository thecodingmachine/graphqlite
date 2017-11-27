<?php


namespace TheCodingMachine\GraphQL\Controllers\Reflection;


class CommentParser
{
    private $comment;

    public function __construct(string $docBlock)
    {
        $lines = $this->getAllLinesWithoutStars($docBlock);

        // First, let's go to the first Annotation.
        // Anything before is the pure comment.

        $oneAnnotationFound = false;
        // Is the line an annotation? Let's test this with a regexp.
        foreach ($lines as $line) {
            if ($oneAnnotationFound === false && preg_match("/^[@][a-zA-Z]/", $line) === 0) {
                $this->comment .= $line."\n";
            } else {
                //$this->parseAnnotation($line);
                $oneAnnotationFound = true;
            }
        }

        $this->comment = rtrim($this->comment, "\n");
    }

    /**
     * @param string $docComment
     * @return string[]
     */
    private function getAllLinesWithoutStars(string $docComment): array
    {
        if (strpos($docComment, '/**') === 0) {
            $docComment = substr($docComment, 3);
        }

        // Let's remove all the \r...
        $docComment = str_replace("\r", '', $docComment);

        $commentLines = explode("\n", $docComment);
        $commentLinesWithoutStars = array_map(function(string $line) {
            return ltrim($line, " \t*");
        }, $commentLines);

        // Let's remove the trailing /:
        $lastComment = $commentLinesWithoutStars[count($commentLinesWithoutStars)-1];
        $commentLinesWithoutStars[count($commentLinesWithoutStars)-1] = substr($lastComment, 0, strlen($lastComment)-1);

        if ($commentLinesWithoutStars[count($commentLinesWithoutStars)-1] == "") {
            array_pop($commentLinesWithoutStars);
        }

        if (isset($commentLinesWithoutStars[0]) && $commentLinesWithoutStars[0] === '') {
            $commentLinesWithoutStars = array_slice($commentLinesWithoutStars, 1);
        }
        return $commentLinesWithoutStars;
    }

    /**
     * Returns the comment out of a Docblock, ignoring all annotations
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }
}
