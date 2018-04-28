<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@member.fsf.org>
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

namespace Legoktm\CloverDiff;

class CloverXmlTest extends \PHPUnit\Framework\TestCase {

	public function provideGetFiles() {
		$dir = __DIR__ . '/data/';
		return [
			[
				"$dir/linter-old.xml",
				CloverXml::PERCENTAGE,
				[
					'ApiQueryLintErrors.php' => 0,
					'ApiQueryLinterStats.php' => 0,
					'ApiRecordLint.php' => 0,
					'CategoryManager.php' => 0,
					'Database.php' => 0,
					'Hooks.php' => 0,
					'LintError.php' => 0,
					'LintErrorsPager.php' => 0,
					'MissingCategoryException.php' => 0,
					'RecordLintJob.php' => 0,
					'SpecialLintErrors.php' => 94.28571428571428,
					'TotalsLookup.php' => 0,
				],
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
		];
	}

	/**
	 * @dataProvider provideGetFiles
	 */
	public function testGetFiles( $path, $mode ) {
		$xml = new CloverXml( $path );
		$this->assertInstanceOf( CloverXml::class, $xml );
		$output = print_r( $xml->getFiles( $mode ), true );
		$this->assertSame(
			file_get_contents( "$path.$mode-expected" ),
			$output
		);
	}
}
