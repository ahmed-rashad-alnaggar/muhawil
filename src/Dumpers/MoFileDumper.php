<?php

namespace Alnaggar\Muhawil\Dumpers;

class MoFileDumper extends FileDumper
{
    /**
     * The delimiter used to identify message context from the translation key.
     * If null or empty, assume none of the translations has a message context.
     * 
     * @var string|null
     */
    protected $contextDelimiter;

    /**
     * The delimiter used to identify plural strings from the translation key and value.
     * If null or empty, assume none of the translations has a plural form.
     * 
     * @var string|null
     */
    protected $pluralDelimiter;

    /**
     * Creates a new instance.
     * 
     * @param string|null $contextDelimiter The delimiter used to identify message context from the translation key.
     * If null or empty, assume none of the translations has a message context. Defaults to '::'.
     * @param string|null $pluralDelimiter The delimiter used to identify plural strings from the translation key and value.
     * If null or empty, assume none of the translations has a plural form. Defaults to '|'.
     * @return void    
     */
    public function __construct(?string $contextDelimiter = '::', ?string $pluralDelimiter = '|')
    {
        $this->setDelimiters($contextDelimiter, $pluralDelimiter);
    }

    /**
     * Formats translations into a storable MO representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        $metadata = $this->formatMetadata($arguments['metadata'] ?? []);
        $translations = ['' => $metadata] + $translations;

        $count = count($translations);

        $header = [
            0x950412DE, // Magic Number
            0, // Format Revision
            $count, // Num Strings
            28, // Originals Table Offset
            28 + $count * 8, // Translations Table Offset
            0, // hash Table Size
            28 + $count * 16 // Hash Table Offset
        ];

        $originalStrings = '';
        $translationStrings = '';

        $offsets = [];

        foreach ($translations as $original => $translation) {
            if (! is_null($this->contextDelimiter)) {
                $original = explode($this->contextDelimiter, $original, 2);
                $original = implode("\x04", $original);
            }

            if (! is_null($this->pluralDelimiter)) {
                $original = explode($this->pluralDelimiter, $original);
                $original = implode("\x00", $original);

                $translation = explode($this->pluralDelimiter, $translation);
                $translation = implode("\x00", $translation);
            }

            $offsets[] = array_map('strlen', [$original, $originalStrings, $translation, $translationStrings]);

            $originalStrings .= "\0" . $original;
            $translationStrings .= "\0" . $translation;
        }

        $originalsTable = '';
        $translationsTable = '';

        $startOffset = $header[6] + 1;
        $originalsSize = strlen($originalStrings);

        foreach ($offsets as $offset) {
            $originalsTable .= pack('V2', $offset[0], $offset[1] + $startOffset);
            $translationsTable .= pack('V2', $offset[2], $offset[3] + $startOffset + $originalsSize);
        }

        return pack('V7', ...$header) . $originalsTable . $translationsTable . $originalStrings . $translationStrings;
    }

    /**
     * Format the metadata part of the MO file.
     * 
     * @param array $metadata
     * @return string
     */
    protected function formatMetadata(array $metadata) : string
    {
        $output = '';

        $metadata = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit'
        ] + $metadata;

        foreach ($metadata as $key => $value) {
            $output .= "{$key}: {$value}\\n" . "\n";
        }

        return $output;
    }

    /**
     * Set the delimiters used in recognition of message context and pluralization in translation keys and values.
     * 
     * @param string|null $contextDelimiter
     * @param string|null $pluralDelimiter
     * @return static
     */
    public function setDelimiters(?string $contextDelimiter, ?string $pluralDelimiter)
    {
        if ($contextDelimiter === '') {
            $contextDelimiter = null;
        }

        if ($pluralDelimiter === '') {
            $pluralDelimiter = null;
        }

        $this->contextDelimiter = $contextDelimiter;
        $this->pluralDelimiter = $pluralDelimiter;

        return $this;
    }
}
