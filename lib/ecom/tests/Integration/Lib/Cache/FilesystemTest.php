<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

/** @noinspection PhpMultipleClassDeclarationsInspection */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Cache;

use Exception;
use JsonException;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\ValidationException;
use Resursbank\Ecom\Lib\Cache\AbstractCache;
use Resursbank\Ecom\Lib\Cache\Filesystem;
use stdClass;

/**
 * This class will test Filesystem cache methods.
 */
class FilesystemTest extends TestCase
{
    /**
     * Base path of the directories and files these tests will create.
     */
    private const BASE_PATH = '/tmp/resursbank-test';

    /**
     * Whether test is running on pipeline server.
     */
    private bool $isPipeline = false;

    /**
     * Unique filesystem path for each test method (reset between tests).
     */
    private string $path;

    /**
     * Unique FileSystem instance for each test method (reset between tests).
     */
    private Filesystem $fileSystem;

    /**
     * Unique cache key to be utilised in various tests (resets between tests).
     */
    private string $key;

    /**
     * Unique filesystem path to expected cache file (resets between tests).
     *
     * NOTE: Should be $this->path/$this->key
     */
    private string $file;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->isPipeline = (bool) $_ENV['IS_PIPELINE'];

        // Create directory where all other directories / files will be created
        // during our tests, to avoid bloating /tmp.
        if (!is_dir(filename: self::BASE_PATH)) {
            mkdir(
                directory: self::BASE_PATH,
                permissions: 0755,
                recursive: true
            );
        }

        $this->path = $this->getPath();
        $this->fileSystem = $this->getFilesystem(path: $this->path);
        $this->key = $this->getKey();
        $this->file = "$this->path/$this->key.cache";

        Config::setup(cache: $this->fileSystem);

