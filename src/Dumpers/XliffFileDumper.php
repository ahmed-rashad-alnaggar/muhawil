<?php

namespace Alnaggar\Muhawil\Dumpers;

/**
 * @method $this dump() dump(array $translations, string $path, string $sourceLocale = 'en', string $targetLocale = 'en', bool $legacy = false, string $fileId = 'f1') Dump translations into the specified XLIFF file.
 */
class XliffFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable XLIFF representation.
     * 
     * @param array $translations
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param bool $legacy
     * @param string $fileId
     * @return string
     */
    public function format(array $translations, string $sourceLocale = 'en', string $targetLocale = 'en', bool $legacy = false, string $fileId = 'f1'): string
    {
        $sourceLocale = str_replace('_', '-', $sourceLocale);
        $targetLocale = str_replace('_', '-', $targetLocale);

        if ($legacy) {
            return $this->formatLegacy($translations, $sourceLocale, $targetLocale);
        } else {
            return $this->formatStandard($translations, $sourceLocale, $targetLocale, $fileId);
        }
    }

    /**
     * Formats translations into a storable XLIFF 1.2 representation.
     *
     * @param array $translations
     * @param string $sourceLocale
     * @param string $targetLocale
     * @return string
     */
    protected function formatLegacy(array $translations, string $sourceLocale, string $targetLocale): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
        $xliff->setAttribute('version', '1.2');

        $file = $xliff->appendChild($dom->createElement('file'));
        $file->setAttribute('source-language', $sourceLocale);
        $file->setAttribute('target-language', $targetLocale);
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
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string $fileId
     * @return string
     */
    protected function formatStandard(array $translations, string $sourceLocale, string $targetLocale, string $fileId): string
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $xliff = $dom->appendChild($dom->createElement('xliff'));
        $xliff->setAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:2.0');
        $xliff->setAttribute('version', '2.0');
        $xliff->setAttribute('srcLang', $sourceLocale);
        $xliff->setAttribute('trgLang', $targetLocale);

        $file = $xliff->appendChild($dom->createElement('file'));
        $file->setAttribute('id', $fileId);

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
