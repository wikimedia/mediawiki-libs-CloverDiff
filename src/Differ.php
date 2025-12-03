<?php
/**
 * Copyright (C) 2018 Kunal Mehta <legoktm@debian.org>
 * @license GPL-3.0-or-later
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
