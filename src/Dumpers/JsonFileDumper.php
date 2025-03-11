<?php

namespace Alnaggar\Muhawil\Dumpers;

/**
 * @method $this dump() dump(array $translations, string $path, int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) Dump translations into the specified JSON file.
 */
class JsonFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable JSON representation.
     *
     * @param array $translations
     * @param int $flags
     * @return string
     */
    public function format(array $translations, int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): string
    {
        return json_encode($translations, $flags);
    }
}
