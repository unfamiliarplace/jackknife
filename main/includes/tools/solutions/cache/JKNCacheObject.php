<?php

/**
 * A CacheObject mediates between the cacher and a module object.
 *
 * TODO Use native WP Cache Object
 * https://developer.wordpress.org/reference/functions/wp_cache_set
 */
abstract class JKNCacheObject {

	private $cdir;
	protected $args;

	/*
	 * ==========================================================================
	 * Override
	 * ==========================================================================
	 */

	/**
	 * Return a string identifying the application object.
	 *
	 * @return string
	 */
	protected abstract function fname(): string;

	/**
	 * Return the callback that yields the content.
	 * Note that the callback must accept arguments, though it may null them.
	 *
	 * TODO Find a way to specify the return type (callable... but arrays...).
	 *
	 * @param array $args
	 * @return callable
	 */
	protected abstract function fetcher(array $args=[]): callable;


	/*
	 * ==========================================================================
	 * Optionally override
	 * ==========================================================================
	 */

	/**
	 * Returned the filtered content to write.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function filter_write(string $content): string {
		return $content;
	}

	/**
	 * Returned the filtered content that has been read.
	 *
	 * @param string $content
	 * @return string
	 */
	protected function filter_read(string $content): string { return $content; }

	/**
	 * Return the filtered URL to this object.
	 *
	 * @param string $url
	 * @return string
	 */
	protected function filter_url(string $url): string { return $url; }


	/*
	 * ==========================================================================
	 * Do not override
	 * ==========================================================================
	 */

	/**
	 * Store internal references to the cache directory and an optional object
	 * to be used in fname and/or fetcher.
	 *
	 * @param JKNCacheDir $cdir
	 * @param array $args
	 */
	final function __construct(JKNCacheDir $cdir, array $args=[]) {
		$this->cdir = $cdir;
		$this->args = $args;
	}

	/**
	 * Return the content to be written.
	 *
	 * @param array $args
	 * @param bool $filter Whether to filter it.
	 * @return string
	 */
	final function content(array $args=[], bool $filter=true): string {
		$cb = $this->fetcher();
		$content = $this->filter_write($cb([$args]));
		return ($filter) ? $this->filter_write($content) : $content;
	}

	/**
	 * Return the external path (URL) of the object.
	 *
	 * @param bool $filter Whether to filter it.
	 * @return string
	 */
	function url(bool $filter=true): string {
		$url = sprintf('%s/%s', $this->cdir->path_ext(), $this->fname());
		return ($filter) ? $this->filter_url($url) : $url;
	}

	/**
	 * Return the internal path of the object.
	 */
	function path(): string {
		return sprintf('%s/%s', $this->cdir->path_int(), $this->fname());
	}

	/**
	 * Return true iff the object has been cached.
	 */
	function exists(): bool {
		return file_exists($this->path());
	}

	/**
	 * Return the content of the cached object.
	 *
	 * @param bool $filter Whether to filter it.
	 * @return null|string
	 */
	function read(bool $filter=true): ?string {

		// Bail if file doesn't exist and we aren't allowed to write
		if (!$this->exists()) return null;

		// Otherwise return
		$content = file_get_contents($this->path());
		return ($filter) ? $this->filter_read($content) : $content;
	}

	/**
	 * Write the content of the cached object to the file, and also return it.
	 *
	 * @param array $args To pass to the content-fetcher function.
	 * @param bool $overwrite Whether to overwrite the content if it exists.
	 * @param bool $filter_read Whether to filter read content if it exists.
	 * @return string
	 */
	function write(array $args=[], bool $overwrite=false,
		bool $filter_read=true): string {

		if ($overwrite || !$this->exists()) {
			$this->cdir->create();
			$content = $this->content($args);
			file_put_contents($this->path(), $content);
			return $content;

		} else {
			return $this->read($filter_read);
		}
	}

	/**
	 * Delete this object if it exists.
	 */
	function purge(): void { if ($this->exists()) unlink($this->path()); }
}
