<?php

class Minify extends \Prefab {
	public static function clean($_src) {
		$timer_start = microtime(TRUE);
		$_out = preg_replace(
			'/\h*<\?(?!xml)(?:php|\s*=)?.+?\?>\h*|'.
			'\{\*.+?\*\}/is','',$_src);
		$_out2 = preg_replace('/<!--(.|\s)*?-->/', '', $_out);
		$html = str_replace(array("\n","\r","\t","\r\n"),'',$_out);
		$timer_end = microtime(TRUE);
		$TimeLoad = $timer_end - $timer_start;
		//ini_set('zlib.output_compression','On');
		//return $html."\r\n<!----- Chargement de la page : $TimeLoad ----->\r\n";
		return $_out2;
	}
}