<?php

/**
 * Shared behaviour between JKNCacheDir and JKNCacheRoot.
 */
abstract class JKNCacheParentDir {

	/*
	 * =========================================================================
	 * Override
	 * =========================================================================
	 */

	/**
	 * Return the internal path of this directory.
	 *
	 * @return string
	 */
	abstract function path_int(): string;

	/**
	 * Return the external path (URL) of this directory.
	 *
	 * @return string
	 */
	abstract function path_ext(): string;

	/**
	 * Create the parent directory (if necessary) where this will live.
	 */
	abstract function create_parent(): void;


	/*
	 * =========================================================================
	 * Do not override
	 * =========================================================================
	 */

	/**
	 * Return true iff this directory exists.
	 *
	 * @return bool
	 */
	final function exists(): bool {
		return is_dir($this->path_int());
	}

	/**
	 * Delete this directory and its contents.
	 */
	final function purge(): void {
		if ($this->exists()) JKNFilesystem::rmdir_recursive($this->path_int());
	}

	/**
	 * Create this directory, if necessary.
	 */
	final function create(): void {

		// Recursive: create the parent if necessary first!
		$this->create_parent();

		if (!$this->exists()) {

			// Temporarily remove permissions masking during creation
			$old = umask(0);
			mkdir($this->path_int(), 0755);
			umask($old);
		}
	}
}
