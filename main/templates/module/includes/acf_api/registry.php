<?php
/**
 * Creates the ACF field groups for the announcement.
 */
final class MJKANC_ACF extends JKNACF {

	/**
	 * Define the type.
	 *
	 * @return string
	 */
	static function type(): string { return self::LOCATION_OPTIONS; }

    // Fields
    const use_anc = 'use_anc';
    const between = 'between';
    const from = 'from';
    const until = 'until';
    const type = 'type';
    const text = 'text';
    const rt = 'rt';
    const image = 'image';
    const bg_colour = 'bg_colour';
    const text_colour = 'text_colour';
    const link_block = 'link_block';
    const link = 'link';
    const link_colour = 'link_colour';

	/**
	 * Register the group and fields.
	 */
    static function add_filters(): void {

        // Group
        add_action('acf/init', [__CLASS__, 'add_group']);

        // Announcement
        add_action('acf/init', [__CLASS__, 'add_use_anc']);
        add_action('acf/init', [__CLASS__, 'add_between']);
        add_action('acf/init', [__CLASS__, 'add_from']);
        add_action('acf/init', [__CLASS__, 'add_until']);
        add_action('acf/init', [__CLASS__, 'add_type']);
        add_action('acf/init', [__CLASS__, 'add_text']);
        add_action('acf/init', [__CLASS__, 'add_rt']);
        add_action('acf/init', [__CLASS__, 'add_image']);
	    add_action('acf/init', [__CLASS__, 'add_link_block']);
	    add_action('acf/init', [__CLASS__, 'add_link']);
	    add_action('acf/init', [__CLASS__, 'add_link_colour']);
        add_action('acf/init', [__CLASS__, 'add_bg_colour']);
        add_action('acf/init', [__CLASS__, 'add_text_colour']);
    }

