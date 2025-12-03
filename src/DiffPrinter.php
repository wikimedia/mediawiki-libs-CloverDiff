<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 * @license GPL-3.0-or-later
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
	private OutputInterface $output;

	/**
	 * @param OutputInterface $output stdout
	 */
	public function __construct( OutputInterface $output ) {
		$this->output = $output;
	}

	/**
	 * Fancy coloring and padding for numbers
	 *
	 * @param float $num
	 *
	 * @return string
	 */
	private function format( float $num ): string {
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
