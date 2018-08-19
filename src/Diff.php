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

/**
 * Represents changes to coverage
 */
class Diff {

	private $missingFromOld = [];
	private $missingFromNew = [];
	private $changed = [];

	private $oldFiles;
	private $newFiles;

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
				// Check equality after rounding, because we don't care
				// about changes past 2 decimals, its meaningless
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
	public function getMissingFromOld() {
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
	public function getMissingFromNew() {
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
	public function getChanged() {
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
