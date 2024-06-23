<?php

namespace Alnaggar\Muhawil\Dumpers;

class YamlFileDumper extends FileDumper
{
    /**
     * Formats translations into a storable YAML representation.
     *
     * @param array $translations
     * @param array $arguments
     * @return string
     */
    public function format(array $translations, array $arguments = []) : string
    {
        $output = '---' . "\n";

        if ($arguments['dry'] ?? true) {
            [$arrs, $hashes] = $this->getArraysAndHashes($translations);
            [$anchors, $merges] = $this->getAnchorsAndMerges($arrs, $hashes);

            $output .= $this->formatTranslations($translations, $anchors, $merges, $hashes);
        } else {
            $output .= $this->formatTranslations($translations);
        }

        $output .= '...';

        return $this->reAnchor($output, array_keys($anchors));
    }

    /**
     * Get array of all arrays in the translations and get their hashes for better lookup.
     * 
     * @param array $translations
     * @param array $arrs
     * @param array $hashes
     * @return array
     */
    protected function getArraysAndHashes(array $translations, array &$arrs = [], array &$hashes = []) : array
    {
        foreach ($translations as $translation) {
            if (is_array($translation)) {
                $arrs[] = $translation;

                if (! in_array($translation, $hashes, true)) {
                    $hash = md5(json_encode($translation));
                    $hashes[$hash] = $translation;
                }

                $this->getArraysAndHashes($translation, $arrs, $hashes);
            }
        }

        return [$arrs, $hashes];
    }

    /**
     * Get arrays that can be anchored and merged in the YAML output to reduce redundancy.
     * 
     * @param array $arrs
     * @param array $hashes
     * @return array
     */
    protected function getAnchorsAndMerges(array $arrs, array $hashes) : array
    {
        $anchors = [];
        $merges = [];

        while (! empty($arrs)) {
            $arr = array_shift($arrs);

            if (! in_array($arr, $anchors, true)) {
                $isAnchor = false;
                $anchorName = count($anchors) + 1;

                foreach ($arrs as $arr2) {
                    if ($arr === $arr2) {
                        $isAnchor = true;
                    } else {
                        $arrCount = count($arr);
                        $arr2Count = count($arr2);

                        if ($arr2Count >= $arrCount) {
                            $diff = array_udiff_assoc($arr2, $arr, function ($a, $b) {
                                return $a <=> $b;
                            });
                            $quotient = count($diff) / $arr2Count;

                            if ($quotient <= 0.5 && $quotient > 0) {
                                $hash = array_search($arr2, $hashes, true);

                                if (is_null($lastMergeQuotient = $merges[$hash][0] ?? null) || $quotient < $lastMergeQuotient) {
                                    $isAnchor = true;

                                    $merges[$hash] = [$quotient, $anchorName, $diff];
                                }
                            }
                        }
                    }
                }

                if ($isAnchor) {
                    if (! in_array($arr, $anchors, true)) {
                        $anchors[$anchorName] = $arr;
                    }
                }
            }
        }

        return [$anchors, $merges];
    }

    /**
     * Format translations recursively into YAML strings with anchors, aliases and merges where applicable.
     * 
     * @param array $translations
     * @param array $anchors
     * @param array $merges
     * @param array $hashes
     * @param array $anchored
     * @param int $indentLevel
     * @return string
     */
    protected function formatTranslations(array $translations, array $anchors = [], array $merges = [], array $hashes = [], array &$anchored = [], int $indentLevel = 0) : string
    {
        $output = '';
        $indent = str_repeat('  ', $indentLevel);

        foreach ($translations as $key => $value) {
            $key = $this->formatValue($key);

            if (is_array($value)) {
                $anchorName = array_search($value, $anchors, true);

                if ($anchorName) {
                    if (in_array($anchorName, $anchored, true)) {
                        $output .= "{$indent}{$key}: *{$anchorName}" . "\n";

                        continue;
                    } else {
                        $anchored[] = $anchorName;

                        $output .= "{$indent}{$key}: &{$anchorName}" . "\n";
                    }
                } else {
                    $output .= "{$indent}{$key}:" . "\n";
                }

                $hash = array_search($value, $hashes, true);

                if (array_key_exists($hash, $merges)) {
                    $anchorName = $merges[$hash][1];
                    $value = $merges[$hash][2];

                    $output .= "{$indent}  <<: *{$anchorName}" . "\n";
                    $output .= $this->formatTranslations($value, $anchors, $merges, $hashes, $anchored, $indentLevel + 1);

                    continue;
                }

                $output .= $this->formatTranslations($value, $anchors, $merges, $hashes, $anchored, $indentLevel + 1);
            } else {
                $value = $this->formatValue($value);

                $output .= "{$indent}{$key}: {$value}" . "\n";
            }
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
     * Remove the unused anchors then rename the rest.
     * 
     * @param string $formatted
     * @param array $anchors
     * @return string
     */
    protected function reAnchor(string $formatted, array $anchors) : string
    {
        $anchorId = 0;

        foreach ($anchors as $anchor) {
            // The pattern is adjusted for use in the sprintf function.
            $pattern = '/^( *)((?:"(?:[^"\x5c]|\x5c.)+")|(?:\'(?:[^\']+(?:\'\'[^\']*)*)\')|(?:[^\-?:,\[\]\{\}#&*!|>\'"%%@`]+)):[ \t]+(?:%s%s)/m';

            if (! preg_match(sprintf($pattern, '\*', $anchor), $formatted)) {
                $formatted = preg_replace(sprintf($pattern, '&', $anchor), '$1$2:', $formatted);
            } else {
                $formatted = preg_replace(sprintf($pattern, '(&|\*)', $anchor), '$1$2: $3anchor_' . ++$anchorId, $formatted);
            }
        }

        return $formatted;
    }
}
