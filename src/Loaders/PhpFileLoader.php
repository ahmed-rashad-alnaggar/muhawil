<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\ResourceParsingException;

class PhpFileLoader extends FileLoader
{
    /**
     * Parse a PHP file into translations.
     *
     * @param \SplFileObject $resource
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceParsingException
     * @return array
     */
    protected function parse($resource): array
    {
        $filepath = $resource->getRealPath();
        $translations = require $filepath;

        if (!is_array($translations)) {
            throw new ResourceParsingException("The PHP file at '{$filepath}' did not return an array of translations.");
        }

        return $translations;
    }
}
