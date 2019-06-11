<?php

/**
 * An overlay on Advanced Custom Fields Pro for adding groups programmatically. * 
 * Its purpose is to save having to think about qualifying groups and fields;
 * you never have to supply parent, key or name, and you can use shorter names
 * when getting fields in the module.
 * 
 * Ref: https://www.advancedcustomfields.com/resources/register-fields-via-php
 * 
 * How to use:
 * 
 * 1. Extend. If a module has one ACF class, don't override group or data_id.
 * 
 *      a. Override group when you want to provide more than one place where
 *          groups appear (e.g. an options page and a custom post type).
 * 
 *      b. Override data_id when you want fields to share the same group, but
 *          be distinct in the database.
 *
 *      c. Override type to set this as a post, user, or options page group.
 *          Then, you can call 'get' more easily (e.g. you don't have to supply
 *          that second 'options' parameter. The return value must be one of
 *          self::LOCATION_POST, _USER, _OPTIONS, or _OTHER.
 * 
 *      N.B. Purely for reference:
 *      A qualified group name looks like OPTBASE_MODULE_GROUP.
 *      A qualified field name looks like OPTBASE_MODULE_DATAID_FIELD.
 * 
 * 2. Determine your field names and create a method stub to add each one.
 * 
 * 3. Write add_filters, calling add_filter('acf/init', METHOD) for each field.
 *      Here is also where you would set up filters for methods that validate a
 *      field or dynamically populate it. Note that you can call self::add_tab
 *      to break up groups, supplying a name and (optionally) 'left' or 'top'.
 * 
 * 4. Write the add_group method, calling self::add_acf_group. This is mainly
 *      for setting the appearance and location -- e.g. post_type == 'my_cpt'
 *      or 'options_page' == JKNAPI::settings_page()->slug().
 * 
 * 5. Fill in the method stub for each field adder. Use add_acf_field if the
 *      field exists at group level or add_acf_sub_field if it belongs to
 *      another field (e.g. a repeater).
 * 
 *      N.B. When referring to another field in an argument array for a field,
 *      e.g. in conditional logic, supply qualify_field(name).
 * 
 * 6. Call add_filters somewhere in your module.
 * 
 * 7. Use MyACFExtender::get, ::sub, ::have_rows, and ::have_sub_rows to get
 *      auto-qualified names. Supply a post ID or user ID where required (post
 *      ID is filled in automatically if used within the loop). If you set
 *      type() correctly you don't need to include ACF's 'user_' or 'options'.
 *      
 *      N.B. This can only be done on or after the acf/init action, otherwise
 *      ACF breaks all over the site. There is a check in place to prevent this.
 *      If you desperately need to get a value before acf/init, you can always
 *      hackishly use get_option using MyACFExtender::qualify(name).
 *
 * TODO Create abstract function field_names returning an array of strings.
 *      Could use this to create anonymous getter functions that auto-qualify
 *      (e.g. supply 'use_anc' and the class would create a ::use_anc method).
 *      This could also auto-add filters and auto-clean on deactivation.
 */
abstract class JKNACF {

	/*
	 * =========================================================================
	 * Type constants
	 * =========================================================================
	 */

	const LOCATION_OTHER    = -1;
	const LOCATION_POST     =  0;
	const LOCATION_USER     =  1;
	const LOCATION_OPTIONS  =  2;

    
    /*
     * =========================================================================
     * Override
     * =========================================================================
     */
	/**
	 * Return a type for this ACF registry. Option-getting will be assisted
	 * based on this.
	 *
	 * Either JKNACF::LOCATION_POST, _USER, _OPTIONS, or _OTHER.
	 *
	 * @return string
	 */
	protected abstract static function type(): string;
    
    /*
     * Add the ACF filters for this group.
     */
    abstract static function add_filters(): void;
    
    /*
     * Register this group.
     */
    protected abstract static function add_group(): void;

    
    
    /*
     * =========================================================================
     * Optionally override
     * =========================================================================
     */
    
    /**
     * Return the base name for qualifying the group.
     *
     * @return string
     */
	protected static function group(): string { return 'g'; }
    
    /**
     * Return the base name for qualifying the data.
     * By default, this is the same as the group.
     *
     * @return string
     */
	protected static function data_id(): string { return static::group(); }
    
    
    /*
     * =========================================================================
     * Do not override
     * =========================================================================
     */

	/*
	 * =========================================================================
	 * Name qualification
	 * =========================================================================
	 */
    
    /**
     * Return the given name qualified by the module and our data ID base.
	 *
	 * @param string $name
	 * @return string
     */
    final static function qualify(string $name): string {
        $qname = sprintf('%s_%s', static::data_id(), $name);
        $module = JKNAPI::module(static::class);
        return $module->qualify($qname);
    }

    /**
     * Return a 'group_N' qualification.
     * ACF group keys must begin this way.
     *
     * @return string
     */
    protected final static function qualify_group(): string {
        $module = JKNAPI::module(static::class);
        $qgroup = $module->qualify(static::group());
        return sprintf('group_%s', $qgroup);
    }

