# Muhawil - PHP Package For Loading And Dumping Translations

![I Stand With Palestine Badge](./images/PalestineBadge.svg)

![I Stand With Palestine Banner](./images/PalestineBanner.svg)

Muhawil is a PHP package designed to simplify the process of loading and dumping translations. The name 'Muhawil' is derived from the Arabic word "**مُحَوِّل**", meaning transformer or adapter, as it facilitates reading and writing translations in several formats.

The package natively supports 6 file types:

- [JSON](#json)
- [MO (Machine Object)](#mo)
- [PHP](#php)
- [PO (Portable Object)](#po)
- [XLIFF (XML Localization Interchange File Format)](#xliff)
- [YAML](#yaml)

Additionally, you can create your own [custom loaders and dumpers](#custom-loaders-and-dumpers).

## Requirements

- PHP >= 7.3
- `ext-json` PHP extension
- `ext-pcre` PHP extension

## Installation

Install the package using Composer:

```bash
composer require alnaggar/muhawil
```

## Loading Translations

All files loaders extend the `Alnaggar\Muhawil\Loaders\FileLoader` abstract class, which provides the `load (string $path)` method to load the transaltions from the specified file.

```php
# Example of loading translations from a PHP file.

use Alnaggar\Muhawil\Loaders\PhpFileLoader;

$loader = new PhpFileLoader;

$translations = $loader->load('path/to/translations/file.php');
```

### Handling Missing Translation Values

The `FileLoader` class uses the `Alnaggar\Muhawil\Traits\HasMissingTranslationValueHandler` trait. By default, if a translation value is missing (empty or `null`), the translation **key** itself is returned. This makes missing translations are visibly flagged in the UI for correction.

To override this behavior, pass a callback to `setMissingTranslationValueCallback()` which receives:

- The translation `$key`.
- `$path` (only for `FileLoader` classes).

The callback function is responsible for determining and returning the appropriate value.

```php
# Example of logging missing translations for later review.

use Alnaggar\Muhawil\Loaders\PhpFileLoader;

$loader = new PhpFileLoader;

$callback = function (string $key, string $path) {
  $message = "Found an untranslated key: {$key} while loading the translations at {$path}";
  error_log($message);

  // Return the key as the translation value so it can be visibly flagged for correction.
  return $key;
};

$loader->setMissingValueCallback($callback);

$translations = $loader->load('path/to/translations/file.php');
```

## Dumping Translations

All file dumpers extend the `Alnaggar\Muhawil\Dumpers\FileDumper` abstract class, and each has its own `dump()` method signature as described in each section below.

## JSON

Load JSON translations using `Alnaggar\Muhawil\Loaders\JsonFileLoader` class.

Dump JSON translations using `Alnaggar\Muhawil\Dumpers\JsonFileDumper` class, using its `dump(array $translations, string $path, int $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT): static` method.

- `$flags`: A bitmask that controls the behavior of the JSON encoding process.

```php
// Example of dumping translations into a JSON file.

use Alnaggar\Muhawil\Dumpers\JsonFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new JsonFileDumper;

$dumper->dump($translations, 'path/to/translations/file.json', JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE);
```

## MO

Load MO translations using `Alnaggar\Muhawil\Loaders\MoFileLoader` class.

Dump MO translations using `Alnaggar\Muhawil\Dumpers\MoFileDumper` class, using its `dump(array $translations, string $path, array $metadata = []): static` method.

- `$metadata`: An associative array to include additional information about the translation file, such as language, authorship, or pluralization rules.

The dumper uses the `Alnaggar\Muhawil\Traits\HasPluralForms` trait, to automatically generates the `Plural-Forms` metadata if the `Language` metadata is provided and `Plural-Forms` is not explicitly set.

Both The MO loader and dumper constructors accept two optional parameters:

- `?string $contextDelimiter = '::'`: Delimiter for message context in translation keys.

- `?string $pluralDelimiter = '|'`: Delimiter for pluralization in translation keys and values.

```php
// Example of dumping translations into an MO file.

use Alnaggar\Muhawil\Dumpers\MoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$metadata  = [
    'Language' => 'ar',
];

$dumper = new MoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.mo', $metadata);
```

## PHP

Load PHP translations using `Alnaggar\Muhawil\Loaders\PhpFileLoader` class.

Dump PHP translations using `Alnaggar\Muhawil\Dumpers\PhpFileDumper` class, using its `dump(array $translations, string $path): static` method.

```php
// Example of dumping translations into a PHP file.

use Alnaggar\Muhawil\Dumpers\PhpFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new PhpFileDumper;

$dumper->dump($translations, 'path/to/translations/file.php');
```

## PO

Load PO translations using `Alnaggar\Muhawil\Loaders\PoFileLoader` class.

Dump PO translations using `Alnaggar\Muhawil\Dumpers\PoFileDumper` class, using its `dump(array $translations, string $path, array $metadata = []): static` method.

- `$metadata`: An associative array to include additional information about the translation file, such as language, authorship, or pluralization rules.

The dumper uses the `Alnaggar\Muhawil\Traits\HasPluralForms` trait, to automatically generates the `Plural-Forms` metadata if the `Language` metadata is provided and `Plural-Forms` is not explicitly set.

Both The PO loader and dumper constructors accept two optional parameters:

- `?string $contextDelimiter = '::'`: Delimiter for message context in translation keys.

- `?string $pluralDelimiter = '|'`: Delimiter for pluralization in translation keys and values.

```php
// Example of dumping translations into a PO file.

use Alnaggar\Muhawil\Dumpers\PoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$metadata  = [
    'Language' => 'ar',
];

$dumper = new PoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.po', $metadata);
```

## XLIFF

Load XLIFF translations using `Alnaggar\Muhawil\Loaders\XliffFileLoader` class.

Dump XLIFF translations using `Alnaggar\Muhawil\Dumpers\XliffFileDumper` class, using its `dump(array $translations, string $path, string $sourceLocale = 'en', string $targetLocale = 'en', bool $legacy = false, string $fileId = 'f1'): static` method.

- `$sourceLocale`: Specifies the source language of the translations.

- `$targetLocale`: Specifies the target language of the translations.

- `$legacy`: Determines whether to confrom to XLIFF 1.2 (`true`) or XLIFF 2.0 (`false`).

- `$fileId`: When `$legacy` is set to `false` (indicating XLIFF 2.0), this parameter is used to specify the `id` attribute for the `<file>` node in the XLIFF 2.0 structure.

```php
// Example of dumping translations into an XLIFF file.

use Alnaggar\Muhawil\Dumpers\XliffFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new XliffFileDumper;

$dumper->dump($translations, 'path/to/translations/file.', 'en', 'en', false);
```

## YAML

Load YAML translations using `Alnaggar\Muhawil\Loaders\YamlFileLoader` class.

Dump YAML translations using `Alnaggar\Muhawil\Dumpers\YamlFileDumper` class, using its `dump(array $translations, string $path, bool $dry = true): static` method.

- `$dry`: Determines whether to generate anchors and aliases for similar **mappings** in the YAML structure.

```php
// Example of dumping translations into a YAML file.

use Alnaggar\Muhawil\Dumpers\YamlFileDumper;

$translations = [
    'welcome_message' => 'Welcome to our application!',
    'greeting' => 'Hello, world!',
    'farewell' => 'Goodbye, see you soon!',
    'errors' => [
        'not_found' => 'The requested resource was not found.',
        'internal_error' => 'An internal server error occurred. Please try again later.'
    ],
    'common_actions' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete'
    ],
    'user' => [
        'profile' => 'Profile',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'actions' => [
            'create' => 'Create',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete'
        ]
    ]
];

$dumper = new YamlFileDumper;

$dumper->dump($translations, 'path/to/translations/file.yaml', true);
```

Both the YAML loader and dumper support only simple YAML structures, including mappings, nested mappings, and scalar values. Keys and scalar values may be double-quoted, single-quoted, or unquoted (as long as they do not contain special YAML characters).

## Custom Loaders and Dumpers

You can implement your own loaders and dumpers for any type of storage, like a database or any other storage, by implementing the `Alnaggar\Muhawil\Interfaces\Loader` and `Alnaggar\Muhawil\Interfaces\Dumper` interfaces and their respective `load` and `dump` methods.

For example, to create a custom loader for a database, you would create a class that implements the `Loader` interface and define the `load` method to fetch translations from the database. Similarly, for a custom dumper, implement the `Dumper` interface and define the `dump` method to save translations back to the database.

This flexibility allows you to tailor Muhawil to your specific needs beyond the provided file-based loaders and dumpers.

## Contributing

If you find any issues or have suggestions for improvements, feel free to open an issue or submit a pull request on the GitHub repository.

## Credits

- Palestine banner and badge by [Safouene1](https://github.com/Safouene1/support-palestine-banner).

## License

Muhawil is open-sourced software licensed under the [MIT license](LICENSE).
