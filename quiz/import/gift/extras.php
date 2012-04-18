<?php 

function get_config($k, $v){

	if($k == 'quiz' && $v == 'shuffleanswers'){
		return false;
	}
}

function shorten_text($text, $ideal=30, $exact = false, $ending='...') {

	global $CFG;

	// if the plain text is shorter than the maximum length, return the whole text
	if (strlen(preg_replace('/<.*?>/', '', $text)) <= $ideal) {
		return $text;
	}

	// Splits on HTML tags. Each open/close/empty tag will be the first thing
	// and only tag in its 'line'
	preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

	$total_length = strlen($ending);
	$truncate = '';

	// This array stores information about open and close tags and their position
	// in the truncated string. Each item in the array is an object with fields
	// ->open (true if open), ->tag (tag name in lower case), and ->pos
	// (byte position in truncated text)
	$tagdetails = array();

	foreach ($lines as $line_matchings) {
		// if there is any html-tag in this line, handle it and add it (uncounted) to the output
		if (!empty($line_matchings[1])) {
			// if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
			if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
				// do nothing
				// if tag is a closing tag (f.e. </b>)
			} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
				// record closing tag
				$tagdetails[] = (object)array('open'=>false,
                    'tag'=>strtolower($tag_matchings[1]), 'pos'=>strlen($truncate));
				// if tag is an opening tag (f.e. <b>)
			} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
				// record opening tag
				$tagdetails[] = (object)array('open'=>true,
                    'tag'=>strtolower($tag_matchings[1]), 'pos'=>strlen($truncate));
			}
			// add html-tag to $truncate'd text
			$truncate .= $line_matchings[1];
		}

		// calculate the length of the plain text part of the line; handle entities as one character
		$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
		if ($total_length+$content_length > $ideal) {
			// the number of characters which are left
			$left = $ideal - $total_length;
			$entities_length = 0;
			// search for html entities
			if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
				// calculate the real length of all entities in the legal range
				foreach ($entities[0] as $entity) {
					if ($entity[1]+1-$entities_length <= $left) {
						$left--;
						$entities_length += strlen($entity[0]);
					} else {
						// no more characters left
						break;
					}
				}
			}
			$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
			// maximum length is reached, so get off the loop
			break;
		} else {
			$truncate .= $line_matchings[2];
			$total_length += $content_length;
		}

		// if the maximum length is reached, get off the loop
		if($total_length >= $ideal) {
			break;
		}
	}

	// if the words shouldn't be cut in the middle...
	if (!$exact) {
		// ...search the last occurence of a space...
		for ($k=strlen($truncate);$k>0;$k--) {
			if (!empty($truncate[$k]) && ($char = $truncate[$k])) {
				if ($char == '.' or $char == ' ') {
					$breakpos = $k+1;
					break;
				} else if (ord($char) >= 0xE0) {
					// Chinese/Japanese/Korean text
					$breakpos = $k;               // can be truncated at any UTF-8
					break;                        // character boundary.
				}
			}
		}

		if (isset($breakpos)) {
			// ...and cut the text in this position
			$truncate = substr($truncate, 0, $breakpos);
		}
	}

	// add the defined ending to the text
	$truncate .= $ending;

	// Now calculate the list of open html tags based on the truncate position
	$open_tags = array();
	foreach ($tagdetails as $taginfo) {
		if(isset($breakpos) && $taginfo->pos >= $breakpos) {
			// Don't include tags after we made the break!
			break;
		}
		if($taginfo->open) {
			// add tag to the beginning of $open_tags list
			array_unshift($open_tags, $taginfo->tag);
		} else {
			$pos = array_search($taginfo->tag, array_reverse($open_tags, true)); // can have multiple exact same open tags, close the last one
			if ($pos !== false) {
				unset($open_tags[$pos]);
			}
		}
	}

	// close all unclosed html-tags
	foreach ($open_tags as $tag) {
		$truncate .= '</' . $tag . '>';
	}

	return $truncate;
}

?>