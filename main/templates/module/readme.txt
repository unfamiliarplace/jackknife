This module was made by Luke Sawczak in August 2016
to add some options for customizing the front page.

Last updated July 1, 2017 / v 2.6

Added:

ANNOUNCEMENT settings page.

Other files:

-- Settings page is an ACF options page, programmatically registered in includes/acf_api
-- announcement.php is a shortcode for rendering announcement banners

================================================================================
ANNOUNCEMENT
============

Author(s):          Luke Sawczak
Created:            2016-08
Last updated:       2017-09-10
Last documented:    2017-09-10

================================================================================
OVERVIEW
========

This module adds a simple shortcode to insert an announcement banner controlled
by an ACF settings page. You can set a text, rich text, or image announcement,
along with options like a link for the block and the various colours.

================================================================================
FEATURES
========

--  Adds an ACF settings page to set the announcement variables.

--  Adds a shortcode to display the announcement.

================================================================================
USAGE
=====

1.  Configure the settings on the settings page.

2.  Add the shortcode and/or widget wherever you want. It will not show
    anything if you've turned Announcement off, so you can set it and forget it.

================================================================================
FUTURE
======

--  More customization for the announcement.

================================================================================
TECHNICAL NOTES
===============

--  The settings page uses JKNACF.