	/**
	 * Add the group.
	 */
    static function add_group(): void {
        self::add_acf_group([
            'title' => sprintf('%s Front Page Announcement',
                    JKNAPI::space()->name()),
            'location' => [
                [
                    [
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => JKNAPI::settings_page()->slug()
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
        ]);
    }

	/**
	 * Add the 'use_anc' field.
	 */
    static function add_use_anc(): void {
        self::add_acf_field(self::use_anc, [
            'label' => 'Announcement',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'ui_on_text' => '',
            'ui_off_text' => '',
            'type' => 'true_false',
            'instructions' => 'Shift content down to make room for an announcement?',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'between' field.
	 */
    static function add_between(): void {
        self::add_acf_field(self::between, [
            'label' => 'Provide start/end time',
            'default_value' => 0,
            'message' => '',
            'ui' => 1,
            'ui_on_text' => '',
            'ui_off_text' => '',
            'type' => 'true_false',
            'instructions' => 'Provide start/end times for the announcement?',
            'required' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'from' field.
	 */
    static function add_from(): void {
        self::add_acf_field(self::from, [
            'label' => 'Start announcement',
            'type' => 'date_time_picker',
            'instructions' => 'Show announcement from: ',
            'required' => 1,
            'display_format' => 'F j, Y g:i a',
            'return_format' => 'Y-m-d H:i:s',
            'first_day' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::between),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'until' field.
	 */
    static function add_until(): void {
        self::add_acf_field(self::until, [
            'label' => 'End announcement',
            'type' => 'date_time_picker',
            'instructions' => 'Show announcement until: ',
            'required' => 1,
            'display_format' => 'F j, Y g:i a',
            'return_format' => 'Y-m-d H:i:s',
            'first_day' => 0,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::between),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '25',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'type' field.
	 */
    static function add_type(): void {
        self::add_acf_field(self::type, [
            'label' => 'Announcement type',
            'layout' => 'horizontal',
            'choices' => [
                'text' => 'Plain text',
                'rt' => 'Rich text editor',
                'image' => 'Image'
            ],
            'default_value' => 'Plain text',
            'other_choice' => 0,
            'save_other_choice' => 0,
            'allow_null' => 0,
            'return_format' => 'value',
            'type' => 'radio',
            'instructions' => '',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '100',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'text' field.
	 */
	static function add_text(): void {
		self::add_acf_field(self::text, [
			'label' => 'Announcement text',
			'default_value' => '',
			'new_lines' => 'br',
			'maxlength' => 120,
			'placeholder' => '',
			'rows' => 2,
			'type' => 'textarea',
			'instructions' => 'Enter the announcement text. Up to 120 characters. Note that one line on desktop is around 60 characters.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::type),
						'operator' => '==',
						'value' => 'text',
					],
				],
			],
			'wrapper' => [
				'width' => '100',
				'class' => '',
				'id' => '',
			],
			'readonly' => 0,
			'disabled' => 0,
		]);
	}

	/**
	 * Add the 'rt' field.
	 */
	static function add_rt(): void {
		self::add_acf_field(self::rt, [
			'label' => 'Announcement rich text',
			'tabs' => 'all',
			'toolbar' => 'full',
			'media_upload' => 1,
			'default_value' => '',
			'delay' => 0,
			'type' => 'wysiwyg',
			'instructions' => '',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::type),
						'operator' => '==',
						'value' => 'rt',
					],
				],
			],
			'wrapper' => [
				'width' => '100',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'image' field.
	 */
	static function add_image(): void {
		self::add_acf_field(self::image, [
			'label' => 'Announcement image',
			'default_value' => '',
			'type' => 'image',
			'instructions' => '',
			'required' => 1,
			'return_format' => 'id',
			'preview_size' => 'thumbnail',
			'library' => 'all',
			'min_width' => '',
			'min_height' => '',
			'min_size' => '',
			'max_width' => '',
			'max_height' => '',
			'max_size' => '',
			'mime_types' => '',
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::type),
						'operator' => '==',
						'value' => 'image',
					],
				],
			],
			'wrapper' => [
				'width' => '100',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'link_block' field.
	 */
	static function add_link_block(): void {
		self::add_acf_field(self::link_block, [
			'label' => 'Link',
			'default_value' => 1,
			'message' => '',
			'ui' => 1,
			'ui_on_text' => '',
			'ui_off_text' => '',
			'type' => 'true_false',
			'instructions' => 'Make the announcement block a link?' .
				'<br>N.B. It\s not recommended to include links in a' .
				'  rich-text announcement if you make the whole block a link.',
			'required' => 0,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					]
				]
			],
			'wrapper' => [
				'width' => '20',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'link' field.
	 */
	static function add_link(): void {
		self::add_acf_field(self::link, [
			'label' => 'Announcement link',
			'default_value' => '',
			'maxlength' => '',
			'placeholder' => '',
			'prepend' => '',
			'append' => '',
			'type' => 'text',
			'instructions' => 'Use full URL (https://) for offsite links. For links relative to the home URL you can start with /.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::link_block),
						'operator' => '==',
						'value' => '1',
					]
				],
			],
			'wrapper' => [
				'width' => '50',
				'class' => '',
				'id' => '',
			],
			'readonly' => 0,
			'disabled' => 0,
		]);
	}

	/**
	 * Add the 'link_colour' field.
	 */
	static function add_link_colour(): void {
		self::add_acf_field(self::link_colour, [
			'label' => 'Announcement link colour',
			'default_value' => '#454545',
			'type' => 'color_picker',
			'instructions' => 'Choose a colour for the announcement link. The default is #454545.',
			'required' => 1,
			'conditional_logic' => [
				[
					[
						'field' => self::qualify_field(self::use_anc),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::link_block),
						'operator' => '==',
						'value' => '1',
					],
					[
						'field' => self::qualify_field(self::type),
						'operator' => '!=',
						'value' => 'image',
					]
				],
			],
			'wrapper' => [
				'width' => '30',
				'class' => '',
				'id' => '',
			],
		]);
	}

	/**
	 * Add the 'bg_colour' field.
	 */
    static function add_bg_colour(): void {
        self::add_acf_field(self::bg_colour, [
            'label' => 'Announcement background colour',
            'default_value' => '#cce8e5',
            'type' => 'color_picker',
            'instructions' => 'Choose a background colour for the announcement block. The default is #cce8e5.',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::type),
                        'operator' => '!=',
                        'value' => 'image',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '50',
                'class' => '',
                'id' => '',
            ],
        ]);
    }

	/**
	 * Add the 'text_colour' field.
	 */
    static function add_text_colour(): void {
        self::add_acf_field(self::text_colour, [
            'label' => 'Announcement text colour',
            'default_value' => '#222222',
            'type' => 'color_picker',
            'instructions' => 'Choose a colour for the announcement text. The default is #222222.',
            'required' => 1,
            'conditional_logic' => [
                [
                    [
                        'field' => self::qualify_field(self::use_anc),
                        'operator' => '==',
                        'value' => '1',
                    ],
                    [
                        'field' => self::qualify_field(self::type),
                        'operator' => '!=',
                        'value' => 'image',
                    ],
                ],
            ],
            'wrapper' => [
                'width' => '50',
                'class' => '',
                'id' => '',
            ],
        ]);
    }
}
