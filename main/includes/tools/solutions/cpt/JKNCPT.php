<?php

/**
 * Provides very basic custom post functionality.
 *
 * How to use:
 *
 * 1. Override the abstract functions.
 *
 * 2. Consider the optional override functions. By default, there's an editing
 *      screen with just a title column. In particular, review the default
 *      register args function and see if you need to override anything.
 *
 * 3. Add the hooks somewhere in your module.
 *
 * 4. Use CPTExtender::posts and ::spage as needed.
 *
 * TODO Perhaps make date also a custom column?
 *
 */
abstract class JKNCPT {

    /*
     * =========================================================================
     * Override
     * =========================================================================
     */

    /**
     * Return the (pretty) name of this CPT.
     * If you don't override ID, it will also be converted to an ID.
     *
     * @return string
     */
    protected abstract static function name(): string;

    /**
     * Return the (pretty) plural name of this CPT.
     * By default returns name + s.
     *
     * @return string
     */
    protected static function plural(): string {
        return static::name() . 's';
    }

    /**
     * Return the id of this CPT.
     *
     * @return string
     */
    protected static function id(): string {
        return JKNStrings::sanitize(static::name());
    }

    /**
     * Return the description of this CPT.
     *
     * @return string
     */
    protected abstract static function description(): string;

    /**
     * Return true iff this post type uses an edit screen (post list page).
     *
     * @return bool
     */
    protected abstract static function has_edit_screen(): bool;

    /**
     * Return an integer for sorting posts of this type.
     * If this is irrelevant to your use case, implement and return null.
     *
     * @param WP_Post $p The post being saved.
     * @return int|null A number representing this post's order.
     */
    protected abstract static function derive_sort_num(WP_Post $p): ?int;

    /**
     * Return a string for the title for a post of this post type.
     * If this is irrelevant to your use case, implement and return null.
     *
     * @param WP_Post $p The post being saved.
     * @return string|null The title of the post.
     */
    protected abstract static function derive_title(WP_Post $p): ?string;


    /*
     * =========================================================================
     * Optionally override
     * =========================================================================
     */

    /*
     * =========================================================================
     * Set up
     * =========================================================================
     */

    /**
     * Return any special registry arguments.
     *
     * @return array
     */
    protected static function register_args(): array { return []; }

    /*
     * =========================================================================
     * Metabox
     * =========================================================================
     */

    /**
     * Return true iff this CPT has a metabox.
     *
     * @return bool
     */
    protected static function has_metabox(): bool { return false; }

    /**
     * Derive and return a metabox name.
     *
     * @return string
     */
    protected static function metabox_name(): string {
        $module = JKNAPI::module(static::class);
        return sprintf('%s — %s Tools', $module->name(), static::name());
    }

    /**
     * Return the HTML of a metabox if one is required.
     */
    static function render_metabox(): void { echo ''; }

    /**
     * Add a metabox for this post type to WP.
     * You shouldn't need to override this unless you need more than one or
     * to change the position or priority.
     */
    static function add_metabox(): void {

        // Register the box
        add_meta_box(static::metabox_id(), static::metabox_name(),
            [static::class, 'render_metabox'], static::qid(), 'side', 'low');
    }

    /*
     * =========================================================================
     * On-save functionality
     * =========================================================================
     */

    /**
     * Carry out any actions on the saving of this post.
     *
     * @param WP_Post $p The post being saved.
     */
    protected static function do_save_actions(WP_Post $p): void {
        static::save_sort_num($p);
        static::save_title($p);
    }

    /*
     * =========================================================================
     * On-trash functionality
     * =========================================================================
     */

    /**
     * Carry out any actions on the trashing of this post.
     *
     * @param WP_Post $p The post being saved.
     */
    protected static function do_trash_actions(WP_Post $p): void {}

    /*
     * =========================================================================
     * Edit screen columns
     * =========================================================================
     */

    /**
     * Return an array of custom columns: [id => name]
     * By default, just returns the title.
     *
     * @return string[]
     */
    protected static function get_columns(): array {
        return ['title' => 'Title'];
    }

