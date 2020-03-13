<?php
$mode = __FILE__;
$pos = strpos($mode, "@");
$mode = $pos === false ? "bare" : substr($mode, $pos + 1, strrpos($mode, ".") - $pos - 1);
$libload = function () {
	$dirs = scandir(__DIR__);
	foreach ($dirs as $dir) {
		$autoload = __DIR__ . "/{$dir}/autoload.php";
		if (file_exists($autoload))
			require_once($autoload);
		$autoload = __DIR__ . "/{$dir}/autoload@bare.php";
		if (file_exists($autoload))
			require_once($autoload);
		$autoload = __DIR__ . "/{$dir}/autoload@module.php";
		if (file_exists($autoload))
			require_once($autoload);
	}
};
if ($mode === "lib") {
	$libload();
} else {
	if ($mode !== "bare" && $mode !== "module") {
		echo "[autoload/" . __FILE__ . "] Invalid mode of autoload" . PHP_EOL;
		exit;
	}
	$prefix = $mode === "module" ? "/src/" : "/";
	spl_autoload_register(function ($class) use ($prefix) {
		$baseDir = __DIR__ . $prefix;
		$file = str_replace('\\', '/', $baseDir . $class) . '.php';
		// echo "[autoload] Loading " . $file . PHP_EOL;
		if (file_exists($file)) {
			require_once($file);
		}
	});
}