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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiffCommand extends Command {
	/**
	 * Set up our parameters/arguments
	 */
	protected function configure(): void {
		$this->setName( 'diff' )
			->addArgument(
				'first', InputArgument::REQUIRED,
				'First clover.xml file'
			)->addArgument(
				'second', InputArgument::REQUIRED,
				'Second clover.xml file'
			);
	}

	/**
	 * @param InputInterface $input stdin
	 * @param OutputInterface $output stdout
	 *
	 * @return int
	 */
	protected function execute( InputInterface $input, OutputInterface $output ): int {
		$differ = new Differ();
		$diff = $differ->diff(
			$input->getArgument( 'first' ),
			$input->getArgument( 'second' )
		);

		$printer = new DiffPrinter( $output );
		$lowered = $printer->show( $diff );

		return $lowered ? 1 : 0;
	}
}
