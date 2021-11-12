<?php
require_once(__DIR__.'/../conf.php');
require_once('lang.php');
require_once('db.php');
require_once('api.php');
require_once('plan.php');
require_once('genics.php');
require_once('genpdf.php');
require_once('genhtml.php');
require_once('permissions.php');
require_once('htmlinput.php');
require_once('autoplan.php');
require_once('license.php');
require_once('browser.php');
require_once('color.php');
require_once('boxes.php');
require_once('tools.php');
require_once('roles.php');
require_once('mailer.php');
require_once('texttemplate.php');
require_once('const.php');
require_once('fpdf/fpdf.php');
require_once('phplot/phplot.php');


// integrity check
$md5sums = explode("\n", file_get_contents(__DIR__.'/md5sums.txt'));
foreach(glob(__DIR__.'/*.php') as $file) {
	$ok = false;
	$sum = md5_file($file);
	foreach($md5sums as $md5sum) {
		$split = explode('  ', $md5sum);
		if(count($split) != 2) continue;
		if($split[1] == basename($file) && $split[0] == $sum) {
			$ok = true;
			break;
		}
	}
	if(!$ok) die('Integrity check failed. Please reinstall MASTERPLAN.');
}

// init locale
setlocale(LC_ALL, LOCALE);

// init db connection
$db    = new db(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$perm  = new permissions($db);
$lic   = new license(count($db->getUsers()));
$roles = new roles($db);
