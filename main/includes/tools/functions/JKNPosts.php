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
	 * @return WP_Post
	 */
	static function to_post($post_or_pid): WP_Post {
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

    /**
     * Add the given capability stems for the given post type.
     * Note: plurals are only handled using 's'.
     *
     * @param WP_Role $role The role to add the capabilities to.
     * @param string[] $stems An array of capability stems, e.g. 'delete' for 'delete_post'.
     * @param WP_Post_Type $ptype The post type to add capabilities for.
     */
    final static function _add_post_type_capabilities(WP_Role $role, array $stems, WP_Post_Type $ptype): void {
        $names = [$ptype->labels->name, $ptype->labels->singular_name];

        foreach($stems as $stem) {
            foreach($names as $name) {
                $cap = sprintf('%s_%s', $stem, strtolower($name));
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Add the given capability stems for the given post type by name.
     * Note: plurals are only handled using 's'.
     *
     * @param WP_Role $role The role to add the capabilities to.
     * @param string[] $stems An array of capability stems, e.g. 'delete' for 'delete_post'.
     * @param string $ptype_id The id of the post type to add capabilities for.
     */
    final static function add_post_type_capabilities(WP_Role $role, array $stems, string $ptype_id='post'): void {
        $ptype = get_post_type_object(strtolower($ptype_id));
        JKNPosts::_add_post_type_capabilities($role, $stems, $ptype);
    }

    /**
     * Add the given capability stems for the given post type.
     * Note: plurals are only handled using 's'.
     *
     * @param WP_Role $role The role to add the capabilities to.
     * @param string[] $stems An array of capability stems, e.g. 'delete' for 'delete_post'.
     * @param string $ptype_id The post type id to add capabilities for.
     */
    final static function add_post_type_capabilities_2(WP_Role $role, array $stems, string $ptype_id='post'): void {
        foreach($stems as $stem) {
            foreach(['s', ''] as $plural) {
                $cap = sprintf('%s_%s%s', $stem, strtolower($ptype_id), $plural);
                $role->add_cap($cap);
            }
        }
    }
}
