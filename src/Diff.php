<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 * @license GPL-3.0-or-later
 */

namespace Wikimedia\CloverDiff;

/**
 * Represents changes to coverage
 */
class Diff {

	/**
	 * @var int[]|string[]
	 */
	private array $missingFromOld;

	/**
	 * @var int[]|string[]
	 */
	private array $missingFromNew;

	/**
	 * @var array
	 */
	private array $changed;

	/**
	 * @var array
	 */
	private array $oldFiles;

	/**
	 * @var array
	 */
	private array $newFiles;

	/**
	 * @param array $oldFiles Parsed clover.xml
	 * @param array $newFiles Parsed clover.xml
	 */
	public function __construct( array $oldFiles, array $newFiles ) {
		// Use array_filter to remove files that have 0 coverage, because
		// it's not useful to output a 0 -> 0 diff report
		$this->missingFromNew = array_diff(
			array_keys( array_filter( $oldFiles ) ),
			array_keys( $newFiles )
		);
		$this->missingFromOld = array_diff(
			array_keys( array_filter( $newFiles ) ),
			array_keys( $oldFiles )
		);

		$changed = [];
		foreach ( $oldFiles as $path => $oldInfo ) {
			if ( isset( $newFiles[$path] ) ) {
				$newInfo = $newFiles[$path];
				// Even though it's already been rounded, we want to round
				// here just in case the change pushed it over the threshold
				// for rounding
				if ( round( $oldInfo, 2 ) !== round( $newInfo, 2 ) ) {
					$changed[] = $path;
				}
			}
		}
		$this->changed = $changed;

		// @todo remove the files we don't care about anymore
		$this->oldFiles = $oldFiles;
		$this->newFiles = $newFiles;
	}

	/**
	 * Get files that are missing from the old XML file
	 *
	 * @return array
	 */
	public function getMissingFromOld(): array {
		$rows = [];
		foreach ( $this->missingFromOld as $fname ) {
			$rows[$fname] = $this->newFiles[$fname];
		}

		return $rows;
	}

	/**
	 * Get files that are missing from the new XML file
	 *
	 * @return array
	 */
	public function getMissingFromNew(): array {
		$rows = [];
		foreach ( $this->missingFromNew as $fname ) {
			$rows[$fname] = $this->oldFiles[$fname];
		}

		return $rows;
	}

	/**
	 * Get files that are in both, but have different values
	 *
	 * @return array
	 */
	public function getChanged(): array {
		$rows = [];
		foreach ( $this->changed as $fname ) {
			$rows[$fname] = [
				$this->oldFiles[$fname],
				$this->newFiles[$fname],
			];
		}

		return $rows;
	}
}
