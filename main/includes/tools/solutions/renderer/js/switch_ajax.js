/*
 * =============================================================================
 * jQuery for switching the content on a switch page via AJAX.
 * =============================================================================
 */

$ = jQuery;

/**
 * Perform onload actions.
 */
function jkn_rdr_switch_onload() {
    $jkn_rdr_switch_history = [];
    jkn_rdr_bind_default_history();
    jkn_rdr_bind_select();
    jkn_rdr_bind_get();
}

/*
 * =============================================================================
 * PHP localization: JKNRendererSwitch
 *      JKNRendererSwitch.ajaxurl           // The Ajax post URL
 *      JKNRendererSwitch.switch_action     // The Ajax post action
 *      JKNRendererSwitch.get_key           // Url param key for opt prechoosing
 *      JKNRendererSwitch.opts_to_ids       // Table of values to div IDs
 *      JKNRendererSwitch.special_options   // Non-content "meta" options
 *      JKNRendererSwitch.id_select         // The id of the switcher
 *      JKNRendererSwitch.cl_select_dis     // The switcher disable class
 *      JKNRendererSwitch.cl_switch_div     // Class for all switch divs
 *      JKNRendererSwitch.cl_hide           // Class for hiding switch divs
 */


/*
 * =============================================================================
 * Binding
 * =============================================================================
 */

/**
 * Bind the content switcher to the select dropdown.
 */
function jkn_rdr_bind_select() {
    $select = $('#' + JKNRendererSwitch.id_select);
    $select.change(function () { jkn_rdr_switch_content(this.value); });
}

/**
 * Bind on load to put in the default history.
 */
function jkn_rdr_bind_default_history() {
    $(document).ready(jkn_rdr_default_history);
}

/**
 * Bind on load to use a get parameter to change the option, if supplied.
 */
function jkn_rdr_bind_get() {
    $(document).ready(jkn_rdr_read_get);
}


/*
 * =============================================================================
 * Default history & GET parameters
 * =============================================================================
 */

/**
 * Set the selecter to the default option and trigger a change.
 */
function jkn_rdr_default_history() {
    $options = JKNRendererSwitch.opts_to_ids;
    for (var $option in $options) {

        // Make sure to get all preloads
        $div_id = $options[$option];

        // Unknown if it's empty, the div id if it already has content
        $html = $('#' + $div_id).html();
        if (!$html || !$html.trim()) {
            $jkn_rdr_switch_history[$option] = 'unknown';
        } else {
            $jkn_rdr_switch_history[$option] = $div_id;
        }
    }
}

/**
 * Return the value of the given URL parameter.
 * Credit: jquerybyexample.net/2012/06/get-url-parameters-using-jquery.html
 */
function jkn_rdr_url_param($param) {
    var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    for (var i = 0; i < sURLVariables.length; i++) {
        var sParameterName = sURLVariables[i].split('=');
        if (sParameterName[0] === $param) {
            return sParameterName[1];
        }
    }
}

/**
 * Update the option based on the get parameter, if supplied.
 */
function jkn_rdr_read_get() {
    var $option = jkn_rdr_url_param(JKNRendererSwitch.get_key);
    if ($option) {
        $select = $('#' + JKNRendererSwitch.id_select);

        // Only change if we have such an option
        if ($select.has('[value="' + $option + '"]').length > 0) {
            $select.val($option);
            $select.change();
        }
    }
}


/*
 * =============================================================================
 * Specific AJAX behaviour
 * =============================================================================
 */

/**
 * React to a switch in the select field: post an AJAX query to switch content.
 */        
