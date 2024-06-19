<?php

namespace Alnaggar\Muhawil\Dumpers;

class PoFileDumper extends FileDumper
{
    /**
     * The delimiter used to identify msgctxt (message context) from the translation key.
     * If null or empty, assume none of the translations has a message context (no msgctxt entity).
     * 
     * @var string|null
     */
    protected $contextDelimiter;

    /**
     * The delimiter used to identify plural strings from the translation key and value.
     * If null or empty, assume none of the translations has a plural form (no msgid_plural and msgstr[\d] entities).
     * 
     * @var string|null
     */
    protected $pluralDelimiter;

    /**
     * Creates a new instance.
     * 
     * @param string|null $contextDelimiter The delimiter used to identify msgctxt (message context) from the translation key.
     * If null or empty, assume none of the translations has a message context (msgctxt will not be added). Defaults to '::'.
     * @param string|null $pluralDelimiter The delimiter used to identify plural strings from the translation key and value.
     * If null or empty, assume none of the translations has a plural form (msgid_plural and msgstr[\d] will not be added). Defaults to '|'.
     * @return void    
     */
    public function __construct(?string $contextDelimiter = '::', ?string $pluralDelimiter = '|')
    {
        $this->setDelimiters($contextDelimiter, $pluralDelimiter);
    }

    /**
     * Formats translations into a storable PO representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        return $this->formatHeader($arguments['metadata'] ?? []) . $this->formatTranslations($translations);
    }

    /**
     * Format the header part for the PO file.
     * 
     * @param array $metadata
     * @return string
     */
    protected function formatHeader(array $metadata) : string
    {
        $output = '';

        $metadata = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit'
        ] + $metadata;

        $output .= "msgid \"\"" . "\n";
        $output .= "msgstr \"\"" . "\n";

        foreach ($metadata as $key => $value) {
            $output .= "\"{$key}: {$value}\\n\"" . "\n";
        }

        return $output;
    }

    /**
     * Format the translations part of the PO file.
     *
     * @param array $translations
     * @return string
     */
    protected function formatTranslations(array $translations) : string
    {
        $output = '';

        foreach ($translations as $key => $value) {
            $output .= "\n";

            if (! is_null($this->contextDelimiter)) {
                $ctxtAndId = explode($this->contextDelimiter, $key, 2);

                if (count($ctxtAndId) === 2) {
                    $ctxt = $this->formatValue($ctxtAndId[0]);

                    $output .= "msgctxt {$ctxt}" . "\n";
                }

                $key = end($ctxtAndId);
            }

            if (! is_null($this->pluralDelimiter)) {
                $ids = explode($this->pluralDelimiter, $key);
                $strs = explode($this->pluralDelimiter, $value);

                if (count($ids) === 2 && count($strs) >= 2) {
                    $id = $this->formatValue($ids[0]);
                    $id_plural = $this->formatValue($ids[1]);

                    $output .= "msgid {$id}" . "\n";
                    $output .= "msgid_plural {$id_plural}" . "\n";

                    for ($i = 0; $i < count($strs); $i++) {
                        $str_plural = $this->formatValue($strs[$i]);

                        $output .= "msgstr[{$i}] {$str_plural}" . "\n";
                    }

                    continue;
                }
            }

            $id = $this->formatValue($key);
            $str = $this->formatValue($value);

            $output .= "msgid {$id}" . "\n";
            $output .= "msgstr {$str}" . "\n";
        }

        return $output;
    }

    /**
     * Double qoute the value and escape non-printable control characters and `"` `\`.
     * 
     * @param string $value
     * @return string
     */
    protected function formatValue(string $value) : string
    {
        return '"' . addcslashes($value, "\0..\37\42\134") . '"';
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
