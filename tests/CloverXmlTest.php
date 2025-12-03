<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 * @license GPL-3.0-or-later
 */

namespace Wikimedia\CloverDiff\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Wikimedia\CloverDiff\CloverXml;
use Wikimedia\ScopedCallback;

class CloverXmlTest extends TestCase {

	/**
	 * @var bool
	 */
	private bool $fix = false;
	/** @var ScopedCallback[] */
	private $callbacks = [];

	public function setUp(): void {
		parent::setUp();
		$this->fix = (bool)getenv( 'FIX' );
	}

	private function decompress( string $name ): string {
		file_put_contents( $name, gzdecode( file_get_contents( "$name.gz" ) ) );
		$this->callbacks[] = new ScopedCallback( static function () use ( $name ) {
			if ( file_exists( $name ) ) {
				unlink( $name );
			}
		} );
		return $name;
	}

	public function testConstructor(): void {
		$this->expectException( InvalidArgumentException::class );
		new CloverXml( 'doesnotexist.txt' );
	}

	public static function provideGetFiles(): array {
		$dir = __DIR__ . '/data';
		return [
			[
				"$dir/linter-old.xml",
				CloverXml::PERCENTAGE,
			],
			[
				"$dir/linter-new.xml",
				CloverXml::PERCENTAGE,
			],
			[
				"$dir/linter-old.xml",
				CloverXml::LINES,
			],
			[
				"$dir/linter-new.xml",
				CloverXml::LINES,
			],
			[
				"$dir/linter-old.xml",
				CloverXml::METHODS,
			],
			[
				"$dir/linter-new.xml",
				CloverXml::METHODS,
			],
			[
				"$dir/linter-new.xml",
				CloverXml::METHODS,
				false,
			],
			[
				"$dir/core-old.xml",
				CloverXml::METHODS,
			],
			[
				"$dir/core-new.xml",
				CloverXml::METHODS,
			],
			[
				"$dir/core6.xml",
				CloverXml::METHODS,
			],
		];
	}

	/**
	 * @dataProvider provideGetFiles
	 */
	public function testGetFiles( $path, $mode, $rounding = true ): void {
		$path = $this->decompress( $path );
		$xml = new CloverXml( $path );
		$this->assertInstanceOf( CloverXml::class, $xml );
		$xml->setRounding( $rounding );
		$output = print_r( $xml->getFiles( $mode ), true );
		$extra = !$rounding ? '-exact' : '';
		$fname = $this->decompress( "$path.$mode$extra-expected" );
		if ( $this->fix ) {
			file_put_contents( "$fname.gz", gzencode( $output ) );
		}
		$this->assertStringEqualsFile(
			$fname,
			$output
		);
	}
}