function jkn_rdr_switch_content($option) {

    // If history, insta-switch
    if ($jkn_rdr_switch_history[$option] !== 'unknown') {
        jkn_rdr_switch_on($jkn_rdr_switch_history[$option]);

    // Otherwise load
    } else {

        // Do the Ajax request
        jkn_rdr_switch_ajax_post(JKNRendererSwitch.switch_action, $option,
            function ($response) {

                // Set history to error
                $error = $response.indexOf('Fatal error') !== -1;
                if ($error || ($response == '0') || $response == '-1') {
                    $error = JKNRendererSwitch.special_options['error'];
                    $jkn_rdr_switch_history[$option] = $error;

                // Set history to the option's own div id and fill the div
                } else {
                    $div_id = JKNRendererSwitch.opts_to_ids[$option];
                    $jkn_rdr_switch_history[$option] = $div_id;

                    $('#' + $div_id).html($response);
                }
            }
        );
    }
}


/*
 * =============================================================================
 * General AJAX behaviour
 * =============================================================================
 */

/**
 * Carry out an AJAX post.
 */
function jkn_rdr_switch_ajax_post($action, $option, $fn_response) {
    jkn_rdr_switch_ajax_begin();
    $.ajax({
        url:  JKNRendererSwitch.ajaxurl,
        type: 'post',
        data: jkn_rdr_switch_get_post_array($action, $option),
        success: $fn_response
   }).done(jkn_rdr_switch_ajax_done).fail(jkn_rdr_switch_ajax_fail);
}

/**
 * Return the array to be posted to Ajax.
 */
function jkn_rdr_switch_get_post_array($action, $option) {
    return {
        'action': $action,
        'option': $option
    };
}

/**
 * Carry out actions at the beginning of an AJAX post.
 */
function jkn_rdr_switch_ajax_begin() {
    jkn_rdr_switch_disable_switch(true);
    jkn_rdr_switch_change_cursor('wait');
    jkn_rdr_switch_on(JKNRendererSwitch.special_options['loading']);
}

/**
 * Carry out actions at the finishing of an AJAX post.
 */
function jkn_rdr_switch_ajax_done() {
    jkn_rdr_switch_disable_switch(false);
    jkn_rdr_switch_change_cursor('');

    $selected = $('#' + JKNRendererSwitch.id_select).val();
    jkn_rdr_switch_on($jkn_rdr_switch_history[$selected]);
}

/**
 * Carry out actions at the failure of an AJAX post.
 */
function jkn_rdr_switch_ajax_fail() {

    // Set the current option to error in history
    $selected = $('#' + JKNRendererSwitch.id_select).val();
    $error = JKNRendererSwitch.special_options['error'];
    $jkn_rdr_switch_history[$selected] = $error;

    // Re-enable elements
    jkn_rdr_switch_ajax_done();
}


/*
 * =============================================================================
 * Element manipulation
 * =============================================================================
 */

/**
 * Change the cursor to the given value for various key elements.
 */
function jkn_rdr_switch_change_cursor($value) {
    $('html, body, select').each(function() {
            this.style.setProperty('cursor', $value, 'important');
        }
    );
}

/**
 * Return a jQuery selector for all switch divs.
 *
 * @returns {*|HTMLElement}
 */
function jkn_rdr_switch_divs() {
    return $('.' + JKNRendererSwitch.cl_switch_div);
}

/**
 * Return a jQuery selector for the currently active switch div.
 *
 * @returns {*}
 */
function jkn_rdr_switch_current() {
    $divs = jkn_rdr_switch_divs();
    return $divs.not('.' + JKNRendererSwitch.cl_hide);
}

/**
 * Turn the given switch div ID on (and turn off the current one).
 *
 * @param $id The ID of the switch div to turn on.
 */
function jkn_rdr_switch_on($id) {
    $hide = JKNRendererSwitch.cl_hide;
    jkn_rdr_switch_current().addClass($hide);
    $('#' + $id).removeClass($hide);
}

/**
 * Disable/enable select field.
 */              
function jkn_rdr_switch_disable_switch($value) {
    $select = $('#'.concat(JKNRendererSwitch.id_select));
    $select.attr('disabled', $value);
    if ($value) {
        $select.addClass(JKNRendererSwitch.cl_select_dis);
    } else {
        $select.removeClass(JKNRendererSwitch.cl_select_dis);
    }
}


/*
 * =============================================================================
 * Onload
 * =============================================================================
 */

jkn_rdr_switch_onload();
