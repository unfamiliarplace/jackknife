<?php

/**
 * Provides functions for dealing with strings.
 */
final class JKNPosts {

	/**
	 * Return the id for the given post or post ID.
	 *
	 * @param string|WP_Post $post_or_pid A WP_Post or a (string) post ID.
	 * @return string
	 */
	static function to_pid($post_or_pid): string {
		if (gettype($post_or_pid) == 'string') return $post_or_pid;
		return $post_or_pid->ID;
	}

	/**
	 * Return the post for the given post or post ID.
	 *
	 * @param string|WP_Post $post_or_pid A WP_Post or a (string) post ID.
	 * @return string
	 */
	static function to_post($post_or_pid): string {
		if (gettype($post_or_pid) == 'string') return get_post($post_or_pid);
		return $post_or_pid;
	}

	/**
	 * Return an array of posts from the given array of post IDs.
	 *
	 * @param string[] $post_ids
	 * @return WP_Post[]
	 */
	static function to_posts(array $post_ids): array {
		$cb = function(string $pid): WP_Post { return get_post($pid); };
		return array_map($cb, $post_ids);
	}

	/**
	 * Return an array of post IDs from the given array of posts.
	 *
	 * @param WP_Post[] $posts
	 * @return string[]
	 */
	static function to_pids(array $posts): array {
		$cb = function(WP_Post $p): string { return $p->ID; };
		return array_map($cb, $posts);
	}
}
