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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends Command {
	protected function configure() {
		$this->setName( 'diff' )
			->addArgument(
				'first', InputArgument::REQUIRED,
				'First clover.xml file'
			)->addArgument(
				'second', InputArgument::REQUIRED,
				'Second clover.xml file'
			);
	}

	private function format( $num ) {
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
		// Pad leading space to lign up with 100%,
		// and pick a color!
		return "<$color>" . str_pad(
			$pad,
			6,
			' ',
			STR_PAD_LEFT
		). "</$color>";
	}

	protected function execute( InputInterface $input, OutputInterface $output ) {
		$differ = new Differ();
		$diff = $differ->diff(
			$input->getArgument( 'first' ),
			$input->getArgument( 'second' )
		);
		$tableRows = [];
		foreach ( $diff->getMissingFromNew() as $fname => $val ) {
			$tableRows[] = [
				$fname,
				$this->format( $val ),
				0,
			];
			$output->writeln( "* $fname" );
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
			list( $old, $new ) = $info;
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

		// Sort all files in order!
		usort( $tableRows, function ( $a, $b ) {
			return strcmp( $a[0], $b[0] );
		} );

		$table = new Table( $output );
		$table->setHeaders( [
			'Filename', 'Old %', 'New %'
		] )->setRows( $tableRows )->render();

		return $lowered ? 1 : 0;
	}
}
