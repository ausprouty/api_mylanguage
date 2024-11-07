<?php

function writeLog($filename, $content){
	if (LOG_MODE !== 'write_log' &&  LOG_MODE !== 'write_time_log'){
       return;
	}
	if ( LOG_MODE == 'write_time_log'){
       $filename =   time() . '-' . $filename;
	}
	$text = var_dump_ret($content);
    if (!file_exists(ROOT_LOG)){
		mkdir(ROOT_LOG);
	}
	$fh = fopen(ROOT_LOG . $filename . '.txt', 'w');
	fwrite($fh, $text);
    fclose($fh);
}
function writeLogAppend($filename, $content){
	$text = var_dump_ret($content);
    if (!file_exists(ROOT_LOG)){
		mkdir(ROOT_LOG);
	}
	$fh = ROOT_LOG .  'APPEND-'. $filename . '.txt';
    file_put_contents($fh, $text,  FILE_APPEND | LOCK_EX );
}

function writeLogDebug($filename, $content){
	$text = var_dump_ret($content);
    if (!file_exists(ROOT_LOG)){
		mkdir(ROOT_LOG);
	}
	$fh = fopen(ROOT_LOG . 'DEBUG-'. $filename . '.txt', 'w');
	fwrite($fh, $text);
	fclose($fh);
}
function writeLogError($filename, $content){
	$text = var_dump_ret($content);
    if (!file_exists(ROOT_LOG)){
		mkdir(ROOT_LOG);
	}
	$fh = fopen(ROOT_LOG . 'ERROR-'. $filename . '.txt', 'w');
	fwrite($fh, $text);
	fclose($fh);
}
function var_dump_ret($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}
