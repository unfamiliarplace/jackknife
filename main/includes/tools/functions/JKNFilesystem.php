<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNFilesystem {

	/**
	 * Recursively remove a given directory and all its files.
	 * WARNING: Very dangerous, though there's a catch to avoid removing root.
	 *
	 * @param string $dir The root directory to delete (including itself).
	 */
	static function rmdir_recursive(string $dir): void {

		// Do not remove the root directory o_O
		if (empty($dir) or '/' == $dir) return;

		// Determine the nodes
		$nodes = array_diff(scandir($dir), ['.', '..']);

		// If the node is a dir, recurse; if a file, unlink
		foreach ($nodes as $node) {
			if (is_dir("$dir/$node")) {
				self::rmdir_recursive("$dir/$node");
			} else {
				unlink("$dir/$node");
			}
		}

		// Finish by removing the root directory now that all nodes are clear
		rmdir($dir);
	}
}
