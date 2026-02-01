<?php

declare(strict_types=1);

namespace WPZylos\Framework\Cli\Core;

use RuntimeException;

/**
 * File writer.
 *
 * Safe file writing with directory creation.
 *
 * @package WPZylos\Framework\Cli\Core
 */
class FileWriter
{
    /**
     * @var bool Whether to overwrite existing files
     */
    private bool $overwrite;

    /**
     * @var int Directory permissions
     */
    private int $dirPermissions;

    /**
     * Create writer.
     *
     * @param bool $overwrite Overwrite existing files
     * @param int $dirPermissions Permissions for created directories
     */
    public function __construct(bool $overwrite = false, int $dirPermissions = 0755)
    {
        $this->overwrite      = $overwrite;
        $this->dirPermissions = $dirPermissions;
    }

    /**
     * Write content to a file.
     *
     * @param string $path File path
     * @param string $content File content
     *
     * @return bool True on success
     * @throws RuntimeException If a file exists and overwrite is false
     */
    public function write(string $path, string $content): bool
    {
        if (! $this->overwrite && file_exists($path)) {
            throw new RuntimeException("File already exists: {$path}");
        }

        $directory = dirname($path);

        if (! is_dir($directory) && ! mkdir($directory, $this->dirPermissions, true) && ! is_dir($directory)) {
            throw new RuntimeException("Could not create directory: {$directory}");
        }

        $result = file_put_contents($path, $content);

        if ($result === false) {
            throw new RuntimeException("Could not write file: {$path}");
        }

        return true;
    }

    /**
     * Write if a file doesn't exist.
     *
     * @param string $path File path
     * @param string $content File content
     *
     * @return bool True if written, false if skipped
     */
    public function writeIfNotExists(string $path, string $content): bool
    {
        if (file_exists($path)) {
            return false;
        }

        return $this->write($path, $content);
    }

    /**
     * Set overwrite mode.
     *
     * @param bool $overwrite Overwrite mode
     *
     * @return static
     */
    public function setOverwrite(bool $overwrite): static
    {
        $this->overwrite = $overwrite;

        return $this;
    }
}
