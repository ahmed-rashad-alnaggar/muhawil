<?php

namespace Alnaggar\Muhawil\Dumpers;

class XliffFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable XLIFF representation.
     *
     * @param array $translations
     * @param array $arguments
     * @throws \InvalidArgumentException
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        if (! array_key_exists('source_locale', $arguments)) {
            throw new \InvalidArgumentException("Source locale must be specified.");
        }

        if (! array_key_exists('target_locale', $arguments)) {
            throw new \InvalidArgumentException("Target locale must be specified.");
        }

        $arguments['source_locale'] = str_replace('_', '-', $arguments['source_locale']);
        $arguments['target_locale'] = str_replace('_', '-', $arguments['target_locale']);

        if ($arguments['legacy'] ?? false) {
            return $this->formatLegacy($translations, $arguments);
        } else {
            return $this->formatStandard($translations, $arguments);
        }
    }

    /**
     * Formats translations into a storable XLIFF 1.2 representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    protected function formatLegacy(array $translations, array $arguments) : string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
        $xliff->setAttribute('version', '1.2');

        $file = $xliff->appendChild($dom->createElement('file'));
        $file->setAttribute('source-language', $arguments['source_locale']);
        $file->setAttribute('target-language', $arguments['target_locale']);
        $file->setAttribute('datatype', 'plaintext');
        $file->setAttribute('original', 'file.ext');

        $body = $file->appendChild($dom->createElement('body'));

        $id = 0;
        foreach ($translations as $source => $target) {
            $unit = $dom->createElement('trans-unit');
            $unit->setAttribute('id', ++$id);

            $sourceElement = $dom->createElement('source');
            $sourceElement->appendChild($dom->createTextNode($source));

            $targetElement = $dom->createElement('target');
            $targetElement->appendChild($dom->createTextNode($target));

            $unit->appendChild($sourceElement);
            $unit->appendChild($targetElement);

            $body->appendChild($unit);
        }

        return $dom->saveXML();
    }

    /**
     * Formats translations into a storable XLIFF 2.0 representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    protected function formatStandard(array $translations, array $arguments) : string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:2.0');
        $xliff->setAttribute('version', '2.0');
        $xliff->setAttribute('srcLang', $arguments['source_locale']);
        $xliff->setAttribute('trgLang', $arguments['target_locale']);

        $file = $xliff->appendChild($dom->createElement('file'));
        $file->setAttribute('id', $arguments['file_id'] ?? 'f1');

        $id = 0;
        foreach ($translations as $source => $target) {
            $unit = $dom->createElement('unit');
            $unit->setAttribute('id', ++$id);

            $segment = $dom->createElement('segment');

            $sourceElement = $dom->createElement('source');
            $sourceElement->appendChild($dom->createTextNode($source));

            $targetElement = $dom->createElement('target');
            $targetElement->appendChild($dom->createTextNode($target));

            $segment->appendChild($sourceElement);
            $segment->appendChild($targetElement);

            $unit->appendChild($segment);
            $file->appendChild($unit);
        }

        return $dom->saveXML();
    }
}
