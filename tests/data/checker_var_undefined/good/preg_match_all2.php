<?php
/**
 *
 * @author k.vagin
 */

function _parse_content() {

	// Matches generated content
	$re = "/\n".
		"\s(counters?\\([^)]*\\))|\n".
		"\A(counters?\\([^)]*\\))|\n".
		"\s([\"']) ( (?:[^\"']|\\\\[\"'])+ )(?<!\\\\)\\3|\n".
		"\A([\"']) ( (?:[^\"']|\\\\[\"'])+ )(?<!\\\\)\\5|\n" .
		"\s([^\s\"']+)|\n" .
		"\A([^\s\"']+)\n".
		"/xi";

	$content = $this->_frame->get_style()->content;

	$quotes = $this->_parse_quotes();

	// split on spaces, except within quotes
	if ( !preg_match_all($re, $content, $matches, PREG_SET_ORDER) ) {
		return null;
	}

	$text = "";

	foreach ($matches as $match) {

		if ( isset($match[2]) && $match[2] !== "" ) {
			$match[1] = $match[2];
		}

		if ( isset($match[6]) && $match[6] !== "" ) {
			$match[4] = $match[6];
		}

		if ( isset($match[8]) && $match[8] !== "" ) {
			$match[7] = $match[8];
		}

		if ( isset($match[1]) && $match[1] !== "" ) {

			// counters?(...)
			$match[1] = mb_strtolower(trim($match[1]));

			// Handle counter() references:
			// http://www.w3.org/TR/CSS21/generate.html#content

			$i = mb_strpos($match[1], ")");
			if ( $i === false ) {
				continue;
			}

			preg_match( '/(counters?)(^\()*?\(\s*([^\s,]+)\s*(,\s*["\']?([^"\'\)]+)["\']?\s*(,\s*([^\s)]+)\s*)?)?\)/i' , $match[1] , $args );
			$counter_id = $args[3];
			if ( strtolower( $args[1] ) == 'counter' ) {
				// counter(name [,style])
				if ( isset( $args[5] ) ) {
					$type = trim( $args[5] );
				}
				else {
					$type = null;
				}
				$p = $this->_frame->lookup_counter_frame( $counter_id );

				$text .= $p->counter_value($counter_id, $type);

			}
			else if ( strtolower( $args[1] ) == 'counters' ) {
				// counters(name, string [,style])
				if ( isset($args[5]) ) {
					$string = $this->_parse_string( $args[5] );
				}
				else {
					$string = "";
				}

				if ( isset( $args[7] ) ) {
					$type = trim( $args[7] );
				}
				else {
					$type = null;
				}

				$p = $this->_frame->lookup_counter_frame($counter_id);
				$tmp = array();
				while ($p) {
					// We only want to use the counter values when they actually increment the counter
					if ( array_key_exists( $counter_id , $p->_counters ) ) {
						array_unshift( $tmp , $p->counter_value($counter_id, $type) );
					}
					$p = $p->lookup_counter_frame($counter_id);

				}
				$text .= implode( $string , $tmp );

			}
			else {
				// countertops?
				continue;
			}

		}
		else if ( isset($match[4]) && $match[4] !== "" ) {
			// String match
			$text .= $this->_parse_string($match[4]);
		}
		else if ( isset($match[7]) && $match[7] !== "" ) {
			// Directive match

			if ( $match[7] === "open-quote" ) {
				// FIXME: do something here
				$text .= $quotes[0][0];
			}
			else if ( $match[7] === "close-quote" ) {
				// FIXME: do something else here
				$text .= $quotes[0][1];
			}
			else if ( $match[7] === "no-open-quote" ) {
				// FIXME:
			}
			else if ( $match[7] === "no-close-quote" ) {
				// FIXME:
			}
			else if ( mb_strpos($match[7],"attr(") === 0 ) {

				$i = mb_strpos($match[7],")");
				if ( $i === false ) {
					continue;
				}

				$attr = mb_substr($match[7], 5, $i - 5);
				if ( $attr == "" ) {
					continue;
				}

				$text .= $this->_frame->get_parent()->get_node()->getAttribute($attr);
			}
			else {
				continue;
			}
		}
	}

	return $text;
}