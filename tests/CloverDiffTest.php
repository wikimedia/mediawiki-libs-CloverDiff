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

/**
 * Integration tests
 */
class CloverDiffTest extends TestCase {

	/**
	 * @var bool
	 */
	private $fix = false;

	public function setUp(): void {
		parent::setUp();
		$this->fix = (bool)getenv( 'FIX' );
	}

	public function provideDiff() {
		return [
			[ 'of' ],
			[ 'linter' ],
			[ 'core' ],
		];
	}

	/**
	 * @dataProvider provideDiff
	 */
	public function testDiff( $name ) {
		$differ = new Differ();
		$dir = __DIR__ . '/data';
		$old = "$dir/$name-old.xml";
		$new = "$dir/$name-new.xml";
		$diff = $differ->diff( $old, $new );
		$dump = "$dir/$name.dump";
		$realDump = print_r( $diff, true );
		if ( $this->fix ) {
			file_put_contents( $dump, $realDump );
		}
		$this->assertStringEqualsFile(
			$dump,
			$realDump
		);

		$output = new BufferedOutput();
		$printer = new DiffPrinter( $output );
		$printer->show( $diff );
		$console = "$dir/$name-console.txt";
		$buffer = $output->fetch();
		if ( $this->fix ) {
			file_put_contents( $console, $buffer );
		}
		$this->assertStringEqualsFile(
			$console, $buffer
		);
	}
}
