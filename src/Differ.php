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

use InvalidArgumentException;
use SimpleXMLElement;

/**
 * Diff two files
 */
class Differ {

	private function parseFiles( SimpleXMLElement $xml ) {
		$files = [];
		$commonPath = null;
		foreach ( $xml->project->children() as $node ) {
			if ( $node->getName() !== 'file' ) {
				continue;
			}
			$coveredLines = 0;
			$totalLines = 0;
			foreach ( $node->children() as $child ) {
				if ( $child->getName() !== 'line' ) {
					continue;
				}
				$totalLines++;
				if ( (int)$child['count'] ) {
					// If count > 0 then it's covered
					$coveredLines++;
				}
			}
			$path = (string)$node['name'];
			if ( $totalLines === 0 ) {
				// Don't ever divide by 0
				$files[$path] = 0;
			} else {
				$files[$path] = $coveredLines / $totalLines * 100;
			}
			if ( $commonPath === null ) {
				$commonPath = $path;
			} else {
				while ( strpos( $path, $commonPath ) === false ) {
					$commonPath = dirname( $commonPath ) . '/';
				}
			}
		}

		// Now strip common path from everything...
		$sanePathFiles = [];
		foreach ( $files as $path => $info ) {
			$newPath = str_replace( $commonPath, '', $path );
			$sanePathFiles[$newPath] = $info;
		}

		return $sanePathFiles;
	}

	/**
	 * @param string $old path to old XML file
	 * @param string $new path to new XML file
	 *
	 * @return Diff
	 * @throws InvalidArgumentException if old or new don't exist
	 */
	public function diff( $old, $new ) {
		if ( !file_exists( $old ) ) {
			throw new InvalidArgumentException( "$old doesn't exist" );
		}
		if ( !file_exists( $new ) ) {
			throw new InvalidArgumentException( "$new doesn't exist" );
		}
		$oldXml = new SimpleXMLElement( file_get_contents( $old ) );
		$oldFiles = $this->parseFiles( $oldXml );
		$newXml = new SimpleXMLElement( file_get_contents( $new ) );
		$newFiles = $this->parseFiles( $newXml );

		return new Diff( $oldFiles, $newFiles );
	}

}
