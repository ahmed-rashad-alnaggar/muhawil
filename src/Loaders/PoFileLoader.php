<?php

namespace Alnaggar\Muhawil\Loaders;

class PoFileLoader extends FileLoader
{
    /**
     * The delimiter used to separate msgctxt (message context) from msgid (message ID) in the translation key.
     * If null, msgctxt is not included in the translation key, causing later msgid entries to override previous ones with the same value.
     * 
     * @var string|null
     */
    protected $contextDelimeter;

    /**
     * The delimiter used to separate plural strings in the translation key and value.
     * 
     * @var string
     */
    protected $pluralDelimeter;

    /**
     * Creates a new instance.
     * 
     * @param string|null $contextDelimeter The delimiter used to separate msgctxt (message context) from msgid (message ID) in the translation key. Defaults to '::'.
     * If null, msgctxt is not included in the translation key, causing later msgid entries to override previous ones with the same value.
     * @param string $pluralDelimeter The delimiter used to separate plural strings in the translation key and value. Defaults to '|'.
     * @return void    
     */
    public function __construct(?string $contextDelimeter = '::', string $pluralDelimeter = '|')
    {
        $this->setDelimeters($contextDelimeter, $pluralDelimeter);
    }

    /**
     * Parse a valid PO file into translations.
     *
     * @param \SplFileObject $resource
     * @return array
     */
    protected function parse($resource) : array
    {
        $translations = [];
        $key = $value = '';
        $lastLineType = ''; // Helper to ignore header and comments.

        while (! $resource->eof()) {
            $line = trim($resource->fgets()); // Remove whitespace and newline sequence around the line.

            // Empty line means the end of the current unit.
            if (empty($line) || $resource->eof()) {
                // Ensure the last unit is processed.
                if ($resource->eof()) {
                    $this->parseLine($line, $key, $value, $lastLineType);
                }

                // If the key is empty that means the current unit is a non-translation unit (header or a comment unit).
                if ($key !== '') {
                    $key = stripcslashes($key);
                    $value = stripcslashes($value);

                    $translations[$key] = $value;
                }

                // Reset for the next unit.
                $key = $value = $lastLineType = '';
            } else {
                $lastLineType = $this->parseLine($line, $key, $value, $lastLineType);
            }
        }

        return $translations;
    }

    /**
     * Parses a line of the PO file and updates the current key and value being constructed.
     *
     * @param string $line
     * @param string $key
     * @param string $value
     * @param string $lastLineType
     * @return string
     */
    protected function parseLine(string $line, string &$key, string &$value, string $lastLineType) : string
    {
        // Regular expression to match a valid translation line and capture groups.
        // - Group 1 captures the identifier (msgctxt, msgid, msgid_plural, msgstr, msgstr[\d+], none if spaned-line).
        // - Group 2 captures the value of the identifier.
        $pattern = '/^(msg(?:ctxt|id|id_plural|str(?:\[\d+\])?))?\h*"((?:[^"\x5c]|\x5c.)*)".*$/';

        // Ignore non-translation lines (comments, flags, etc.).
        if (preg_match($pattern, $line, $matches)) {
            $id = $matches[1];
            $str = $matches[2];
            $lastLineType = $id ?: $lastLineType;

            if ($id === 'msgctxt') {
                if (! is_null($this->contextDelimeter)) { // Check if msgctxt can be added to the key.
                    if ($str !== '') {
                        $key = "{$str}{$this->contextDelimeter}";
                    }
                }
            } elseif ($id === 'msgid') {
                $key .= $str;
            } elseif ($id === 'msgid_plural') {
                $key .= "{$this->pluralDelimeter}{$str}";
            } elseif ($id === 'msgstr') {
                $value = $str;
            } elseif (empty($id)) { // spaned-line case
                if ($lastLineType === 'msgctxt') {
                    if (! is_null($this->contextDelimeter)) {
                        if ($str !== '') {
                            if ($key === '') {
                                $key = "{$str}{$this->contextDelimeter}";
                            } else {
                                $key .= $str;
                            }
                        }
                    }
                } elseif (strpos($lastLineType, 'msgid') === 0) {
                    $key .= $str;
                } else {
                    $value .= $str;
                }
            } else { // msgstr[\d+] case
                $value .= $value === '' ? "{$str}" : "{$this->pluralDelimeter}{$str}";
            }
        }

        return $lastLineType;
    }

    /**
     * Set delimeters used in formatting of translation keys and values.
     * 
     * @param string|null $contextDelimeter
     * @param string $pluralDelimeter
     * @return static
     */
    public function setDelimeters(?string $contextDelimeter, string $pluralDelimeter)
    {
        $this->contextDelimeter = $contextDelimeter;
        $this->pluralDelimeter = $pluralDelimeter;

        return $this;
    }
}
