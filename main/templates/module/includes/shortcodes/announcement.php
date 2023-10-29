<?php

/**
 * Adds an announcement to the front page.
 */
class MJKAnnouncementShortcode {

    // CSS variables
    const cl_panel = 'mjk-fp-anc-panel';
    const cl_table = 'mjk-fp-anc-table';
    const cl_tr = 'mjk-fp-anc-tr';
    const cl_td = 'mjk-fp-anc-td';
    const cl_text = 'mjk-fp-anc-text';
	const cl_text_block = 'mjk-fp-anc-text-block';

	/**
	 * Add the shortcode.
	 */
    static function add_shortcode(): void {
        add_shortcode( 'mjk_announcement', [__CLASS__, 'render']);
    }


	/**
	 * Return the announcement content.
	 * @param string $type 'text', 'rt' or 'image'.
	 * @return string
	 */
	private static function get_content($type='text'): string {

        // Derive a getter and get the content
        $getter = sprintf('get_%s', $type);
        $content = self::$getter();

        // Wrap in an <a> tag if there's a link
        $link_block = MJKANC_ACF::get(MJKANC_ACF::link_block);
        if (!empty($link_block)) {
            $link = MJKANC_ACF::get(MJKANC_ACF::link);
            $content = sprintf('<a href="%s">%s</a>', $link, $content);
        }

        // Return
        return $content;
    }

	/**
	 * Return a plain text announcement.
	 *
	 * @return string
	 */
	private static function get_text(): string {
        $text = MJKANC_ACF::get(MJKANC_ACF::text);

        // Remove paragraphs in text if any
        $text = str_replace('<p>', '', str_replace('</p>', '', $text));

	    return sprintf('<div class="%s">%s</div>', self::cl_text_block, $text);
    }

	/**
	 * Return a rich text announcement.
	 *
	 * @return string
	 */
	private static function get_rt(): string {
        $rt = MJKANC_ACF::get(MJKANC_ACF::rt);
        return $rt;
    }

	/**
	 * Return an image announcement.
	 *
	 * @return string
	 */
	private static function get_image(): string {

        // The image is returned by ACF in the form of the thumbnail ID
        $img_id = MJKANC_ACF::get(MJKANC_ACF::image);
        return wp_get_attachment_image($img_id, $size='large');
    }

	/**
	 * Render the announcement HTML.
	 *
	 * @param mixed $atts The shortcode attributes. Unused.
	 * @return string
	 */
	static function render($atts): string {
        $html = '';

        // Check whether an announcement is being used
        if (!empty(MJKANC_ACF::get(MJKANC_ACF::use_anc))) {

            // Short-circuit if start/end times are specified
            // and now is not between them
            if (!empty(MJKANC_ACF::get(MJKANC_ACF::between))) {
                $from = MJKANC_ACF::get(MJKANC_ACF::from);
                $until = MJKANC_ACF::get(MJKANC_ACF::until);

                // Formalize as DateTimes...
                $from = JKNTime::dt($from);
                $until = JKNTime::dt($until);
                $now = JKNTime::dt('now');

                if (($now < $from) || ($now > $until)) return '';
            }

            // Otherwise keep going! Determine the type
            $type = MJKANC_ACF::get(MJKANC_ACF::type);

            // Get the content
            $content = self::get_content($type);

            // Open HTML structure
            $html .= sprintf('<div class="%s"><table class="%s"><tr class="%s"><td class="%s">',
                            self::cl_panel, self::cl_table, self::cl_tr, self::cl_td);

            // Add content HTML
            $html .= sprintf('<span class="%s">%s</span>', self::cl_text, $content);

            // Close HTML structure
            $html .= '</td></tr></table></div>';

            // Insert CSS
            $html = self::insert_style($type) . $content;
        }

        return $html;
    }

	/**
	 * Insert the formatted CSS directly.
	 *
	 * @param string $type 'text', 'rt' or 'image'
	 */
	static function insert_style(string $type): string {

        // Option-dependent styles
        $bg_colour      = MJKANC_ACF::get(MJKANC_ACF::bg_colour);
        $text_colour    = MJKANC_ACF::get(MJKANC_ACF::text_colour);
        $link_colour    = MJKANC_ACF::get(MJKANC_ACF::link_colour);

        // For images, remove a few customizations
        $padding        = ($type == 'image') ? '0'      : '10px';
        $font_size      = ($type == 'image') ? '0'      : '190';
        $bg_colour      = ($type == 'image') ? '#fff'   : $bg_colour;
        $text_colour    = ($type == 'image') ? '#000'   : $text_colour;
        $link_colour    = ($type == 'image') ? '#000'   : $link_colour;

        return JKNCSS::tag('
            /* Announcement */

            .'.self::cl_panel.' {
                border: none !important;
                background: '.$bg_colour.' !important;
                padding-top: '.$padding.' !important;
                padding-bottom: '.$padding.' !important;
                padding-left: '.$padding.';
                padding-right: '.$padding.';
                margin-top: 5px;
                margin-bottom: 15px;
            }

            .'.self::cl_table.' {
                width: 100% !important;
                margin-left: auto;
                margin-right: auto;
            }

            .'.self::cl_td.' {
                text-align: center;
                border: none;
                padding: 0;
            }

            /* Not sure why these would be different but okay */
            .'.self::cl_text.', .'.self::cl_text.' ul {
                font-size: '.$font_size.'% !important;
            }

            .'.self::cl_text.', .'.self::cl_text.' a {
                color: '.$text_colour.';
                text-transform: none !important;
                letter-spacing: .7px !important;
                text-align: inherit;
                font-family: "Nunito", Helvetica, Arial, sans-serif;
                font-weight: 600;
                line-height: 1.5;
                margin-bottom: 0;
            }
            
            .'.self::cl_text_block.' a {
                display: block;
            }

            .'.self::cl_text.' ul, .'.self::cl_text.' ul a {
                color: '.$link_colour.';
                font-family: "Roboto", sans-serif;
                list-style: none;
                line-height: 1.5;
                font-weight: 600;
                margin-bottom: 0px;
            }
        ');
    }
}
