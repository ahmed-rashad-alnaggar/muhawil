# Muhawil - PHP Package For Loading And Dumping Translations

![I Stand With Palestine Badge](./images/PalestineBadge.svg)

![I Stand With Palestine Banner](./images/PalestineBanner.svg)

Muhawil is a PHP package designed to simplify the process of loading and dumping translations. The name "muhawil" is derived from the Arabic word **مُحول**, meaning transformer or adapter.

The package natively supports 6 file types:

- [JSON](#json)
- [MO (Machine Object)](#mo)
- [PHP](#php)
- [PO (Portable Object)](#po)
- [XLIFF (XML Localization Interchange File Format)](#xliff)
- [YAML](#yaml)

Additionally, you can create your own [custom loaders and dumpers](#custom-loaders-and-dumpers).

## Installation

Install the package using Composer:

```bash
composer require alnaggar/muhawil
```

## File Loaders

All files loaders extend the `Alnaggar\Muhawil\Loaders\FileLoader` abstract class, which provides the `load (string $path)` method to load the transaltions from the specified file at that path.

```php
# Example of loading translations from a PHP file.

use Alnaggar\Muhawil\Loaders\PhpFileLoader;

$loader = new PhpFileLoader;

$loader->load('path/to/translations/file.php');
```

## File Dumpers

All file dumpers extend the `Alnaggar\Muhawil\Dumpers\FileDumper` abstract class, which provides the `dump (array $translations, string $path, array $arguments [])` method to dump translations into the specified file at that path, using the passed arguments if needed.

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

## JSON

Load JSON translations using `Alnaggar\Muhawil\Loaders\JsonFileLoader` class.

Dump JSON translations using `Alnaggar\Muhawil\Dumpers\JsonFileDumper` class, with an optional `flags` argument to be passed to the `json_encode` function.

```php
// Example of dumping translations into a JSON file.

use Alnaggar\Muhawil\Dumpers\JsonFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new JsonFileDumper;

$dumper->dump($translations, 'path/to/translations/file.json', ['flags' => JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE]);
```

## MO

Load MO translations using `Alnaggar\Muhawil\Loaders\MoFileLoader` class.

Dump MO translations using `Alnaggar\Muhawil\Dumpers\MoFileDumper` class, with an optional `metadata` argument to be included in the MO file like `Language` and `Plural-Forms`.

```php
// Example of dumping translations into an MO file.

use Alnaggar\Muhawil\Dumpers\MoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new MoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.mo', ['metadata' => [
    'Language' => 'ar'
    'Plural-Forms' => 'nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;'
]]);
```

## PHP

Load PHP translations using `Alnaggar\Muhawil\Loaders\PhpFileLoader` class.

Dump PHP translations using `Alnaggar\Muhawil\Dumpers\PhpFileDumper` class.

## PO

Load PO translations using `Alnaggar\Muhawil\Loaders\PoFileLoader` class.

Dump PO translations using `Alnaggar\Muhawil\Dumpers\PoFileDumper` class, with an optional `metadata` argument to be included in the PO file header like `Language` and `Plural-Forms`.

```php
// Example of dumping translations into a PO file.

use Alnaggar\Muhawil\Dumpers\PoFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new PoFileDumper;

$dumper->dump($translations, 'path/to/translations/file.po', ['metadata' => [
    'Language' => 'ar'
    'Plural-Forms' => 'nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n % 100 >= 3 && n % 100 <= 10 ? 3 : n % 100 >= 11 ? 4 : 5;'
]]);
```

## XLIFF

Load XLIFF translations using `Alnaggar\Muhawil\Loaders\XliffFileLoader` class.

Dump XLIFF translations using `Alnaggar\Muhawil\Dumpers\XliffFileDumper` class, with two required arguments: `source_locale` and `target_locale`. There is also an optional argument `legacy`, which, if set to `true`, will dump the translation in XLIFF 1.2 format instead of the default 2.0 format.

```php
// Example of dumping translations into an XLIFF file.

use Alnaggar\Muhawil\Dumpers\XliffFileDumper;

$translations = [
    'welcome' => 'Welcome to our website!',
    'farewell' => 'Wishing you success on your new journey. Farewell!'
];

$dumper = new XliffFileDumper;

$dumper->dump($translations, 'path/to/translations/file.xliff', [
  'source_locale' => 'en', 
  'target_locale' => 'ar', 
  'legacy' => true
]);
```

> [!NOTE]
> If `legacy` is set to `false` (the default), there is an additional optional argument `file_id` which can be used as the `id` attribute value on the `file` node in the XLIFF 2.0 version.

## YAML

Load YAML translations using `Alnaggar\Muhawil\Loaders\YamlFileLoader` class.

Dump YAML translations using `Alnaggar\Muhawil\Dumpers\YamlFileDumper` class, with an optional `dry` argument that determines and generates anchors and aliases for similar **mappings**.

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

$dumper->dump($translations, 'path/to/translations/file.yaml', ['dry' => true]);
```

Both the YAML loader and the dumper support only simple YAML structures, which include mappings, nested mappings, and scalar values. All keys and scalar values may be double-quoted, single-quoted, or unquoted without allowing special YAML characters.

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
