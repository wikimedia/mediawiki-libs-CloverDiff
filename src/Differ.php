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

/**
 * Diff two files
 */
class Differ {

	/**
	 * @param CloverXml|string|null $old path to old XML file
	 * @param CloverXml|string|null $new path to new XML file
	 *
	 * @return Diff
	 */
	public function diff( $old, $new ): Diff {
		if ( $old && !( $old instanceof CloverXml ) ) {
			$old = new CloverXml( $old );
		}
		if ( $new && !( $new instanceof CloverXml ) ) {
			$new = new CloverXml( $new );
		}
		if ( $old ) {
			$oldFiles = $old->getFiles();
		} else {
			$oldFiles = [];
		}
		if ( $new ) {
			$newFiles = $new->getFiles();
		} else {
			$newFiles = [];
		}

		return new Diff( $oldFiles, $newFiles );
	}

}