    /**
     * Output the content of any columns supplied in get_columns.
     * Do this by identifying the column ID and echoing its content for the pid.
     *
     * N.B. This is added to WP's list of similar fill_columns functions.
     * As such, you are ONLY responsible for filling custom columns --
     * NOT standard checkbox or date or title, for example.
     *
     * @param string $col The name of the column whose content to echo.
     * @param string $pid The ID of the post being saved.
     */
    static function fill_columns(string $col, string $pid): void {}

    /**
     * Return an array of sortable columns: [id => meta_key]
     * The default sort key column will be added for you.
     * By default, just returns the title.
     *
     * @return string[]
     */
    protected static function get_sortable_columns(): array {
        return ['title' => 'title'];
    }


    /*
     * =========================================================================
     * Sorting
     * =========================================================================
     */

    /**
     * Return the default column name used for sorting, if you use any.
     *
     * @return string|null
     */
    protected static function default_sort_key(): ?string { return null; }

    /**
     * Alter the given WP main query to sort posts by a custom order.
     * Optionally override to change how sorting works.
     *
     * @param WP_Query $q
     */
    protected static function set_default_sort(WP_Query $q): void {

        // Short-circuit if we have no default key
        $default_key = static::default_sort_key();
        if (empty($default_key)) return;

        $orderby = strtolower($q->get('orderby'));
        if (empty($orderby) ||
            ((!empty($default_key) && $orderby == $default_key))) {

            $q->set('orderby', 'meta_value_num');
            $q->set('meta_key', static::sort_key());
        }
    }


    /*
     * =========================================================================
     * Should not need to override
     * =========================================================================
     */

    /*
     * =========================================================================
     * Set up
     * =========================================================================
     */

    /**
     * Add the hooks to set up this page.
     * Extend if you want to add any other hooks.
     *
     * @param int|null $spage_order The requested order for the CPT edit screen.
     */
    static function add_hooks(int $spage_order=null): void {

        // The settings page can be created straightaway
        if (!empty(static::has_edit_screen())) {
            static::create_spage($spage_order);
        }

        // Basic registration. Note that create_spage must come after register
        add_action('init', [static::class, 'register']);
        add_action('pre_get_posts', [static::class, 'intercept_default_sort']);
        add_action(static::save_hook(), [static::class, 'save']);
        add_action('wp_trash_post', [static::class, 'trash']);

        // Editing screen (only if a settings page is set)
        if (is_admin() && !empty(static::has_edit_screen())) {
            add_filter(sprintf('manage_%s_posts_columns', static::qid()),
                [static::class, 'change_columns']);
            add_action('manage_posts_custom_column',
                [static::class, 'fill_columns'], 10, 2);
            add_filter(sprintf('manage_edit-%s_sortable_columns', static::qid()),
                [static::class, 'register_sortable_columns']);

            // Metabox
            if (!empty(static::has_metabox())) {
                add_action('add_meta_boxes', [static::class, 'add_metabox']);
            }
        }
    }


    /*
     * =========================================================================
     * Identification
     * =========================================================================
     */

    /**
     * Return a fully qualified ID for this CPT: the ID qualified by the module.
     *
     * @return string
     */
    final static function qid(): string {
        $module = JKNAPI::module(static::class);
        $qid = $module->qualify(static::id());
        return static::truncate($qid);
    }

    /**
     * Return the given column name, qualified.
     *
     * @param string $col_id
     * @return string
     */
    protected final static function qcol(string $col_id): string {
        return sprintf('%s_%s', static::qid(), $col_id);
    }

    /**
     * Return the WP PostType object.
     *
     * @return WP_Post_Type
     */
    final static function wp_posttype(): WP_Post_Type {
        return get_post_type_object(static::qid());
    }

    /**
     * Return the sort meta_key.
     *
     * @return string
     */
    final static function sort_key(): string {
        $space = JKNAPI::space(static::class);
        return $space->qualify('sort_num');
    }


    /*
     * =========================================================================
     * Settings page
     * =========================================================================
     */

