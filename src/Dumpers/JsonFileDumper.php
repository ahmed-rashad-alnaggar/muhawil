<?php

namespace Alnaggar\Muhawil\Dumpers;
class JsonFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable JSON representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        return json_encode($translations, $arguments['flags'] ?? JSON_PRETTY_PRINT);
    }
}
