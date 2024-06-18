<?php

namespace Alnaggar\Muhawil\Loaders;

use Alnaggar\Muhawil\Exceptions\InvalidResourceException;
use Alnaggar\Muhawil\Exceptions\ResourceParsingException;

class YamlFileLoader extends FileLoader
{
    /**
     * Parse a valid Yaml file into translations.
     *
     * @param \SplFileObject $resource
     * @return array
     */
    protected function parse($resource) : array
    {
        $filepath = $resource->getRealPath();
        $lines = $resource->fread($resource->getSize());

        if (! preg_match('//u', $lines)) {
            throw new InvalidResourceException("The YAML file at '{$filepath}' must be UTF-8 encoded.");
        }

        $lines = str_replace(["\r\n", "\r"], "\n", $lines); // Normalize line endings.
        $lines = explode("\n", $lines);

        try {
            return $this->parseLines($lines);
        } catch (\Exception $e) {
            $message = sprintf($e->getMessage(), $filepath);
            $eClass = get_class($e);

            throw new $eClass($message);
        }
    }

    /**
     * Recursively parse YAML lines into a translations array.
     * 
     * @param array $lines
     * @param array $anchors
     * @param int $parentIndentation
     * @param int $lineNumber
     * @throws \Alnaggar\Muhawil\Exceptions\InvalidResourceException
     * @throws \Alnaggar\Muhawil\Exceptions\ResourceParsingException
     * @return array
     */
    protected function parseLines(array &$lines, array &$anchors = [], int $parentIndentation = 0, int &$lineNumber = 0) : array
    {
        $translations = [];
        $mergeKeyTranslations = [];
        $hierarchyIndentation = -1;

        while (! empty($lines)) {
            if (! is_null($line = $this->getNextLine($lines, $lineNumber))) {
                // Regular expression to match a valid translation line and capture groups.
                // - Group 1 captures the key with three alternatives:
                //     1. Double-quoted strings.
                //     2. Single-quoted strings.
                //     3. Unquoted strings without allowing special characters.
                // - Group 2 captures an optional link (anchor or alias).
                // - Group 3 captures an optional value with three alternatives same like key.
                if (preg_match('/^ *((?:"(?:[^"\x5c]|\x5c.)+")|(?:\'(?:[^\']+(?:\'\'[^\']*)*)\')|(?:[^\-?:,\[\]\{\}#&*!|>\'"%@`]+)):[ \t]*((?<=[ \t])[*&]+[a-zA-Z0-9_\-]+)?[ \t]*((?<=[ \t])(?:(?:"(?:[^"\x5c]|\x5c.)+")|(?:\'(?:[^\']+(?:\'\'[^\']*)*)\')|(?:[^\-?:,\[\]\{\}#&*!|>\'"%@`]+)))?[ \t]*(?:(?<=[ \t])#.*)?$/', $line, $matches)) {
                    $currentIndentation = $this->getLineIndentation($line);

                    // Check if first line in the mapping.
                    if ($hierarchyIndentation === -1) {
                        $hierarchyIndentation = $currentIndentation;
                    }

                    // Check if we need to move level up in the mapping.
                    if ($currentIndentation < $hierarchyIndentation) {
                        if ($currentIndentation > $parentIndentation) {
                            throw new InvalidResourceException("The YAML file at '%s' has an invalid indentation detected at line {$lineNumber}.");
                        } else {
                            $this->revertPreviousLine($lines, $lineNumber, $line);
                            break;
                        }
                    }

                    $key = $this->parseKey($matches[1]);
                    $link = $matches[2] ?? '';
                    $value = $this->parseValue($matches[3] ?? '');
                    $isMergeKey = rtrim($matches[1], " \t") === '<<';

                    if (array_key_exists($key, $translations)) {
                        throw new InvalidResourceException("The YAML file at '%s' has a duplicate key detected at line {$lineNumber}.");
                    }

                    if ($isMergeKey && strncmp($link, '*', 1) !== 0) {
                        throw new InvalidResourceException("The YAML file at '%s' expected an alias at line {$lineNumber} because of the merge key.");
                    }

                    $translations[$key] = $value;

                    // Handle referencing.
                    if ($link) {
                        $linkId = substr($link, 1);
                        $linkType = substr($link, 0, 1);

                        if ($linkType === '&') {
                            $anchors[$linkId] = &$translations[$key];
                        } else {
                            if (! is_null($value)) {
                                throw new InvalidResourceException("The YAML file at '%s' has an invalid combination of an alias and value detected at line {$lineNumber}.");
                            } elseif (! array_key_exists($linkId, $anchors)) {
                                throw new InvalidResourceException("The YAML file at '%s' references a non-existent anchor '{$linkId}' detected at line {$lineNumber}.");
                            } elseif ($this->isNextLineExpectsMapping($lines, $lineNumber, $hierarchyIndentation)) {
                                if ($isMergeKey) {
                                    throw new InvalidResourceException("The YAML file at '%s' expects a mapping at line {$lineNumber}, but found a merge Key.");
                                } else {
                                    throw new InvalidResourceException("The YAML file at '%s' expects a mapping at line {$lineNumber}, but found an alias.");
                                }
                            } elseif ($isMergeKey) {
                                if (! is_array($anchors[$linkId])) {
                                    throw new InvalidResourceException("The YAML file at '%s' expected an alias at line {$lineNumber} that refers to a mapping for merging.");
                                } else {
                                    unset($translations[$key]);
                                    $mergeKeyTranslations = $anchors[$linkId];
                                    continue;
                                }
                            } else {
                                $translations[$key] = $anchors[$linkId];
                                continue;
                            }
                        }
                    }

                    // Check if we need to go level down in the mapping.
                    if ($this->isNextLineExpectsMapping($lines, $lineNumber, $hierarchyIndentation)) {
                        if (is_null($value)) {
                            $translations[$key] = $this->parseLines($lines, $anchors, $hierarchyIndentation, $lineNumber);
                        } else {
                            throw new InvalidResourceException("The YAML file at '%s' expected a mapping at line {$lineNumber}.");
                        }
                    }
                } else {
                    throw new ResourceParsingException("Failed to parse YAML file at '%s': Unsupported or invalid YAML structure detected at line {$lineNumber}.");
                }
            }
        }

        return $translations + $mergeKeyTranslations;
    }

