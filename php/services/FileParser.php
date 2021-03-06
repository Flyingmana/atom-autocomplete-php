<?php

class FileParser
{
    const USE_PATTERN = '/(?:use)(?:[^\w\\\\])([\w\\\\]+)(?![\w\\\\])(?:(?:[ ]+as[ ]+)(\w+))?(?:;)/';
    const NAMESPACE_PATTERN = '/(?:namespace)(?:[^\w\\\\])([\w\\\\]+)(?![\w\\\\])(?:;)/';

    /**
     * @var string Content of the file
     */
    protected $content;

    /**
     * Open the given file
     * @param string $filePath Path to the PHP file
     */
    public function __construct($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception(sprintf('File %s not found', $filePath));
        }

        $this->content = file_get_contents($filePath);
    }

    /**
     * Get the full namespace of the given class
     * @param string $className
     * @param bool   $found     Set to true if use founded
     * @return string
     */
    public function getCompleteNamespace($className, &$found)
    {
        $found = false;
        $lines = explode(PHP_EOL, $this->content);

        $matches = array();
        preg_match(self::NAMESPACE_PATTERN, $this->content, $matches);

        $fullClass = $className;
        if (!empty($matches)) {
            $fullClass = $matches[1] . '\\' . $className;
        }

        foreach ($lines as $line) {
            $matches = array();
            preg_match(self::USE_PATTERN, $line, $matches);

            if (!empty($matches)) {
                if (isset($matches[2]) && $matches[2] == $className) {
                    $found = true;
                    return $matches[1];
                } else if (substr($matches[1], strlen($matches[1]) - strlen($className)) == $className) {
                    $found = true;
                    return $matches[1];
                }
            }

            // Stop if declaration of a class
            if (strpos(trim($line), 'class') === 0) {
                return $fullClass;
            }
        }
        return $fullClass;
    }
}

?>
