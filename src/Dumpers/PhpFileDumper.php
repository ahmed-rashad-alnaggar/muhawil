<?php

namespace Alnaggar\Muhawil\Dumpers;
class PhpFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable PHP representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        return "<?php" . PHP_EOL . PHP_EOL . "return " . var_export($translations, true) . ";" . PHP_EOL;
    }
}
