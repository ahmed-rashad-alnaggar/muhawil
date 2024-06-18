<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\ResourceParsingException;

class XliffFileLoader extends FileLoader
{
    /**
     * Parse a valid Xliff file into translations.
     *
     * @param \SplFileObject $resource
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceParsingException
     * @return array
     */
    protected function parse($resource) : array
    {
        $translations = [];

        try {
            $xml = @new \SimpleXMLElement($resource->getRealPath(), LIBXML_NONET | LIBXML_COMPACT, true);
        } catch (\Exception $e) {
            throw new ResourceParsingException("Failed to parse XLIFF file at '{$resource->getRealPath()}': {$e->getMessage()}", $e->getCode(), $e);
        }

        $namespace = $xml->getNamespaces(true)['']; // Get the main namespace for a valid Xliff structure.
        $xml->registerXPathNamespace('xliff', $namespace);

        // Handle both standard and legacy Xliff using a smart XPath query.
        foreach ($xml->xpath('//xliff:unit/xliff:segment | //xliff:trans-unit') as $unit) {
            $key = (string) $unit->source ?? '';
            $value = (string) $unit->target ?? '';

            if ($key !== '') {
                $translations[$key] = $value;
            }
        }

        return $translations;
    }
}
