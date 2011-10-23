<?php
function lang_parse( $body ) {
	$body = preg_replace_callback("/{{([^}]+)}}/", 'lang_parse_word', $body);
	return $body;
}
function lang_parse_word( $word ) {
	$word = $word[1];
	return lt($word);
}
//$x = lang_parse("hello {{world how}} vs {{are you}}");
?>