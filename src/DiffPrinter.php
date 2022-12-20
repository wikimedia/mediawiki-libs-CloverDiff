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

namespace Wikimedia\CloverDiff;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Print the result of a diff in a
 * cool table
 */
class DiffPrinter {

	/**
	 * @var OutputInterface
	 */
	private $output;

	/**
	 * @param OutputInterface $output stdout
	 */
	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Fancy coloring and padding for numbers
	 *
	 * @param int $num
	 *
	 * @return string
	 */
	private function format( $num ): string {
		if ( $num < 50 ) {
			$color = 'error';
		} elseif ( $num > 90 ) {
			$color = 'info';
		} else {
			$color = 'comment';
		}
		// Pad leading 0s
		$pad = str_pad(
			number_format( $num, 2 ),
			5,
			'0',
			STR_PAD_LEFT
		);
		// Pad leading space to line up with 100%,
		// and pick a color!
		return "<$color>" . str_pad(
				$pad,
				6,
				' ',
				STR_PAD_LEFT
			) . "</$color>";
	}

	/**
	 * @param Diff $diff Diff to print
	 *
	 * @return bool Whether any file had lower coverage afterwards
	 */
	public function show( Diff $diff ): bool {
		$tableRows = [];
		foreach ( $diff->getMissingFromNew() as $fname => $val ) {
			$tableRows[] = [
				$fname,
				$this->format( $val ),
				0,
			];
		}

		foreach ( $diff->getMissingFromOld() as $fname => $val ) {
			$tableRows[] = [
				$fname,
				0,
				$this->format( $val ),
			];
		}

		$lowered = false;
		foreach ( $diff->getChanged() as $fname => $info ) {
			[ $old, $new ] = $info;
			$tableRows[] = [
				$fname,
				$this->format( $old ),
				$this->format( $new ),
			];

			// Fail if any file has less coverage.
			if ( $new < $old ) {
				$lowered = true;
			}
		}

		if ( $tableRows ) {
			// Sort all files in order!
			usort( $tableRows, static function ( $a, $b ) {
				return strcmp( $a[0], $b[0] );
			} );

			$table = new Table( $this->output );
			$table->setHeaders( [
				'Filename', 'Old %', 'New %'
			] )->setRows( $tableRows )->render();
		} else {
			$this->output->writeln(
				'<info>No coverage changes found.</info>'
			);
		}

		return $lowered;
	}
}