    /**
     * Return a unique settings page ID for this post type.
     *
     * @return string
     */
    final static function spage_id(): string {
        $sanitized = JKNStrings::sanitize(static::id());
        return sprintf('cpt_%s', $sanitized);
    }

    /**
     * Create and register a settings page for this CPT.
     *
     * @param int|null $order The requested order in the submenu.
     */
    private static function create_spage(int $order=null): void {
        if (is_null($order)) $order = JKNMenu::default_sub_order();

        $space = JKNAPI::space(static::class);
        $module = JKNAPI::module(static::class);
        $name = sprintf('%ss', static::name());
        $spid = static::spage_id();
        $qid = static::qid();
        $spage = new class($module, $spid, $qid, $name)
            extends JKNSettingsPageCPT {};

        $space->add_settings_page($spage, $order);
    }

    /**
     * Set the order of this post type's settings page in the submenu.
     *
     * @param int $order
     */
    final static function set_spage_order(int $order): void {
        static::spage()->set_order($order);
    }

    /**
     * Return the settings page (the edit screen/post listing).
     *
     * @return JKNSettingsPage
     */
    final static function spage(): JKNSettingsPage {
        return JKNAPI::settings_page(static::spage_id(), static::class);
    }


    /*
     * =========================================================================
     * Registration
     * =========================================================================
     */

    /**
     * Update the given args array with defaults for any missing.
     *
     * @param array $args
     * @return array
     */
    protected final static function
    merge_default_register_args(array $args): array {

        // If there is an edit screen for this CPT, derive the men slug
        $menu_slug = false;
        if (static::has_edit_screen()) {
            $space = JKNAPI::space(static::class);
            $menu_slug = $space->menu()->top_slug();
        }

        // Some handy extractions
        $name_sg = static::name();
        $name_pl = static::plural();
        $lcase_name_sg = strtolower($name_sg);
        $lcase_name_pl = strtolower($name_pl);

        // Subarray labels
        $default_labels = [
            'name' => sprintf('%s', $name_pl),
            'singular_name' => $name_sg,
            'add_new_item' => sprintf('Add new %s', $lcase_name_sg),
            'edit_item' => sprintf('Edit %s', $lcase_name_sg),
            'new_item' => sprintf('New %s', $lcase_name_sg),
            'view_item' => sprintf('View %s', $lcase_name_sg),
            'search_items' => sprintf('Search %s', $lcase_name_pl),
            'not_found' => sprintf('No %s found', $lcase_name_pl),
            'not_found_in_trash' => sprintf('No %s in trash', $lcase_name_pl),
            'all_items' => sprintf('%s', $name_pl),
            'archives' => sprintf('%s archives', $lcase_name_sg),
            'menu_name' => sprintf('All %s', $lcase_name_pl)
        ];

        // Subarray rewrite
        $default_rewrite = [
            'slug' => $lcase_name_sg,
            'with_front' => false,
            'feeds' => false,
            'pages' => false
        ];

        // Main array
        $default_args = [
            'description' => static::description(),
            'show_ui' => true,
            'show_in_menu' => $menu_slug,
            'show_in_admin_bar' => false,
            'supports' => false,
            'has_archive' => false,
            'map_meta_cap' => true,
            'capability_type' => $lcase_name_sg
        ];

        // Extract supplied subarrays to save them from the merge
        $supplied_labels = $args['labels'] ?? [];
        $supplied_rewrite = $args['rewrite'] ?? [];

        // Merge
        $args = array_merge($default_args, $args);
        $args['labels'] = array_merge($default_labels, $supplied_labels);

        if ($supplied_rewrite === false) {
            $args['rewrite'] = false;
        } else {
            $args['rewrite'] = array_merge($default_rewrite, $supplied_rewrite);
        }

        return $args;
    }

