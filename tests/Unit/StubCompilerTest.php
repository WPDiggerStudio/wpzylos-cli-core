<?php

declare( strict_types=1 );

namespace WPZylos\Framework\Cli\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use WPZylos\Framework\Cli\Core\StubCompiler;

/**
 * Tests for StubCompiler class.
 */
class StubCompilerTest extends TestCase {
	private string $stubDir;

	protected function setUp(): void {
		$this->stubDir = sys_get_temp_dir() . '/wpzylos_stubs_' . uniqid();
		mkdir( $this->stubDir, 0755, true );
	}

	protected function tearDown(): void {
		$this->deleteDir( $this->stubDir );
	}

	private function deleteDir( string $dir ): void {
		if ( ! is_dir( $dir ) ) {
			return;
		}
		$items = array_diff( scandir( $dir ), [ '.', '..' ] );
		foreach ( $items as $item ) {
			$path = $dir . '/' . $item;
			is_dir( $path ) ? $this->deleteDir( $path ) : unlink( $path );
		}
		rmdir( $dir );
	}

	private function writeStub( string $name, string $content ): void {
		file_put_contents( $this->stubDir . '/' . $name . '.stub', $content );
	}

	public function testCompileReplacesSimplePlaceholder(): void {
		$this->writeStub( 'greeting', 'Hello {{name}}!' );
		$compiler = new StubCompiler( $this->stubDir );

		$result = $compiler->compile( 'greeting', [ 'name' => 'World' ] );

		$this->assertSame( 'Hello World!', $result );
	}

	public function testCompileReplacesMultiplePlaceholders(): void {
		$this->writeStub( 'class', 'class {{class}} extends {{parent}} {}' );
		$compiler = new StubCompiler( $this->stubDir );

		$result = $compiler->compile( 'class', [
			'class'  => 'MyController',
			'parent' => 'BaseController',
		] );

		$this->assertSame( 'class MyController extends BaseController {}', $result );
	}

	public function testCompileReplacesAllOccurrences(): void {
		$this->writeStub( 'repeat', '{{name}} {{name}} {{name}}' );
		$compiler = new StubCompiler( $this->stubDir );

		$result = $compiler->compile( 'repeat', [ 'name' => 'Test' ] );

		$this->assertSame( 'Test Test Test', $result );
	}

	public function testCompilePreservesUnmatchedPlaceholders(): void {
		$this->writeStub( 'partial', 'Hello {{name}}, {{unknown}}!' );
		$compiler = new StubCompiler( $this->stubDir );

		$result = $compiler->compile( 'partial', [ 'name' => 'World' ] );

		$this->assertStringContainsString( 'World', $result );
		$this->assertStringContainsString( '{{unknown}}', $result );
	}

	public function testCompileWithDefaults(): void {
		$this->writeStub( 'defaults', 'namespace {{namespace}}; class {{class}} {}' );
		$compiler = new StubCompiler( $this->stubDir );
		$compiler->setDefaults( [ 'namespace' => 'MyPlugin' ] );

		$result = $compiler->compile( 'defaults', [ 'class' => 'MyClass' ] );

		$this->assertSame( 'namespace MyPlugin; class MyClass {}', $result );
	}

	public function testCompileForPluginAddsContextTokens(): void {
		$this->writeStub( 'plugin', '{{slug}} {{prefix}} {{textDomain}} {{namespace}}' );
		$compiler = new StubCompiler( $this->stubDir );

		$result = $compiler->compileForPlugin(
			'plugin',
			'my-plugin',
			'myplugin_',
			'my-plugin',
			'MyPlugin'
		);

		$this->assertSame( 'my-plugin myplugin_ my-plugin MyPlugin', $result );
	}

	public function testGetAvailableReturnsStubNames(): void {
		$this->writeStub( 'controller', 'content' );
		$this->writeStub( 'model', 'content' );
		$compiler = new StubCompiler( $this->stubDir );

		$stubs = $compiler->getAvailable();

		$this->assertContains( 'controller', $stubs );
		$this->assertContains( 'model', $stubs );
	}

	public function testCompileThrowsForMissingStub(): void {
		$this->expectException( \InvalidArgumentException::class );
		$this->expectExceptionMessage( 'Stub not found: nonexistent' );

		$compiler = new StubCompiler( $this->stubDir );
		$compiler->compile( 'nonexistent' );
	}
}
