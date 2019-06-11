<?php

/**
 * Class for providing various minification functions.
 * 
 * Source: https://gist.github.com/tovic/d7b310dea3b33e4732c0#file-php-html-css-js-minifier-php
 * Based on https://github.com/mecha-cms/mecha-cms/blob/master/engine/plug/converter.php
 * 
 * Very lightly modified to make it class-based by Luke Sawczak.
 */
final class JKNMinifying {
    
    // Helper function(s)

    private static $X = "\x1A"; // a placeholder character

    private static $SS = '"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'';
    private static $CC = '\/\*[\s\S]*?\*\/';
    private static $CH = '<\!--[\s\S]*?-->';

	/**
	 * Return the input with the placeholder prepended to whitespace characters.
	 *
	 * @param string $input The input to add placeholders to.
	 * @return string The input with placeholders.
	 */
    private static function __minify_x(string $input): string {
        return str_replace(
            [
                "\n",
                "\t",
                ' '
            ],
            [
                self::$X . '\n',
                self::$X . '\t',
                self::$X . '\s'
            ],
            $input
        );
    }

	/**
	 * Return the input with the placeholder removed from whitespace characters.
	 *
	 * @param string $input The input to remove placeholders from.
	 * @return string The input without placeholders.
	 */
    private static function __minify_v(string $input): string {
        return str_replace(
            [
                self::$X . '\n',
                self::$X . '\t',
                self::$X . '\s'
            ],
            [
                "\n",
                "\t",
                ' '
            ],
        $input);
    }


    /*
     * =======================================================
     *  HTML MINIFIER
     * =======================================================
     * -- CODE: ----------------------------------------------
     *
     *    echo minify_html(file_get_contents('test.html'));
     *
     * -------------------------------------------------------
     */

	/**
	 * Helper for minify_html. Return the minified input.
	 *
	 * @param string $input The input to minify.
	 * @return string The minified input.
	 */
    private static function _minify_html(string $input): string {
        return preg_replace_callback('#<\s*([^\/\s]+)\s*(?:>|(\s[^<>]+?)\s*>)#',
                function(string $m): string {
            
            if(isset($m[2])) {
                // Minify inline CSS declaration(s)
                if(stripos($m[2], ' style=') !== false) {
                    $m[2] = preg_replace_callback('#( style=)([\'"]?)(.*?)\2#i',
                            function(string $m): string {
                        
                        return $m[1] . $m[2] . self::minify_css($m[3]) . $m[2];
                    }, $m[2]);
                }
                return '<' . $m[1] . preg_replace(
                    [
                        // From `defer="defer"`, `defer='defer'`, `defer="true"`, `defer='true'`, `defer=""` and `defer=''` to `defer` [^1]
                        '#\s(checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped)(?:=([\'"]?)(?:true|\1)?\2)#i',
                        // Remove extra white-space(s) between HTML attribute(s) [^2]
                        '#\s*([^\s=]+?)(=(?:\S+|([\'"]?).*?\3)|$)#',
                        // From `<img />` to `<img/>` [^3]
                        '#\s+\/$#'
                    ],
                    [
                        // [^1]
                        ' $1',
                        // [^2]
                        ' $1$2',
                        // [^3]
                        '/'
                    ],
                str_replace("\n", ' ', $m[2])) . '>';
            }
            return '<' . $m[1] . '>';
        }, $input);
    }

