<?php

/**
 * A CacheDir represents a folder in the cache hierarchy.
 */
abstract class JKNCacheDir extends JKNCacheParentDir {

	private $parent;
	protected $args;

	/*
	 * ==========================================================================
	 * Override
	 * ==========================================================================
	 */

	/**
	 * Return a string identifier for this directory.
	 *
	 * @return string
	 */
	abstract function id(): string;


	/*
	 * ==========================================================================
	 * Do not override
	 * ==========================================================================
	 */

	/**
	 * Store internal references to the parent CacheDir and an optional object
	 * that can be used during the id method.
	 *
	 * @param JKNCacheParentDir $parent
	 * @param array $args
	 */
	final function __construct(JKNCacheParentDir $parent, array $args=[]) {
		$this->parent = $parent;
		$this->args = $args;
	}

	/**
	 * Create the parent directory.
	 */
	final function create_parent(): void { $this->parent->create(); }

	/**
	 * Return the external path (URL) to this directory.
	 *
	 * @return string
	 */
	final function path_ext(): string {
		return sprintf('%s/%s', $this->parent->path_ext(), $this->id());
	}

	/**
	 * Return the internal path to this directory.
	 *
	 * @return string
	 */
	final function path_int(): string {
		return sprintf('%s/%s', $this->parent->path_int(), $this->id());
	}
}
