<?php

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Locale;

use JsonException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\TranslationException;
use Resursbank\Ecom\Exception\Validation\IllegalTypeException;
use Resursbank\Ecom\Lib\Cache\Redis;
use Resursbank\Ecom\Lib\Locale\Language;
use Resursbank\Ecom\Lib\Locale\Phrase;
use Resursbank\Ecom\Lib\Locale\Translator;
use Resursbank\Ecom\Lib\Log\LoggerInterface;

/**
 * Test that phrases can be translated.
 */
class TranslatorTest extends TestCase
{
    /**
     * @throws ConfigException
     */
    protected function setUp(): void
    {
        $this->setupConfig();
        Config::getCache()->clear(key: Translator::getCacheKey());

        parent::setUp();
    }

    private function setupConfig(Language $locale = Language::en): void
    {
        Config::setup(
            logger: $this->createMock(
                originalClassName: LoggerInterface::class
            ),
            language: $locale,
            cache: new Redis(host: $_ENV['REDIS_HOST'])
        );
    }

    /**
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslationWorks(): void
    {
        $result = Translator::translate(phraseId: 'read-more');
        $this->assertSame(expected: 'Read More', actual: $result);

        // Test translating into swedish.
        $this->setupConfig(locale: Language::sv);
        $result = Translator::translate(phraseId: 'read-more');
        $this->assertSame(expected: 'Läs Mer', actual: $result);
    }

    /**
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws FilesystemException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateThrowsWhenPhraseIdDoesNotExists(): void
    {
        $this->expectException(exception: TranslationException::class);
        Translator::translate(phraseId: 'read');
    }

    /**
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testDecodeDataThrowsIfDataIsFaulty(): void
    {
        $this->expectException(exception: JsonException::class);
        Translator::decodeData(data: 'not-there');
    }

    /**
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateLoadsDataFromFile(): void
    {
        $cachedData = Config::getCache()->read(
            key: Translator::getCacheKey()
        );

        $translatedData = Translator::translate(phraseId: 'read-more');

        $this->assertNull(actual: $cachedData);
        $this->assertNotEmpty(actual: $translatedData);
    }

    /**
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     * @throws ConfigException
     */
    public function testTranslateLoadsDataFromCache(): void
    {
        $phraseId = 'read-more';
        $oldCache = Config::getCache()->read(
            key: Translator::getCacheKey()
        );
        $translatedString = Translator::translate(phraseId: $phraseId);
        $newCache = Config::getCache()->read(
            key: Translator::getCacheKey()
        );

        $this->assertNotNull(actual: $newCache);

        $decodedCache = Translator::decodeData(data: $newCache);
        $result = null;

        /** @var Phrase $item */
        foreach ($decodedCache->toArray() as $item) {
            if ($item->id !== $phraseId) {
                continue;
            }

            /** @var string $result */
            $result = $item->translation->{Config::getLanguage()->value};
        }

        $this->assertNull(actual: $oldCache);
        $this->assertSame(expected: $translatedString, actual: $result);
    }

    /**
     * Verify that translating from alternate translation file works
     *
     * @throws ConfigException
     * @throws FilesystemException
     * @throws IllegalTypeException
     * @throws JsonException
     * @throws ReflectionException
     * @throws TranslationException
     */
    public function testTranslateFromAlternateTranslationFile(): void
    {
        $source = __DIR__ . '/../../../Data/Translator/alternate.json';

        $this->assertEquals(
            expected: 'This is a test string',
            actual: Translator::translate(
                phraseId: 'test-string',
                translationFile: $source
            ),
            message: 'Translated string does not match expected output'
        );
    }
}
