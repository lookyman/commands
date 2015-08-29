<?php

use Nette\Caching\Storages\DevNullStorage;
use Nette\Loaders\RobotLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new RobotLoader;
$loader->autoRebuild = true;
$loader->setCacheStorage(new DevNullStorage)
	->addDirectory(__DIR__ . '/../src')
	->addDirectory(__DIR__)
	->register();

define('TEMP_DIR', __DIR__ . '/tmp');

call_user_func(function ($dir) {
	if (!is_dir($dir)) {
		mkdir($dir);
	}
	foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $entry) {
		$entry->isDir() ? rmdir($entry) : unlink($entry);
	}
}, TEMP_DIR);
