<?php

declare(strict_types=1);

namespace WPZylos\Framework\Cli\Core;

/**
 * Generator base class.
 *
 * Base for all file generators.
 *
 * @package WPZylos\Framework\Cli\Core
 */
abstract class Generator
{
    protected StubCompiler $compiler;
    protected FileWriter $writer;
    protected string $basePath;

    /**
     * Create generator.
     *
     * @param StubCompiler $compiler Stub compiler
     * @param FileWriter $writer File writer
     * @param string $basePath Plugin base path
     */
    public function __construct(StubCompiler $compiler, FileWriter $writer, string $basePath)
    {
        $this->compiler = $compiler;
        $this->writer = $writer;
        $this->basePath = rtrim($basePath, '/\\');
    }

    /**
     * Generate files.
     *
     * @param string $name Item name
     * @param array<string, mixed> $options Generation options
     * @return string[] Paths of generated files
     */
    abstract public function generate(string $name, array $options = []): array;

    /**
     * Get stub name for this generator.
     *
     * @return string
     */
    abstract protected function getStubName(): string;

    /**
     * Get output path for generated file.
     *
     * @param string $name Item name
     * @return string
     */
    abstract protected function getOutputPath(string $name): string;

    /**
     * Convert name to class name.
     *
     * @param string $name Input name
     * @return string
     */
    protected function toClassName(string $name): string
    {
        // my-thing or my_thing -> MyThing
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    /**
     * Convert name to variable name.
     *
     * @param string $name Input name
     * @return string
     */
    protected function toVariableName(string $name): string
    {
        return lcfirst($this->toClassName($name));
    }
}
