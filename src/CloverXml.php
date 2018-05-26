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
 * Represents a clover.xml file
 */
class CloverXml {

	/**
	 * Count percentage covered
	 */
	const PERCENTAGE = 1;

	/**
	 * Return (un)covered lines
	 */
	const LINES = 2;

	/**
	 * Count coverage status of classes and functions
	 */
	const METHODS = 3;

	/**
	 * @var string
	 */
	private $fname;

	/**
	 * @var SimpleXMLElement
	 */
	private $xml;

	/**
	 * @param string $fname Filename
	 * @throws InvalidArgumentException
	 */
	public function __construct( $fname ) {
		if ( !file_exists( $fname ) ) {
			throw new InvalidArgumentException( "$fname doesn't exist" );
		}
		$this->fname = $fname;
		$this->xml = new SimpleXMLElement( file_get_contents( $fname ) );
	}

	/**
	 * @param int $mode
	 * @return array
	 */
	public function getFiles( $mode = self::PERCENTAGE ) {
		$files = [];
		$commonPath = null;
		foreach ( $this->xml->project->children() as $node ) {
			if ( $node->getName() === 'package' ) {
				// If there's a common namespace I think, PHPUnit will
				// put everything under a package subnode.
				foreach ( $node->children() as $subNode ) {
					if ( $subNode->getName() === 'file' ) {
						$files += $this->handleFileNode( $subNode, $commonPath, $mode );
					}
				}
			} elseif ( $node->getName() === 'file' ) {
				$files += $this->handleFileNode( $node, $commonPath, $mode );
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

	private function handleFileNode( SimpleXMLElement $node, &$commonPath, $mode ) {
		$coveredLines = 0;
		$totalLines = 0;
		$lines = [];
		$mStats = [];
		$mCovered = 0;
		$mTotal = 0;
		$class = null;
		$method = null;
		foreach ( $node->children() as $child ) {
			if ( $child->getName() === 'class' ) {
				$class = $child['name'];
				if ( isset( $child['namespace'] ) ) {
					$class = "{$child['namespace']}\\$class";
				}
				continue;
			} elseif ( $child->getName() !== 'line' ) {
				continue;
			}
			if ( $child['type'] == 'method' ) {
				if ( $method !== null ) {
					$mStats[$method] = $mCovered / $mTotal * 100;
					$mCovered = 0;
					$mTotal = 0;
				}
				$method = "$class::{$child['name']}";
			}
			$totalLines++;
			$mTotal++;
			$lineCovered = (int)$child['count'];
			if ( $lineCovered ) {
				// If count > 0 then it's covered
				$coveredLines++;
				$mCovered++;
			}
			$lines[(int)$child['num']] = $lineCovered;
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

		if ( $mode === self::LINES ) {
			$ret = $lines;
		} elseif ( $mode === self::METHODS ) {
			$ret = $mStats;
		} else {
			$ret = $covered;
		}
		return [ $path => $ret ];
	}

}
