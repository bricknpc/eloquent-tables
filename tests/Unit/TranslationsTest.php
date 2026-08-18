<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Guards against translation drift: every string the package passes through the translator must have an entry in
 * every language file we ship.
 *
 * @internal
 */
#[CoversNothing]
class TranslationsTest extends TestCase
{
    #[DataProvider('languageFileProvider')]
    public function test_every_language_file_is_valid_json(string $file): void
    {
        /** @var string $contents */
        $contents = file_get_contents($file);

        $this->assertIsArray(json_decode($contents, true), sprintf('%s is not valid JSON.', basename($file)));
    }

    #[DataProvider('languageFileProvider')]
    public function test_every_translatable_string_has_a_translation(string $file): void
    {
        $translations = self::translations($file);

        foreach (self::translatableStrings() as $key => $source) {
            $this->assertArrayHasKey(
                $key,
                $translations,
                sprintf('%s is missing a translation for "%s", used in %s.', basename($file), $key, $source),
            );
        }
    }

    #[DataProvider('languageFileProvider')]
    public function test_no_translation_is_left_empty(string $file): void
    {
        foreach (self::translations($file) as $key => $translation) {
            $this->assertNotSame(
                '',
                trim($translation),
                sprintf('%s has an empty translation for "%s".', basename($file), $key),
            );
        }
    }

    /**
     * @return \Generator<string, array{string}>
     */
    public static function languageFileProvider(): \Generator
    {
        /** @var string[] $files */
        $files = glob(self::packagePath() . '/resources/lang/*.json') ?: [];

        foreach ($files as $file) {
            yield basename($file) => [$file];
        }
    }

    public function test_the_scanner_finds_the_strings_it_is_meant_to_guard(): void
    {
        $strings = self::translatableStrings();

        // A canary: if the scanner silently stops matching, the tests above would pass against an empty set.
        $this->assertGreaterThan(10, count($strings));
        $this->assertArrayHasKey('Close', $strings);
        $this->assertArrayHasKey('You are not authorized to view this table.', $strings);
    }

    /**
     * @return array<string, string>
     */
    private static function translations(string $file): array
    {
        /** @var string $contents */
        $contents = file_get_contents($file);

        /** @var array<string, string> $decoded */
        $decoded = json_decode($contents, true);

        return $decoded;
    }

    /**
     * Every string handed to the translator, mapped to the file it was found in.
     *
     * @return array<string, string>
     */
    private static function translatableStrings(): array
    {
        $strings = [];

        foreach (self::sourceFiles() as $file) {
            /** @var string $contents */
            $contents = file_get_contents($file);

            preg_match_all('/(?:__|trans->get)\(\s*\'((?:[^\'\\\]|\\\.)*)\'/', $contents, $matches);

            foreach ($matches[1] as $match) {
                $strings[stripslashes($match)] ??= str_replace(self::packagePath() . '/', '', $file);
            }
        }

        return $strings;
    }

    /**
     * @return \Generator<string>
     */
    private static function sourceFiles(): \Generator
    {
        foreach (['/resources/views', '/src'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(self::packagePath() . $directory, \FilesystemIterator::SKIP_DOTS),
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                yield $file->getPathname();
            }
        }
    }

    private static function packagePath(): string
    {
        return \dirname(__DIR__, 2);
    }
}
