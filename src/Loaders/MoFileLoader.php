<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\InvalidResourceException;

class MoFileLoader extends FileLoader
{
    /**
     * The delimiter used to separate message context from message ID in the translation key.
     * If null, message context is not included in the translation key,
     * which may cause later message ID entries to override previous ones with the same value.
     * 
     * @var string|null
     */
    protected $contextDelimiter;

    /**
     * The delimiter used to separate plural strings in the translation key and value.
     * 
     * @var string
     */
    protected $pluralDelimiter;

    /**
     * Creates a new instance.
     * 
     * @param string|null $contextDelimiter The delimiter used to separate message context from message ID in the translation key. Defaults to '::'.
     * If null, message context is not included in the translation key,
     * which may cause later message ID entries to override previous ones with the same value.
     * @param string $pluralDelimiter The delimiter used to separate plural strings in the translation key and value. Defaults to '|'.
     * @return void    
     */
    public function __construct(?string $contextDelimiter = '::', string $pluralDelimiter = '|')
    {
        $this->setDelimiters($contextDelimiter, $pluralDelimiter);
    }

    /**
     * Parse a valid MO file into translations.
     *
     * @param \SplFileObject $resource
     * @throws \Alnaggar\Muhawil\Exceptions\InvalidResourceException
     * @return array
     */
    protected function parse($resource): array
    {
        $filepath = $resource->getRealPath();
        $format = 'V';

        // Check if the header size is valid.
        if ($resource->fstat()['size'] < 28) {
            throw new InvalidResourceException("The MO file at '{$filepath}' is invalid: Invalid header size.");
        }

        // Determine the endianness of the file.
        $magicNumber = unpack($format, $resource->fread(4))[1];
        if ($magicNumber === 0xDE120495) {
            $format = 'N';
        } else if ($magicNumber !== 0x950412DE) {
            throw new InvalidResourceException("The MO file at '{$filepath}' is invalid: Invalid magic number.");
        }

        $header = $this->parseHeader($resource, $format);

        return $this->parseTranslations($resource, $header, $format);
    }

    /**
     * Parse the header of the MO file.
     *
     * @param \SplFileObject $resource
     * @param string $format
     * @return array
     */
    protected function parseHeader(\SplFileObject $resource, string $format): array
    {
        return unpack(
            "{$format}formatRevision/{$format}numStrings/{$format}originalsTableOffset/{$format}translationsTableOffset/{$format}hashTableSize/{$format}hashTableOffset",
            $resource->fread(24)
        );
    }

    /**
     * Parse the translations from the MO file.
     *
     * @param \SplFileObject $resource
     * @param array $header
     * @param string $format
     * @return array
     */
    protected function parseTranslations(\SplFileObject $resource, array $header, string $format): array
    {
        $translations = [];

        $numStrings = $header['numStrings'];

        $resource->fseek($header['originalsTableOffset']);
        $originalsTable = $resource->fread($numStrings * 8);

        $resource->fseek($header['translationsTableOffset']);
        $translationsTable = $resource->fread($numStrings * 8);

        for ($i = 0; $i < $numStrings; $i++) {
            $o = unpack("{$format}length/{$format}offset", substr($originalsTable, $i * 8, 8));
            $t = unpack("{$format}length/{$format}offset", substr($translationsTable, $i * 8, 8));

            if ($o['length'] < 1 || $t['length'] < 1) {
                continue;
            }

            $resource->fseek($o['offset']);
            $original = $resource->fread($o['length']);

            $resource->fseek($t['offset']);
            $translation = $resource->fread($t['length']);

            // Handle context and plural delimiters.
            $original = explode("\x04", $original);
            $original = is_null($this->contextDelimiter) ? end($original) : implode($this->contextDelimiter, $original);
            $original = implode($this->pluralDelimiter, explode("\x00", $original));
            $translation = implode($this->pluralDelimiter, explode("\x00", $translation));

            $original = stripcslashes($original);
            $translation = stripcslashes($translation);

            $translations[$original] = $translation;
        }

        return $translations;
    }

    /**
     * Set the delimiters used in formatting translation keys and values.
     * 
     * @param string|null $contextDelimiter
     * @param string $pluralDelimiter
     * @return static
     */
    public function setDelimiters(?string $contextDelimiter, string $pluralDelimiter)
    {
        $this->contextDelimiter = $contextDelimiter;
        $this->pluralDelimiter = $pluralDelimiter;

        return $this;
    }
}