    /**
     * Register this post type with WP.
     *
     * N.B. wp_posts > post_type is by default a VARCHAR(20).
     * It is possible for values > 20 to be inserted using our ID method.
     * If any are found > 20, the column will be permanently expanded.
     */
    final static function register(): void {
        $args = static::register_args();
        $args = static::merge_default_register_args($args);
        register_post_type(static::qid(), $args);
        flush_rewrite_rules();
        static::add_capabilities();
    }

    /**
     * Return the qid, truncated if necessary.
     *
     * @param string $qid
     * @return string
     */
    final static function truncate(string $qid): string {
        global $wpdb;

        if (strlen($qid) > 20) {

            // Get existing post types
            $query_post_types =
                "SELECT DISTINCT post_type
			    	FROM wp_posts";
            $result = $wpdb->get_results($query_post_types);
            $post_types = array_map(function (stdClass $row): string { return $row->post_type; },
                $result);

            // Truncate
            $i = 20;
            $qid = trim(substr($qid, 0, $i));

            // Check for collisions
            while (in_array($qid, $post_types) && $i >= 5) {
                $qid = trim(substr($qid, 0, $i--));
            }

            // If too short and still colliding, add letters
            while (in_array($qid, $post_types) && $i <= 20) {
                $qid .= trim(chr(60 + ++$i));
            }

            // Chances of collision after that are low :)
        }

        return $qid;
    }


    /*
     * =========================================================================
     * Capabilities
     * =========================================================================
     */

    /**
     * Add the default capabilities for each role for this post type.
     */
    final private static function add_capabilities(): void {
        static::add_administrator_capabilities();
        static::add_editor_capabilities();
        static::add_author_capabilities();
        static::add_contributor_capabilities();
    }

    /**
     * Add custom capabilities for a given role for this post type.
     *
     * @param WP_Role $role The role to add the capabilities for.
     * @param array $stems The stems to add, e.g. 'delete' for 'delete_post'.
     */
    final static function add_custom_capabilities(WP_Role $role, array $stems): void {
        JKNPosts::add_post_type_capabilities($role, $stems, static::qid());
    }

    /**
     * Add the default administrator capabilities for this post type.
     */
    final private static function add_administrator_capabilities(): void {
        $role = get_role('administrator');
        $stems = [
            'create',
            'publish',
            'read',
            'read_private',
            'copy',
            'delete',
            'delete_others',
            'delete_private',
            'delete_published',
            'edit',
            'edit_others',
            'edit_published',
            'edit_private'
        ];

        static::add_custom_capabilities($role, $stems);
    }

    /**
     * Add the default editor capabilities for this post type.
     */

    final private static function add_editor_capabilities(): void {
        $role = get_role('editor');
        $stems = [
            'publish',
            'delete',
            'edit',
            'read',
            'delete_others',
            'delete_private',
            'delete_published',
            'edit_others',
            'edit_published',
            'edit_private',
        ];

        static::add_custom_capabilities($role, $stems);
    }

    /**
     * Add the default author capabilities for this post type.
     */

    final private static function add_author_capabilities(): void {
        $role = get_role('author');
        $stems = [
            'publish',
            'delete',
            'edit',
            'edit_published',
            'delete_published',
        ];

        static::add_custom_capabilities($role, $stems);
    }

    /**
     * Add the default contributor capabilities for this post type.
     */

    final private static function add_contributor_capabilities(): void {
        $role = get_role('contributor');
        $stems = [
            'delete',
            'edit',
        ];

        static::add_custom_capabilities($role, $stems);
    }


    /*
     * =========================================================================
     * Sorting
     * =========================================================================
     */

    /**
     * Intercept the given WP main query to set its default sort.
     *
     * @param WP_Query $q
     */
    final static function intercept_default_sort(WP_Query $q): void {

        // Short-circuit if this is not a main query or it's not our post type
        if(!$q->is_main_query() || static::qid() != $q->get('post_type')) {
            return;

            // Else alter the default sort
        } else {
            static::set_default_sort($q);
        }
    }


    /*
     * =========================================================================
     * Edit screen columns
     * =========================================================================
     */