    /**
     * Return the given HTML minified.
     *
     * @param string $input The input to minify.
     * @return string The minified input.
     */
    static function minify_html(string $input): string {
        if( ! $input = trim($input)) return $input;
        // Keep important white-space(s) after self-closing HTML tag(s)
        $input = preg_replace('#(<(?:img|input)(?:\s[^<>]*?)?\s*\/?>)\s+#i', '$1' . self::$X . '\s', $input);
        // Create chunk(s) of HTML tag(s), ignored HTML group(s), HTML comment(s) and text
        $input = preg_split('#(' . self::$CH . '|<pre(?:>|\s[^<>]*?>)[\s\S]*?<\/pre>|<code(?:>|\s[^<>]*?>)[\s\S]*?<\/code>|<script(?:>|\s[^<>]*?>)[\s\S]*?<\/script>|<style(?:>|\s[^<>]*?>)[\s\S]*?<\/style>|<textarea(?:>|\s[^<>]*?>)[\s\S]*?<\/textarea>|<[^<>]+?>)#i', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach($input as $v) {
            if($v !== ' ' && trim($v) === "") continue;
            if($v[0] === '<' && substr($v, -1) === '>') {
                if($v[1] === '!' && strpos($v, '<!--') === 0) { // HTML comment ...
                    // Remove if not detected as IE comment(s) ...
                    if(substr($v, -12) !== '<![endif]-->') continue;
                    $output .= $v;
                } else {
                    $output .= self::__minify_x(self::_minify_html($v));
                }
            } else {
                // Force line-break with `&#10;` or `&#xa;`
                $v = str_replace(['&#10;', '&#xA;', '&#xa;'], self::$X . '\n', $v);
                // Force white-space with `&#32;` or `&#x20;`
                $v = str_replace(['&#32;', '&#x20;'], self::$X . '\s', $v);
                // Replace multiple white-space(s) with a space
                $output .= preg_replace('#\s+#', ' ', $v);
            }
        }
        // Clean up ...
        $output = preg_replace(
            [
                // Remove two or more white-space(s) between tag [^1]
                '#>([\n\r\t]\s*|\s{2,})<#',
                // Remove white-space(s) before tag-close [^2]
                '#\s+(<\/[^\s]+?>)#'
            ],
            [
                // [^1]
                '><',
                // [^2]
                '$1'
            ],
        $output);
        $output = self::__minify_v($output);
        // Remove white-space(s) after ignored tag-open and before ignored tag-close (except `<textarea>`)
        return preg_replace('#<(code|pre|script|style)(>|\s[^<>]*?>)\s*([\s\S]*?)\s*<\/\1>#i', '<$1$2$3</$1>', $output);
    }


    /*
     * =======================================================
     *  CSS MINIFIER
     * =======================================================
     * -- CODE: ----------------------------------------------
     *
     *    echo minify_css(file_get_contents('test.css'));
     *
     * -------------------------------------------------------
     */

    /**
     * Helper for minify_css. Return the minified input.
     *
     * @param string $input The input to minify.
     * @return string The minified input.
     */
    private static function _minify_css(string $input): string {
        // Keep important white-space(s) in `calc()`
        if(stripos($input, 'calc(') !== false) {
            $input = preg_replace_callback('#\b(calc\()\s*(.*?)\s*\)#i',
                    function(string $m): string {
                
                return $m[1] . preg_replace('#\s+#', self::$X . '\s', $m[2]) . ')';
            }, $input);
        }
        // Minify ...
        return preg_replace(
            [
                // Fix case for `#foo [bar="baz"]` and `#foo :first-child` [^1]
                '#(?<![,\{\}])\s+(\[|:\w)#',
                // Fix case for `[bar="baz"] .foo` and `@media (foo: bar) and (baz: qux)` [^2]
                '#\]\s+#', '#\b\s+\(#', '#\)\s+\b#',
                // Minify HEX color code ... [^3]
                '#\#([\da-f])\1([\da-f])\2([\da-f])\3\b#i',
                // Remove white-space(s) around punctuation(s) [^4]
                '#\s*([~!@*\(\)+=\{\}\[\]:;,>\/])\s*#',
                // Replace zero unit(s) with `0` [^5]
                '#\b(?:0\.)?0([a-z]+\b|%)#i',
                // Replace `0.6` with `.6` [^6]
                '#\b0+\.(\d+)#',
                // Replace `:0 0`, `:0 0 0` and `:0 0 0 0` with `:0` [^7]
                '#:(0\s+){0,3}0(?=[!,;\)\}]|$)#',
                // Replace `background(?:-position)?:(0|none)` with `background$1:0 0` [^8]
                '#\b(background(?:-position)?):(0|none)\b#i',
                // Replace `(border(?:-radius)?|outline):none` with `$1:0` [^9]
                '#\b(border(?:-radius)?|outline):none\b#i',
                // Remove empty selector(s) [^10]
                '#(^|[\{\}])(?:[^\{\}]+)\{\}#',
                // Remove the last semi-colon and replace multiple semi-colon(s) with a semi-colon [^11]
                '#;+([;\}])#',
                // Replace multiple white-space(s) with a space [^12]
                '#\s+#'
            ],
            [
                // [^1]
                self::$X . '\s$1',
                // [^2]
                ']' . self::$X . '\s', self::$X . '\s(', ')' . self::$X . '\s',
                // [^3]
                '#$1$2$3',
                // [^4]
                '$1',
                // [^5]
                '0',
                // [^6]
                '.$1',
                // [^7]
                ':0',
                // [^8]
                '$1:0 0',
                // [^9]
                '$1:0',
                // [^10]
                '$1',
                // [^11]
                '$1',
                // [^12]
                ' '
            ],
        $input);
    }

    /**
     * Return the given CSS minified.
     *
     * @param string $input The input to minify.
     * @return string The minified input.
     */
    static function minify_css(string $input): string {
        if( ! $input = trim($input)) return $input;
        // Keep important white-space(s) between comment(s)
        $input = preg_replace('#(' . self::$CC . ')\s+(' . self::$CC . ')#', '$1' . self::$X . '\s$2', $input);
        // Create chunk(s) of string(s), comment(s) and text
        $input = preg_split('#(' . self::$SS . '|' . self::$CC . ')#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach($input as $v) {
            if(trim($v) === "") continue;
            if(
                ($v[0] === '"' && substr($v, -1) === '"') ||
                ($v[0] === "'" && substr($v, -1) === "'") ||
                (strpos($v, '/*') === 0 && substr($v, -2) === '*/')
            ) {
                // Remove if not detected as important comment ...
                if($v[0] === '/' && strpos($v, '/*!') !== 0) continue;
                $output .= $v; // String or comment ...
            } else {
                $output .= self::_minify_css($v);
            }
        }
        // Remove quote(s) where possible ...
        $output = preg_replace(
            [
                // '#(' . self::$CC . ')|(?<!\bcontent\:|[\s\(])([\'"])([a-z_][-\w]*?)\2#i',
                '#(' . self::$CC . ')|\b(url\()([\'"])([^\s]+?)\3(\))#i'
            ],
            [
                // '$1$3',
                '$1$2$4$5'
            ],
        $output);
        return self::__minify_v($output);
    }


    /*
     * =======================================================
     *  JAVASCRIPT MINIFIER
     * =======================================================
     * -- CODE: ----------------------------------------------
     *
     *    echo minify_js(file_get_contents('test.js'));
     *
     * -------------------------------------------------------
     */

    /**
     * Helper for minify_js. Return the minified input.
     *
     * @param string $input The input to minify.
     * @return string The minified input.
     */
    private static function _minify_js(string $input): string {
        return preg_replace(
            [
                // Remove inline comment(s) [^1]
                '#\s*\/\/.*$#m',
                // Remove white-space(s) around punctuation(s) [^2]
                '#\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#',
                // Remove the last semi-colon and comma [^3]
                '#[;,]([\]\}])#',
                // Replace `true` with `!0` and `false` with `!1` [^4]
                '#\btrue\b#', '#\bfalse\b#', '#\breturn\s+#'
            ],
            [
                // [^1]
                "",
                // [^2]
                '$1',
                // [^3]
                '$1',
                // [^4]
                '!0', '!1', 'return '
            ],
        $input);
    }

    /**
     * Return the given Javascript, minified.
     *
     * @param string $input The input to minify.
     * @return string The minified input.
     */
    static function minify_js(string $input): string {
        if( ! $input = trim($input)) return $input;
        // Create chunk(s) of string(s), comment(s), regex(es) and text
        $input = preg_split('#(' . self::$SS . '|' . self::$CC . '|\/[^\n]+?\/(?=[.,;]|[gimuy]|$))#', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $output = "";
        foreach($input as $v) {
            if(trim($v) === "") continue;
            if(
                ($v[0] === '"' && substr($v, -1) === '"') ||
                ($v[0] === "'" && substr($v, -1) === "'") ||
                ($v[0] === '/' && substr($v, -1) === '/')
            ) {
                // Remove if not detected as important comment ...
                if(strpos($v, '//') === 0 || (strpos($v, '/*') === 0 && strpos($v, '/*!') !== 0 && strpos($v, '/*@cc_on') !== 0)) continue;
                $output .= $v; // String, comment or regex ...
            } else {
                $output .= self::_minify_js($v);
            }
        }
        return preg_replace(
            [
                // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}` [^1]
                '#(' . self::$CC . ')|([\{,])([\'])(\d+|[a-z_]\w*)\3(?=:)#i',
                // From `foo['bar']` to `foo.bar` [^2]
                '#([\w\)\]])\[([\'"])([a-z_]\w*)\2\]#i'
            ],
            [
                // [^1]
                '$1$2$4',
                // [^2]
                '$1.$3'
            ],
        $output);
    }
}
