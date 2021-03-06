<?php

class AutocompleteProvider extends Tools implements ProviderInterface
{
    /**
     * Execute the command
     * @param  array  $args Arguments gived to the command
     * @return array Response
     */
    public function execute($args = array())
    {
        $class = $args[0];
        $name  = $args[1];

        $classMap = $this->getClassMap();
        $data = $this->getClassMetadata($class);
        if (!isset($data['values'][$name]) || !isset($classMap[$class])) {
            return array(
                'class'  => null,
                'names'  => array(),
                'values' => array()
            );
        }

        $returnValue = $data['values'][$name]['args']['return'];
        if (ucfirst($returnValue) === $returnValue) {
            $parser = new FileParser($classMap[$class]);

            $found = false;
            $className = $parser->getCompleteNamespace($returnValue, $found);

            // Look into its parents if use not found
            if (!$found) {
                try {
                    $reflection = new ReflectionClass($class);
                } catch (Exception $e) {
                    return $className;
                }

                while (($parent = $reflection->getParentClass()) && ($found == false)) {
                    if (isset($classMap[$parent->getName()])) {
                        $parser = new FileParser($classMap[$parent->getName()]);
                        $className = $parser->getCompleteNamespace($returnValue, $found);
                    }
                }
            }

            return $this->getClassMetadata($className);
        }
    }
}