    /**
     * Change the default columns for the editing screen.
     * By default, returns checkbox + get_columns.
     *
     * @param string[] $cols The existing columns [id => meta_key]
     * @return string[] The updated (or replaced) columns.
     */
    static function change_columns(array $cols): array {

        // Set the absolute basics
        $cols = ['cb' => '<input type="checkbox" />'];

        // Add custom columns if there are any
        $custom_cols = static::get_columns();
        if (!empty($custom_cols)) {
            foreach($custom_cols as $id => $name) {
                $cols[$id] = __($name, 'trans');
            }
        }

        return $cols;
    }

    /*
     * Return an array of sortable columns: [id => meta_key]
     * N.B. This adds the default sort key to any child-defined ones.
     *
     * @return string[] The columns that can be sorted [id => meta_key]
     */
    final static function register_sortable_columns(): array {
        $cols = static::get_sortable_columns();

        // If we have a default column, add that
        $default_sort_key = static::default_sort_key();
        if (!empty($default_sort_key)) {
            $cols[$default_sort_key] = $default_sort_key;
        }

        return $cols;
    }


    /*
     * =========================================================================
     * On-save functionality
     * =========================================================================
     */

    /**
     * Return the save hook for this post type, i.e. the hook that captures
     * when a post of this kind is created or updated.
     *
     * @return string
     */
    final static function save_hook(): string {
        return sprintf('save_post_%s', static::qid());
    }

    /**
     * Safely start the carrying out of actions on the saving of this post.
     *
     * @param string $pid The ID of the post being updated.
     */
    final static function save(string $pid): void {
        $p = get_post($pid);

        // Remove and re-add the action to avoid an infinite loop
        remove_action(static::save_hook(), [static::class, 'save']);
        static::do_save_actions($p);
        add_action(static::save_hook(), [static::class, 'save']);
    }

    /**
     * Save this post's custom title.
     *
     * @param WP_Post $p The post being updated.
     */
    final protected static function save_title(WP_Post $p): void {
        $title = static::derive_title($p);
        if (!is_null($title)) {
            wp_update_post(['ID' => $p->ID, 'post_title' => $title]);
        }
    }

    /**
     * Save this post's custom sort number.
     *
     * @param WP_Post $p The post being updated.
     */
    final protected static function save_sort_num(WP_post $p): void {
        $n = static::derive_sort_num($p);

        if (!is_null($n)) {
            if (!add_post_meta($p->ID, static::sort_key(), $n, $unique=true)) {
                update_post_meta($p->ID, static::sort_key(), $n);
            }
        }
    }


    /*
     * =========================================================================
     * On-trash functionality
     * =========================================================================
     */

    /**
     * Safely start the carrying out of actions on the trashing of this post.
     *
     * @param string $pid The ID of the post being updated.
     */
    final static function trash(string $pid): void {
        $p = get_post($pid);
        if ($p->post_type != static::qid()) return;

        // Remove and re-add the action to avoid an infinite loop
        remove_action('wp_trash_post', [static::class, 'trash']);
        static::do_trash_actions($p);
        add_action('wp_trash_post', [static::class, 'trash']);
    }


    /*
     * =========================================================================
     * Getting posts
     * =========================================================================
     */

    /**
     * Return the given args updated with the defaults for any missing.
     *
     * @param array $args
     * @return array
     */
    protected final static function
    merge_default_query_args(array $args): array {

        $default_args = [
            'post_type'         => static::qid(),
            'posts_per_page'    => -1,
            'post_status'       => 'publish'
        ];

        return array_merge($default_args, $args);
    }

    /**
     * Return the posts for this post type. By default, returns all published.
     *
     * @param array $query_args
     * @return WP_Post[]
     */
    final static function posts(array $query_args=[]): array {
        $query_args = static::merge_default_query_args($query_args);
        $query = new WP_Query($query_args);
        return $query->posts;
    }


    /*
     * =========================================================================
     * Metabox
     * =========================================================================
     */

    /**
     * Derive and return a metabox ID.
     *
     * @return string
     */
    final protected static function metabox_id(): string {
        return sprintf('%s_metabox', static::qid());
    }
}