	/**
	 * Return a 'field_X' qualification of the given name.
	 * ACF field keys must begin this way.
	 *
	 * @param string $name
	 * @return string
	 */
    final static function qualify_field(string $name): string {
        return sprintf('field_%s', static::qualify($name));
    }


	/*
	 * =========================================================================
	 * ACF registration
	 * =========================================================================
	 */
    
    /**
     * Add an ACF group with the given arguments.
     * The 'key' argument is automatically determined.
     * 
     * Wrapper for advancedcustomfields.com/resources/acf_add_local_field_group
     *
     * @param array $args The settings for the group.
     */
    protected final static function add_acf_group(array $args): void {
        $args['key'] = static::qualify_group();
        acf_add_local_field_group($args);
    }

	/**
	 * Add a group-level ACF field with the given name and arguments.
	 * The 'parent', 'key', and 'name' arguments are automatically determined.
	 *
	 * Wrapper for advancedcustomfields.com/resources/acf_add_local_field
	 *
	 * @param string $name The name of the field (unqualified at this point).
	 * @param array $args The settings for the field.
	 */
    protected final static function add_acf_field(string $name,
	        array $args): void {

        $args['parent'] = static::qualify_group();
        $args['key'] = static::qualify_field($name);
        $args['name'] = static::qualify($name);
        acf_add_local_field($args);
    }
    
    /**
     * Add a sub-level ACF field with the given parent name, name and arguments.
     * The 'parent', 'key', and 'name' arguments are automatically determined.
     * 
     * Wrapper for advancedcustomfields.com/resources/acf_add_local_field
     *
     * @param string $parent The name of the parent field (unqualified).
     * @param string $name The name of the field (unqualified).
     * @param array $args The settings for the group.
     */
    protected final static function add_acf_inner_field(string $parent,
            string $name, array $args): void {
        
        $args['parent'] = static::qualify_field($parent);
        $args['key'] = static::qualify_field($name);
        $args['name'] = $name;
        acf_add_local_field($args);
    }

	/**
	 * Add a tab with the given name.
	 *
	 * @param string $name
	 * @param string|null $placement 'left' or 'top'
	 */
    protected final static function add_tab(string $name,
	        string $placement='top'): void {

    	add_action('acf/init', function() use ($name): void {
		    $key = self::qualify_field('__tab_' . $name);
		    static::add_acf_field($key, [
			    'type'  => 'tab',
			    'label' => $name,
			    'endpoint' => 0
		    ]);
    	});
    }

    
    /*
     * =========================================================================
     * ACF data manipulation wrappers
     * 
     * N.B. The ACF fields these wrap have false, not dull, as the default
     * value for pid. We use null because you can't default bool for a string
     * after type hinting. If it ever produces problems, just change it here...
     * =========================================================================
     */

	/**
	 * Return the assisted context based on this ACF registry's type.
	 *
	 * @param string|null $context
	 * @return string|null
	 */
	final static function context(?string $context): ?string {

		$type = static::type();

		// Posts are already fine as post ID (or null for current post)
		if ($type == self::LOCATION_POST) {
			return $context;

		// User IDs need to be prefixed with 'user_' for ACF
		} elseif ($type == self::LOCATION_USER) {

			if (!JKNStrings::starts_with($context, 'user_')) {
				$context = 'user_' . $context;
			}

			return $context;

		// Options pages just say 'options'
		} elseif ($type == self::LOCATION_OPTIONS) {
			return 'options';

		// No particular way to handle anything else
		} else {
			return $context;
		}
	}

	/**
	 * Return the value of the given option name.
	 * $context is a post ID, user ID, or other string.
	 * Supply 'options' for $pid for an options page, 'user_' + ID for a user.
	 * If $format_value is true, apply ACF formatting to the data.
	 *
	 * Throw an error if this function is called too early.
	 *
	 * Wrapper for advancedcustomfields.com/resources/get_field
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param string|null $context The post ID or other context.
	 * @param bool $format_value
	 * @return mixed|null
	 */
    final static function get(string $name, string $context=null,
            bool $format_value=true) {

        self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

	    $context = static::context($context);
        return get_field(static::qualify_field($name), $context, $format_value);
    }

	/**
	 * Return the value of the given option name in a the_row() context.
	 * If $format_value is true, apply ACF formatting to the data.
	 *
	 * Throw an error if this function is called too early.
	 *
	 * Wrapper for advancedcustomfields.com/resources/get_sub_field
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param bool $format_value
	 * @return mixed|null
	 */
    final static function sub(string $name, bool $format_value=true) {
        self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

        return get_sub_field(static::qualify_field($name), $format_value);
    }

