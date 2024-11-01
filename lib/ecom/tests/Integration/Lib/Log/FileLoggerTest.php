<?php

/**
 * Copyright © Resurs Bank AB. All rights reserved.
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace Resursbank\EcomTest\Integration\Lib\Log;

use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use Resursbank\Ecom\Config;
use Resursbank\Ecom\Exception\ConfigException;
use Resursbank\Ecom\Exception\FilesystemException;
use Resursbank\Ecom\Exception\Validation\EmptyValueException;
use Resursbank\Ecom\Exception\Validation\FormatException;
use Resursbank\Ecom\Lib\Log\FileLogger;
use Resursbank\Ecom\Lib\Log\LogLevel;
use Throwable;

use function get_class;
use function is_array;

/**
 * Verifies that the FileLogger class works as intended.
 */
final class FileLoggerTest extends TestCase
{
    private const BASE_PATH = '/tmp';
    private const PATH_PREFIX = 'phpunit_FileLoggerTest';
    private const LOG_FILENAME = 'ecom.log';

    private bool $isPipeline = false;

    private string $path;
    private string $filename;
    private string $message;

    /**
     * Set up our required variables, directories and files.
     *
     * @throws Exception
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function setUp(): void
    {
        $this->isPipeline = (bool) $_ENV['IS_PIPELINE'];
        $this->message = 'This is a test message';

        if (!is_writable(filename: self::BASE_PATH)) {
            $this::markTestSkipped(
                message: self::BASE_PATH . ' directory is not writable, skipping test'
            );
        }

        $this->path = self::BASE_PATH . DIRECTORY_SEPARATOR . self::PATH_PREFIX . '_' .
            bin2hex(string: random_bytes(length: 8));
        $this->filename = $this->path . DIRECTORY_SEPARATOR . self::LOG_FILENAME;

        if (!mkdir(directory: $this->path)) {
            $this::markTestSkipped(message: 'Failed to create test directory');
        }

        if (!touch(filename: $this->filename)) {
            $this::markTestSkipped(message: 'Failed to touch log file');
        }

        Config::setup(
            logger: new FileLogger(path: $this->path),
            logLevel: LogLevel::DEBUG
        );
    }

    /**
     * Clean up the files and directories we created
     *
     * @noinspection PhpMissingParentCallCommonInspection
     */
    protected function tearDown(): void
    {
        if (
            file_exists(filename: $this->filename) &&
            !unlink(filename: $this->filename)
        ) {
            $this::markTestSkipped(
                message: 'Failed to delete the test log file'
            );
        }

        if (rmdir(directory: $this->path)) {
            return;
        }

        $this::markTestSkipped(message: 'Failed to delete test directory');
    }

    /**
     * Fetches the last line logged to specified file
     */
    private function getLastLineFromFile(string $filename): string
    {
        $lines = file(filename: $filename);

        /** @noinspection OffsetOperationsInspection */
        return is_array(value: $lines) && count($lines) >= 1
            ? $lines[count($lines) - 1]
            : '';
    }

    /**
     * Verify that a FilesystemException is thrown if we attempt to write to an unwritable file.
     *
     * @throws ConfigException
     */
    public function testLoggingFailure(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(message: 'This test cannot run as root.');
        }

        if (!chmod(filename: $this->filename, permissions: 0000)) {
            $this::markTestSkipped(message: 'Failed to set file permissions');
        }

        $this->expectException(exception: FilesystemException::class);