    /**
     * Check if next line expects to be inside a mapping.
     * 
     * @param array $lines
     * @param int $lineNumber
     * @param int $hierarchyIndentation
     * @return bool
     */
    protected function isNextLineExpectsMapping(array &$lines, int &$lineNumber, int $hierarchyIndentation) : bool
    {
        if (! is_null($nextLine = $this->getNextLine($lines, $lineNumber))) {
            $this->revertPreviousLine($lines, $lineNumber, $nextLine);

            if ($this->getLineIndentation($nextLine) > $hierarchyIndentation) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the translation line from the lines array.
     * 
     * @param array $lines
     * @param int $lineNumber
     * @return string|null
     */
    protected function getNextLine(array &$lines, int &$lineNumber) : ?string
    {
        while (! empty($lines)) {
            $lineNumber++;
            $line = array_shift($lines);

            // Skip empty lines, comments, tag, and document markers.
            if (! preg_match('/^ *(?:$|%|#|---|\.\.\.)/', $line)) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Revert the previous line into the beginning of the lines array for consistency.

     * @param array $lines
     * @param int $lineNumber
     * @param string $line
     * @return void
     */
    protected function revertPreviousLine(array &$lines, int &$lineNumber, string $line) : void
    {
        $lineNumber--;
        array_unshift($lines, $line);
    }

    /**
     * Get the indentation level of a line.
     * 
     * @param string $line
     * @return int
     */
    protected function getLineIndentation(string $line) : int
    {
        return strlen($line) - strlen(ltrim($line, ' '));
    }

    /**
     * Parse a YAML key, handling potential escaping.
     * 
     * @param string $key
     * @return string
     */
    protected function parseKey(string $key) : string
    {
        // If the value is null, the key is '~' or literally 'null' respecting case sensitivity.
        return $this->parseValue($key) ?? rtrim($key, " \t");
    }

    /**
     * Parse a YAML value, handling escaping and null cases.
     * 
     * @param string $value
     * @return string|null
     */
    protected function parseValue(string $value) : ?string
    {
        if (strncmp($value, '"', 1) === 0) {
            return stripcslashes(substr($value, 1, -1));
        }

        if (strncmp($value, "'", 1) === 0) {
            $value = substr($value, 1, -1);
            return str_replace("''", "'", $value);
        }

        $value = rtrim($value, " \t");

        if (strtolower($value) === 'null' || $value === '~' || $value === '') {
            return null;
        }

        return $value;
    }
}
