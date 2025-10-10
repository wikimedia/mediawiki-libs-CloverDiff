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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Wikimedia\CloverDiff\Differ;
use Wikimedia\CloverDiff\DiffPrinter;
use Wikimedia\ScopedCallback;

/**
 * Integration tests
 */
class CloverDiffTest extends TestCase {

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

	public static function provideDiff(): array {
		return [
			[ 'of' ],
			[ 'linter' ],
			[ 'core' ],
		];
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

	/**
	 * @dataProvider provideDiff
	 */
	public function testDiff( $name ): void {
		$differ = new Differ();
		$dir = __DIR__ . '/data';
		$old = $this->decompress( "$dir/$name-old.xml" );
		$new = $this->decompress( "$dir/$name-new.xml" );
		$diff = $differ->diff( $old, $new );
		$dump = $this->decompress( "$dir/$name.dump" );
		$realDump = print_r( $diff, true );
		if ( $this->fix ) {
			file_put_contents( "$dump.gz", gzencode( $realDump ) );
		}
		$this->assertStringEqualsFile(
			$dump,
			$realDump
		);

		$output = new BufferedOutput();
		$printer = new DiffPrinter( $output );
		$printer->show( $diff );
		$console = $this->decompress( "$dir/$name-console.txt" );
		$buffer = $output->fetch();
		if ( $this->fix ) {
			file_put_contents( "$console.gz", gzencode( $buffer ) );
		}
		$this->assertStringEqualsFile(
			$console, $buffer
		);
	}
}
