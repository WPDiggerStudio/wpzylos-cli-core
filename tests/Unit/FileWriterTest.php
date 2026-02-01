<?php

declare(strict_types=1);

namespace WPZylos\Framework\Cli\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Cli\Core\FileWriter;

/**
 * Tests for FileWriter class.
 */
class FileWriterTest extends TestCase
{
    private FileWriter $writer;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->writer = new FileWriter();
        $this->tempDir = sys_get_temp_dir() . '/wpzylos_test_' . uniqid();
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tempDir);
    }

    private function deleteDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->deleteDir($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testWriteCreatesFile(): void
    {
        $path = $this->tempDir . '/test.php';

        $this->writer->write($path, '<?php echo "test";');

        $this->assertFileExists($path);
    }

    public function testWriteCreatesNestedDirectories(): void
    {
        $path = $this->tempDir . '/deep/nested/dir/file.php';

        $this->writer->write($path, 'content');

        $this->assertFileExists($path);
    }

    public function testWriteContainsCorrectContent(): void
    {
        $path = $this->tempDir . '/content.php';
        $content = '<?php class Test {}';

        $this->writer->write($path, $content);

        $this->assertSame($content, file_get_contents($path));
    }

    public function testWriteIfNotExistsSkipsExisting(): void
    {
        $path = $this->tempDir . '/exists.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $result = $this->writer->writeIfNotExists($path, 'new content');

        $this->assertFalse($result);
        $this->assertSame('original', file_get_contents($path));
    }

    public function testWriteIfNotExistsCreatesNewFile(): void
    {
        $path = $this->tempDir . '/new.php';

        $result = $this->writer->writeIfNotExists($path, 'content');

        $this->assertTrue($result);
        $this->assertFileExists($path);
    }

    public function testWriteThrowsOnExistingFileWithoutOverwrite(): void
    {
        $this->expectException(\RuntimeException::class);

        $path = $this->tempDir . '/exists.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $this->writer->write($path, 'new content');
    }

    public function testWriteOverwritesWhenEnabled(): void
    {
        $path = $this->tempDir . '/overwrite.php';
        mkdir(dirname($path), 0755, true);
        file_put_contents($path, 'original');

        $writer = new FileWriter(overwrite: true);
        $writer->write($path, 'new content');

        $this->assertSame('new content', file_get_contents($path));
    }
}
