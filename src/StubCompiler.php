<?php

declare(strict_types=1);

namespace WPZylos\Framework\Cli\Core;

/**
 * Stub compiler.
 *
 * Compiles stub templates with token replacement.
 *
 * @package WPZylos\Framework\Cli\Core
 */
class StubCompiler
{
    /**
     * @var string Stub directory
     */
    private string $stubPath;

    /**
     * @var array<string, string> Default replacements
     */
    private array $defaults = [];

    /**
     * Create compiler.
     *
     * @param string $stubPath Path to stubs directory
     */
    public function __construct(string $stubPath)
    {
        $this->stubPath = rtrim($stubPath, '/\\');
    }

    /**
     * Set default replacements.
     *
     * @param array<string, string> $defaults Default token values
     * @return static
     */
    public function setDefaults(array $defaults): static
    {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Compile a stub file.
     *
     * @param string $stubName Stub filename (without .stub)
     * @param array<string, string> $replacements Token => value pairs
     * @return string Compiled content
     */
    public function compile(string $stubName, array $replacements = []): string
    {
        $stubFile = $this->stubPath . '/' . $stubName . '.stub';

        if (!file_exists($stubFile)) {
            throw new \InvalidArgumentException("Stub not found: {$stubName}");
        }

        $content = file_get_contents($stubFile);

        if ($content === false) {
            throw new \RuntimeException("Could not read stub: {$stubName}");
        }

        // Merge defaults with provided replacements
        $allReplacements = array_merge($this->defaults, $replacements);

        // Replace tokens
        foreach ($allReplacements as $token => $value) {
            $content = str_replace('{{' . $token . '}}', $value, $content);
        }

        return $content;
    }

    /**
     * Compile with context-aware defaults.
     *
     * Adds common tokens from plugin context.
     *
     * @param string $stubName Stub filename
     * @param string $slug Plugin slug
     * @param string $prefix Plugin prefix
     * @param string $textDomain Text domain
     * @param string $namespace Plugin namespace
     * @param array<string, string> $extra Extra replacements
     * @return string Compiled content
     */
    public function compileForPlugin(
        string $stubName,
        string $slug,
        string $prefix,
        string $textDomain,
        string $namespace,
        array $extra = []
    ): string {
        return $this->compile($stubName, array_merge([
            'slug' => $slug,
            'prefix' => $prefix,
            'textDomain' => $textDomain,
            'namespace' => $namespace,
            'Slug' => str_replace('-', '', ucwords($slug, '-')),
            'PREFIX' => strtoupper($prefix),
        ], $extra));
    }

    /**
     * Get available stubs.
     *
     * @return string[] Stub names
     */
    public function getAvailable(): array
    {
        $files = glob($this->stubPath . '/*.stub');

        if ($files === false) {
            return [];
        }

        return array_map(fn($f) => basename($f, '.stub'), $files);
    }
}
