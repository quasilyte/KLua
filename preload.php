<?php

// This preload script is needed for PHP.
//
// Use something like this to enable preloading:
// php -d opcache.enable_cli=1 -d opcache.preload=preload.php

require_once __DIR__ . '/vendor/autoload.php';

use KLua\KLua;

$root_path = __DIR__;
if (!chdir($root_path)) {
  die("failed chdir to $root_path\n");
}
if (!KLua::loadFFI()) {
  die("failed to preload FFI\n");
}
