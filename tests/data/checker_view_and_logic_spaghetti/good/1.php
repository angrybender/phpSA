<?php
/**
 *
 * @author k.vagin
 */

function generateHTMLFooter() {
	// Construct HTML
	$html = '';
	$html .= '  </body>' . PHP_EOL;
	$html .= '</html>' . PHP_EOL;

	// Return
	return $html;
}