    /**
     * Return true iff the given option name has rows.
     * $context is a post ID, user ID, or other string.
     * Supply 'options' for $pid for an options page, 'user_' + ID for a user.
     * 
     * Throw an error if this function is called too early.
     * 
     * Wrapper for advancedcustomfields.com/resources/have_rows
     *
     * @param string $name The name of the field (unqualified).
     * @param string|null $context The post ID or other context.
     * @return bool
     */
    final static function have_rows(string $name, string $context=null): bool {
        self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

	    $context = static::context($context);
        return have_rows(static::qualify_field($name), $context);
    }

	/**
	 * Return true iff the given option name of an inner repeater field has rows.
	 *
	 * Throw an error if this function is called too early.
	 *
	 * Wrapper for advancedcustomfields.com/resources/have_rows
	 *
	 * @param string $name The name of the field (unqualified).
	 * @return bool
	 */
	final static function have_sub_rows(string $name): bool {
		self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));
		return have_rows(static::qualify_field($name));
	}

	/**
	 * Update the given option wth the given value.
	 * $context is a post ID, user ID, or other string.
	 * Supply 'options' for $pid for an options page, 'user_' + ID for a user.
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param mixed $value The value.
	 * @param string|null $context The post ID or other context.
	 * @return bool True iff the field was updated.
	 */
    final static function update(string $name, $value,
	        string $context=null): bool {

	    self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

	    $context = static::context($context);
	    return update_field(static::qualify_field($name), $value, $context);
    }

	/**
	 * Update the given subfield option wth the given value.
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param mixed $value The value.
	 * @return bool True iff the subfield was updated.
	 */
	final static function update_sub(string $name, $value): bool {
		self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

		return update_sub_field(static::qualify_field($name), $value);
	}

	/**
	 * Add the given complete row array to the given repeater field.
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param array $row An array of [name => value]. These names are unqualified.
	 * @param string|null $context The post ID or other context.
	 */
	final static function add_row(string $name, array $row,
			string $context=null): void {

		self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

		$context = static::context($context);

		$q_row = [];
		foreach($row as $sub_name => $data) {
			$q_row[self::qualify_field($sub_name)] = $data;
		}

		add_row(self::qualify_field($name), $q_row, $context);
	}

	/**
	 * Delete the row at the given index from the given repeater field.
	 *
	 * @param string $name The name of the field (unqualified).
	 * @param int $i The index of the row to delete.
	 * @param string|null $context The post ID or other context.
	 */
	final static function delete_row(string $name, int $i,
			string $context=null): void {

		self::verify_timing(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1));

		$context = static::context($context);
		delete_row(self::qualify_field($name), $i, $context);
	}


	/*
	 * =========================================================================
	 * Error-checking
	 * =========================================================================
	 */
    
    /**
     * Throw an error iff this function is called before acf/init is done.
     *
     * @param array $backtrace A debug_backtrace to identify the caller file.
	 * @throws Exception If the function is called too early.
     */
    protected final static function verify_timing(array $backtrace) {
        if (!empty(did_action('acf/init'))) return;

        $file = JKNAPI::file_from_backtrace($backtrace);
        $message = sprintf('An Advanced Custom Fields field getter was called'
                . ' before ACF initiated. Delay till after the acf/init hook.'
                . ' This took place in the following file:<br>%s', $file);
        
        throw new Exception($message);
    }


	/*
	 * =========================================================================
	 * $_POST data
	 *
	 * TODO This cannot yet be used for validating data & preventing insertion.
	 * =========================================================================
	 */

	/**
	 * Return the value of the given field from the $_POST array.
	 * This is useful when validating before saving a post; the two ACF hooks
	 * acf/pre_save_post and acf/save_post both take place AFTER WP save_post.
	 * Thus they cannot be used for e.g. saving a post title based on ACF data.
	 *
	 * The $_POST array will contain a subarray as follows:
	 *      ['acf' =>
	 *          ['field_XXX' => value],
	 *          ['field_YYY' => value]
	 *      }
	 *
	 * @param string $name The name of the field.
	 * @return mixed|null The value of the field.
	 */
	final static function get_posted(string $name) {
		if (!isset($_POST['acf'])) return null;
		$fields = $_POST['acf'];
		return $fields[static::qualify_field($name)];
	}

	/**
	 * Set the value of the given field in the $_POST array.
	 * This is useful when validating before saving a post; the two ACF hooks
	 * acf/pre_save_post and acf/save_post both take place AFTER WP save_post.
	 * Thus they cannot be used for e.g. saving a post title based on ACF data.
	 *
	 * The $_POST array will contain a subarray as follows:
	 *      ['acf' =>
	 *          ['field_XXX' => value],
	 *          ['field_YYY' => value]
	 *      }
	 *
	 * @param string $name The name of the field.
	 * @param mixed $value The value to set it to.
	 */
	final static function set_posted(string $name, $value): void {
		if (!isset($_POST['acf'])) return;
		$fields = $_POST['acf'];
		$fields[static::qualify_field($name)] = $value;
	}
}