        Config::getLogger()->debug(message: $this->message);
    }

    /**
     * Verify that attempting to log a message with a log level below the one configured results in no message
     * being logged.
     *
     * @throws EmptyValueException
     * @throws FilesystemException
     * @throws FormatException
     * @throws ConfigException
     */
    public function testTooLowLogLevel(): void
    {
        $first = 'first';
        $second = 'second';

        Config::getLogger()->debug(message: $first);

        Config::setup(
            logger: new FileLogger(path: $this->path),
            logLevel: LogLevel::WARNING
        );

        Config::getLogger()->debug(message: $second);

        $logged = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this->assertEquals(
            expected: LogLevel::DEBUG->name . ': ' . $first . PHP_EOL,
            actual: $logged
        );
    }

    /**
     * Verify that debug logging works
     *
     * @throws ConfigException
     */
    public function testLogDebug(): void
    {
        Config::getLogger()->debug(message: $this->message);
        $loggedDebug = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this::assertSame(
            expected: LogLevel::DEBUG->name . ': ' . $this->message . PHP_EOL,
            actual: $loggedDebug
        );
    }

    /**
     * Verify that info logging works
     *
     * @throws ConfigException
     */
    public function testLogInfo(): void
    {
        Config::getLogger()->info(message: $this->message);
        $loggedInfo = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this::assertSame(
            expected: LogLevel::INFO->name . ': ' . $this->message . PHP_EOL,
            actual: $loggedInfo
        );
    }

    /**
     * Verify that warning logging works
     *
     * @throws ConfigException
     */
    public function testLogWarning(): void
    {
        Config::getLogger()->warning(message: $this->message);
        $loggedWarning = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this::assertSame(
            expected: LogLevel::WARNING->name . ': ' . $this->message . PHP_EOL,
            actual: $loggedWarning
        );
    }

    /**
     * Verify that error logging works
     *
     * @throws ConfigException
     */
    public function testLogError(): void
    {
        Config::getLogger()->error(message: $this->message);
        $loggedError = substr(
            string: $this->getLastLineFromFile(filename: $this->filename),
            offset: 26
        );
        $this::assertSame(
            expected: LogLevel::ERROR->name . ': ' . $this->message . PHP_EOL,
            actual: $loggedError
        );
    }

    /**
     * Verify that Exceptions get logged
     *
     * @throws ConfigException
     */
    public function testLogException(): void
    {
        $exception = new Exception();
        Config::getLogger()->debug(message: $exception);

        $content = file(filename: $this->filename);

        self::assertNotFalse(condition: $content);

        $numLines = count(value: $content);
        $lastLine = $this->getLastLineFromFile(filename: $this->filename);
        $expectedLastLine = '#' . ($numLines - 1) . ' {main}' . PHP_EOL;
        $this::assertSame(expected: $expectedLastLine, actual: $lastLine);
    }

    /**
     * Assert log() will log Error objects.
     *
     * @throws ConfigException
     */
    public function testDebugLogsError(): void
    {
        $error = new Error();
        Config::getLogger()->debug(message: $error);

        $content = file(filename: $this->filename);

        self::assertNotFalse(condition: $content);

        $numLines = count(value: $content);
        $lastLine = $this->getLastLineFromFile(filename: $this->filename);
        $expectedLastLine = '#' . ($numLines - 1) . ' {main}' . PHP_EOL;
        $this::assertSame(expected: $expectedLastLine, actual: $lastLine);
    }

    /**
     * Verify that creating a FileLogger with an empty path throws an
     * EmptyValueException
     */
    public function testValidatePathWithEmptyPath(): void
    {
        //$this->expectException(exception: EmptyValueException::class);
        $className = false;

        try {
            new FileLogger(path: '');
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(
            expected: EmptyValueException::class,
            actual: $className
        );
    }

    /**
     * Verify that creating a FileLogger with a path with leading whitespace throws a ValidationException
     */
    public function testValidatePathWithLeadingWhitespace(): void
    {
        $className = false;

        try {
            new FileLogger(path: '/tmp ');
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger with a path with trailing whitespace throws a ValidationException
     */
    public function testValidatePathWithTrailingWhitespace(): void
    {
        $className = false;

        try {
            new FileLogger(path: ' /tmp');
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that creating a FileLogger with a path with a trailing directory separator character
     * throws a ValidationException
     */
    public function testValidatePathWithTrailingSeparator(): void
    {
        $className = false;

        try {
            new FileLogger(path: '/tmp/');
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(expected: FormatException::class, actual: $className);
    }

    /**
     * Verify that attempting to create a FileLogger using a non-existent path causes a ValidationException
     *
     * @throws Exception
     */
    public function testValidatePathWhichDoesNotExist(): void
    {
        $fakePath = $this->path . bin2hex(string: random_bytes(length: 8));

        if (file_exists(filename: $fakePath)) {
            $this::markTestSkipped(
                message: "Path exists when it shouldn't, skipping"
            );
        }

        $className = false;

        try {
            new FileLogger(path: $fakePath);
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        $this::assertSame(
            expected: FilesystemException::class,
            actual: $className
        );
    }

    /**
     * Verify that creating a FileLogger using a path which is not a directory causes a ValidationException
     *
     * @throws Exception
     */
    public function testValidatePathWhichIsNotDirectory(): void
    {
        $filePath = $this->path . DIRECTORY_SEPARATOR . bin2hex(
            string: random_bytes(length: 8)
        );

        if (!touch(filename: $filePath)) {
            $this::markTestSkipped(message: 'Failed to create file for test');
        }

        $className = false;

        try {
            new FileLogger(path: $filePath);
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        unlink(filename: $filePath);

        $this::assertSame(
            expected: FilesystemException::class,
            actual: $className
        );
    }

    /**
     * Verify that creating a FileLogger using a path which is unwritable causes a ValidationException
     */
    public function testValidatePathWhichIsUnwritable(): void
    {
        if ($this->isPipeline) {
            $this->markTestSkipped(message: 'This test cannot run as root.');
        }

        if (!chmod(filename: $this->path, permissions: 0400)) {
            $this::markTestSkipped(
                message: 'Failed to change path directory permissions'
            );
        }

        $className = false;

        try {
            new FileLogger(path: $this->path);
        } catch (Throwable $e) {
            $className = get_class(object: $e);
        }

        if (!chmod(filename: $this->path, permissions: 0755)) {
            $this::markTestSkipped(
                message: 'Failed to change path directory permissions'
            );
        }

        $this::assertSame(
            expected: FilesystemException::class,
            actual: $className
        );
    }

    /**
     * Verify that creating a FileLogger using a valid path raises no exceptions
     */
    public function testValidatePathWithNoErrors(): void
    {
        $logger = false;

        try {
            $logger = new FileLogger(path: $this->path);
        } catch (Throwable) {
            $this::fail(message: 'Exception thrown with valid path');
        }

        $this::assertSame(
            expected: FileLogger::class,
            actual: get_class(object: $logger)
        );
    }
}
