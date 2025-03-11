<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\ResourceParsingException;

class JsonFileLoader extends FileLoader
{
    /**
     * Parse a valid JSON file into translations.
     *
     * @param \SplFileObject $resource
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceParsingException
     * @return array
     */
    protected function parse($resource): array
    {
        try {
            $content = $resource->fread($resource->getSize());

            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new ResourceParsingException("Failed to parse JSON file at '{$resource->getRealPath()}': {$e->getMessage()}", $e->getCode(), $e);
        }
    }
}
