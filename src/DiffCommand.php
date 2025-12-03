<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 * @license GPL-3.0-or-later
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
