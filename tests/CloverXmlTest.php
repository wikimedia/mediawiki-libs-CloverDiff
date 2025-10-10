<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
