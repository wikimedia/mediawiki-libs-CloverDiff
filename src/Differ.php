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
			if ( $node->getName() === 'package' ) {
				// If there's a common namespace I think, PHPUnit will
				// put everything under a package subnode.
				foreach ( $node->children() as $subNode ) {
					if ( $subNode->getName() === 'file' ) {
						$files += $this->handleFileNode( $subNode, $commonPath );
					}
				}
			} elseif ( $node->getName() === 'file' ) {
				$files += $this->handleFileNode( $node, $commonPath );
			}
			// TODO: else?
		}

		// Now strip common path from everything...
		$sanePathFiles = [];
		foreach ( $files as $path => $info ) {
			$newPath = str_replace( $commonPath, '', $path );
			$sanePathFiles[$newPath] = $info;
		}

		return $sanePathFiles;
	}

	private function handleFileNode( SimpleXMLElement $node, &$commonPath ) {
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
			$covered = 0;
		} else {
			$covered = $coveredLines / $totalLines * 100;
		}
		if ( $commonPath === null ) {
			$commonPath = $path;
		} else {
			while ( strpos( $path, $commonPath ) === false ) {
				$commonPath = dirname( $commonPath ) . '/';
			}
		}

		return [ $path => $covered ];

	}

	/**
	 * @param string|null $old path to old XML file
	 * @param string|null $new path to new XML file
	 *
	 * @return Diff
	 * @throws InvalidArgumentException if old or new don't exist
	 */
	public function diff( $old, $new ) {
		if ( $old && !file_exists( $old ) ) {
			throw new InvalidArgumentException( "$old doesn't exist" );
		}
		if ( $new && !file_exists( $new ) ) {
			throw new InvalidArgumentException( "$new doesn't exist" );
		}
		if ( $old ) {
			$oldXml = new SimpleXMLElement( file_get_contents( $old ) );
			$oldFiles = $this->parseFiles( $oldXml );
		} else {
			$oldFiles = [];
		}
		if ( $new ) {
			$newXml = new SimpleXMLElement( file_get_contents( $new ) );
			$newFiles = $this->parseFiles( $newXml );
		} else {
			$newFiles = [];
		}

		return new Diff( $oldFiles, $newFiles );
	}

}