        parent::setUp();
    }

    /**
     * Tests are marked with this value if running from Bitbucket Pipelines.
     */
    protected function isPipeline(): bool
    {
        return $this->isPipeline;
    }

    /**
     * Create new Filesystem instance.
     */
    private function getFilesystem(string $path): Filesystem
    {
        return new Filesystem(path: $path);
    }

    /**
     * Generate unique path name, to ensure various tests which create files and
     * directories won't interfere with each other.
     *
     * @throws Exception
     */
    private function getPath(): string
    {
        return
            self::BASE_PATH .
            '/ecom-' .
            random_int(min: 0, max: 99999) .
            time() .
            random_int(min: 0, max: 99999)
        ;
    }

    /**
     * @throws Exception
     */
    private function getKey(): string
    {
        // NOTE: Simply using time() is unsafe, tests run too quickly.
        return AbstractCache::getKey(
            key: 'fs-cache-' . random_int(min: 0, max: 999999999) . time()
        );
    }

    /**
     * Assert that write() creates a writable cache directory if none exist.
     *
     * @throws Exception
     */
    public function testWriteCreatesWritableDir(): void
    {
        $this->assertDirectoryDoesNotExist(directory: $this->path);

        $this->fileSystem->write(key: $this->key, data: 'something', ttl: 0);

        $this->assertDirectoryExists(directory: $this->path);
        $this->assertDirectoryIsWritable(directory: $this->path);
    }

    /**
     * Asserts FilesystemException occur from write() when a file exists in the
     * place of the intended cache directory.
     *
     * @throws Exception
     */
    public function testWriteThrowsOnExistingFileAtPath(): void
    {
        touch(filename: $this->path);

        $this->assertFileExists(filename: $this->path);
        $this->expectException(exception: FilesystemException::class);

        $this->fileSystem->write(key: $this->key, data: 'my data set?', ttl: 0);
    }

    /**
     * Assert FilesystemException occurs from write() if the existing cache
     * directory isn't writable.
     *
     * @throws Exception
     */
    public function tesWriteThrowsWhenCacheDirNotWritable(): void
    {
        mkdir(directory: $this->path, permissions: 0500, recursive: true);

        $this->assertDirectoryExists(directory: $this->path);
        $this->assertDirectoryIsNotWritable(directory: $this->path);
        $this->expectException(exception: FilesystemException::class);

        $this->fileSystem->write(
            key: $this->key,
            data: 'Epic data set!',
            ttl: 0
        );
    }

    /**
     * Asserts that write() method accepts existing writable cache directory.
     *
     * @throws Exception
     */
    public function testWriteAcceptsExistingCacheDir(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);

        $this->assertDirectoryExists(directory: $this->path);

        $this->fileSystem->write(key: $this->key, data: '', ttl: 99);

        $this->assertFileExists(filename: $this->file);
    }

    /**
     * Assert that method write() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws Exception
     */
    public function testWriteThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->write(key: 'YAd4!', data: 'Flask', ttl: 777);
    }

    /**
     * Assert ValidationException occurs when calling write() with an empty key.
     *
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->write(key: '', data: 'NoWorries', ttl: 8723847);
    }

    /**
     * Assert that method write() creates the cache directory.
     *
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteCreatesDirectory(): void
    {
        $this->assertDirectoryDoesNotExist(directory: $this->path);

        $this->fileSystem->write(
            key: $this->key,
            data: 'Some cool data set',
            ttl: 0
        );

        $this->assertDirectoryExists(directory: $this->path);
    }

    /**
     * Assert that when we call the method write() it will generate a cache file
     * if none already exist.
     *
     * @throws Exception
     */
    public function testWriteCreatesFile(): void
    {
        $this->assertFileDoesNotExist(filename: $this->file);

        $this->fileSystem->write(key: $this->key, data: 'nada', ttl: 0);

        $this->assertFileExists(filename: $this->file);
    }

    /**
     * Assert that the method write() will accept an existing file (meaning it
     * will not attempt to create a file if the file already exists).
     *
     * @throws Exception
     */
    public function testWriteAcceptsExistingFile(): void
    {
        mkdir(directory: $this->path, permissions: 0755);
        touch(filename: $this->file);

        $this->assertFileExists(filename: $this->file);

        $this->fileSystem->write(key: $this->key, data: 'some data', ttl: 99);

        $this->assertFileExists(filename: $this->file);
    }

    /**
     * Assert that the method write() will throw an instance of
     * FilesystemException with the message "$file is not writable." if the
     * existing cache file isn't writable.
     *
     * @throws Exception
     */
    public function testWriteThrowsIfCacheFileIsNotWritable(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(
                message: 'Pipeline runs as root, privileges breaks this tests.'
            );
        }

        mkdir(directory: $this->path, permissions: 0755);
        touch(filename: $this->file);
        chmod(filename: $this->file, permissions: 0500);

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsNotWritable(file: $this->file);
        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: "$this->file is not writable.");

        $this->fileSystem->write(
            key: $this->key,
            data: 'Calm fort condor in de sun ~',
            ttl: 1233
        );
    }

    /**
     * Assert that the method write() will throw an instance of
     * FilesystemException with the message "$file is not a file." if a
     * directory allocates the cache file location.
     *
     * @throws FilesystemException
     * @throws ValidationException
     * @throws JsonException
     * @throws Exception
     */
    public function testWriteThrowsWithExistingDirectory(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(
                message: 'Pipeline runs as root, privileges breaks this tests.'
            );
        }

        mkdir(directory: $this->path, permissions: 0700);
        mkdir(directory: $this->file, permissions: 0500);

        $this->assertDirectoryExists(directory: $this->file);
        $this->assertDirectoryIsNotWritable(directory: $this->file);
        $this->expectException(exception: FilesystemException::class);
        $this->expectExceptionMessage(message: "$this->file is not a file.");

        $this->fileSystem->write(
            key: $this->key,
            data: json_encode(
                value: ['Cannon', 4, '{bb}'],
                flags: JSON_THROW_ON_ERROR
            ),
            ttl: 971367
        );
    }

    /**
     * Assert that the method write() creates a file with contents.
     *
     * @throws FilesystemException
     * @throws ValidationException
     * @throws Exception
     */
    public function testWriteCreatesNoneEmptyFile(): void
    {
        $this->fileSystem->write(key: $this->key, data: 'Empty', ttl: 55);

        $this->assertFileExists(filename: $this->file);
        $this->assertNotEmpty(
            actual: file_get_contents(filename: $this->file)
        );
    }

    /**
     * Assert that method read() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws Exception
     */
    public function testReadThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->read(key: 'EpicStuff_!');
    }

    /**
     * Assert ValidationException occurs when calling read() with an empty key.
     *
     * @throws Exception
     */
    public function testReadThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->read(key: '');
    }

    /**
     * Assert that method read() will return NULL if there is no cache file
     * matching the supplied key.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadWithoutCacheFileReturnsNull(): void
    {
        $this->assertNull(
            actual: $this->fileSystem->read(key: $this->getKey())
        );
    }

    /**
     * Assert that method read() will return NULL if there is a directory in the
     * place of the intended cache file.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadWithAllocatedCacheFileReturnsNull(): void
    {
        mkdir(directory: $this->file, permissions: 0755, recursive: true);

        $this->assertDirectoryExists(directory: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert that method read() will return NULL if the cache file isn't
     * readable.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadWithUnreadableCacheFileReturnsNull(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(
                message: 'Pipeline runs as root, privileges breaks this tests.'
            );
        }

        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        touch(filename: $this->file);
        chmod(filename: $this->file, permissions: 0000);

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsNotReadable(file: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert method read() will convert JSON data in file to Entry object.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadConvertsJsonToEntry(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: '{ "data": "Test data", "ttl": ' . 21000 . ', "createdAt": ' . time() . ' }'
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertSame(
            expected: 'Test data',
            actual: $this->fileSystem->read(key: $this->key)
        );
    }

    /**
     * Assert method read() will return NULL if the cache file isn't properly
     * formatted ("ttl|data").
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithoutTtl(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: '{ "data": "Test data", "createdAt": ' . time() . ' }'
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert method read() will return NULL if the cache file is properly
     * formatted ("ttl|data") but the specified TTL is "0".
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithZeroTtl(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: '{ "data": "Test data", "ttl": ' . 0 . ', "createdAt": ' . time() . ' }'
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert method read() will return NULL if cache file ttl isn't an integer.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithInvalidTtl(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: '{ "data": "Test data", "ttl": "100.345", "createdAt": ' . time() . ' }'
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert that method read() will return NULL if data is an empty string.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithEmptyData(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: '{ "data": "", "ttl": ' . 10040 . ', "createdAt": ' . time() . ' }'
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert method read() will return the data if the cache file is properly
     * formatted ("ttl|data").
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsData(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(
            filename: $this->file,
            data: $this->fileSystem->encodeEntry(data: 'This data', ttl: 99999)
        );

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertSame(
            expected: 'This data',
            actual: $this->fileSystem->read(key: $this->key)
        );
    }

    /**
     * Assert that method read() will return a serialized object.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsSerializedObject(): void
    {
        $obj = new stdClass();
        $obj->mhm = 'asd';
        $data = $this->fileSystem->encodeEntry(
            data: serialize(value: $obj),
            ttl: 99999
        );

        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(filename: $this->file, data: $data);

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertStringEqualsFile(
            expectedFile: $this->file,
            actualString: $data
        );

        $serialized = $this->fileSystem->read(key: $this->key);

        $this->assertIsString(actual: $serialized);
        $this->assertEquals(
            expected: $obj,
            actual: unserialize(data: $serialized)
        );
    }

    /**
     * Assert that method read() will return NULL if the cache has expired.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithExpiredTtl(): void
    {
        $data = $this->fileSystem->encodeEntry(data: 'Is data', ttl: -99999);

        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(filename: $this->file, data: $data);

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertStringEqualsFile(
            expectedFile: $this->file,
            actualString: $data
        );
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert that method read() will return NULL if the file is empty.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testReadReturnsNullWithEmptyFile(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        file_put_contents(filename: $this->file, data: '');

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsReadable(file: $this->file);
        $this->assertStringEqualsFile(
            expectedFile: $this->file,
            actualString: ''
        );
        $this->assertNull(actual: $this->fileSystem->read(key: $this->key));
    }

    /**
     * Assert read() returns NULL when cache is invalidated, and cached data
     * when not invalidated.
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function testCacheInvalidation(): void
    {
        $key1 = $this->getKey();
        $key2 = $this->getKey();
        $data1 = 'Hello!';
        $data2 = 'Big Bird';

        $this->fileSystem->write(key: $key1, data: $data1, ttl: 10000);
        $this->assertSame(
            expected: $data1,
            actual: $this->fileSystem->read(key: $key1)
        );

        $this->fileSystem->invalidate();
        $this->assertNull(
            actual: $this->fileSystem->read(key: $key1)
        );

        // createdAt of new Entry must be younger than invalidation marker.
        sleep(seconds: 1);

        $this->fileSystem->write(key: $key2, data: $data2, ttl: 10000);
        $this->assertSame(
            expected: $data2,
            actual: $this->fileSystem->read(key: $key2)
        );

        // Update invalidation marker, invalidating the entry we just created.
        sleep(seconds: 1);

        $this->fileSystem->invalidate();
        $this->assertNull(actual: $this->fileSystem->read(key: $key2));

        // Delete the invalidation marker, once again validating current cache.
        $this->fileSystem->clear(
            key: AbstractCache::getKey(
                key: AbstractCache::CACHE_INVALIDATION_KEY
            )
        );

        $this->assertSame(
            expected: $data1,
            actual: $this->fileSystem->read(key: $key1)
        );
        $this->assertSame(
            expected: $data2,
            actual: $this->fileSystem->read(key: $key2)
        );
    }

    /**
     * Assert that method clear() throws instance of ValidationException if our
     * key contains illegal characters.
     *
     * @throws Exception
     */
    public function testClearThrowsWithIllegalKeyCharacter(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->clear(key: 'SomeIllegal key');
    }

    /**
     * Assert ValidationException occurs when calling clear() with an empty key.
     *
     * @throws ValidationException
     * @throws FilesystemException
     */
    public function testClearThrowsWithEmptyKey(): void
    {
        $this->expectException(exception: ValidationException::class);
        $this->fileSystem->clear(key: '');
    }

    /**
     * Assert that clear() method will execute without error when cache file
     * does not exist (i.e. not cache = already cleared = do nothing).
     *
     * @throws ValidationException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testClearWithoutCacheFile(): void
    {
        $this->assertFileDoesNotExist(filename: $this->file);

        $this->fileSystem->clear(
            key: AbstractCache::getKey(key: 'some-bamboozle_not-exist')
        );
    }

    /**
     * Assert FilesystemException occurs if we attempt to clear a cache file
     * that is actually a directory.
     *
     * @throws ValidationException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testClearThrowsWithDirectory(): void
    {
        mkdir(directory: $this->file, permissions: 0755, recursive: true);

        $this->assertDirectoryExists(directory: $this->file);
        $this->expectException(exception: FilesystemException::class);

        $this->fileSystem->clear(key: $this->key);
    }

    /**
     * Assert FilesystemException occurs if the cache file isn't writable.
     *
     * @throws ValidationException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testClearThrowsWhenFileNotWritable(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(
                message: 'Pipeline runs as root, privileges breaks this tests.'
            );
        }

        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        touch(filename: $this->file);
        chmod(filename: $this->file, permissions: 0500);

        $this->assertFileExists(filename: $this->file);
        $this->assertFileIsNotWritable(file: $this->file);
        $this->expectException(exception: FilesystemException::class);

        $this->fileSystem->clear(key: $this->key);
    }

    /**
     * Assert that clear() method will delete file.
     *
     * @throws ValidationException
     * @throws FilesystemException
     * @throws Exception
     */
    public function testClearDeletesFile(): void
    {
        mkdir(directory: $this->path, permissions: 0755, recursive: true);
        touch(filename: $this->file);

        $this->assertFileExists(filename: $this->file);

        $this->fileSystem->clear(key: $this->key);

        $this->assertFileDoesNotExist(filename: $this->file);
    }
}
