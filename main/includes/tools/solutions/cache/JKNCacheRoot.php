<?php

/**
 * A CacheRoot is the root CacheParentDir, used for CacheDirs.
 */
final class JKNCacheRoot extends JKNCacheParentDir {

	private $space_id;
	private $module_id;

	/**
	 * Set the ID of this cache using the given identifier.
	 * The identifier is subject to the same rules as JKNAPI::file..
	 *
	 * @param null $ider A file path, object or class (if supplied).
	 */
	function __construct($ider=null) {
		$module = JKNAPI::module($ider, debug_backtrace());
		$this->space_id = $module->space()->id();
		$this->module_id = $module->id();
	}

	/**
	 * Create the parent directory.
	 */
	final function create_parent(): void {
		$space_dir = sprintf('%s/%s', JKN_CACHE_DIR_INT, $this->space_id);

		$old = umask(0);
		if (!is_dir(JKN_CACHE_ROOT_INT)) mkdir(JKN_CACHE_ROOT_INT, 0755);
		if (!is_dir(JKN_CACHE_DIR_INT)) mkdir(JKN_CACHE_DIR_INT, 0755);
		if (!is_dir($space_dir)) mkdir($space_dir, 0755);
		umask($old);
	}

	/**
	 * Return the external path (URL) of this cache.
	 *
	 * @return string
	 */
	function path_ext(): string {
		return sprintf('%s/%s/%s', JKN_CACHE_DIR_EXT,
			$this->space_id, $this->module_id);
	}

	/**
	 * Return the internal path of this cache.
	 *
	 * @return string
	 */
	function path_int(): string {
		return sprintf('%s/%s/%s', JKN_CACHE_DIR_INT,
			$this->space_id, $this->module_id);
	}
}
