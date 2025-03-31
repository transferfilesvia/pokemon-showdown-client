<?php

$rel_dir = explode('?', $_SERVER['REQUEST_URI'])[0];
$slash_pos = strrpos($rel_dir, '/');
if ($slash_pos !== false) $rel_dir = substr($rel_dir, 0, $slash_pos + 1);

$dir = $_SERVER['DOCUMENT_ROOT'] . $rel_dir;
$dirname = basename($dir);
$files = scandir($dir);
$fileinfo = [];

$at_root = ($rel_dir === '/');
$up = null;

function get_icon($file, $is_dir) {
	if ($is_dir) {
		return 'folder-open';
	} else {
		$info = pathinfo($file);
		$ext = strtolower($info['extension'] ?? '.');
		if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png' || $ext === 'gif' || $ext === 'bmp' || $ext === 'webp' || $ext === 'svg') {
			return 'picture-o';
		} else if ($ext === 'mp4' || $ext === 'webm' || $ext === 'mkv' || $ext === 'avi' || $ext === 'mov') {
			return 'video-camera';
		} else if ($ext === 'mp3' || $ext === 'ogg' || $ext === 'wav' || $ext === 'flac' || $ext === 'aac') {
			return 'volume-up';
		} else if ($ext === 'zip' || $ext === 'tar' || $ext === 'gz') {
			return 'file-archive-o';
		} else if ($ext === 'txt' || $ext === 'md') {
			return 'file-text-o';
		} else if ($ext === 'html' || $ext === 'php') {
			return 'file-code-o';
		} else if ($ext === 'c' || $ext === 'cpp' || $ext === 'h' || $ext === 'hpp' || $ext === 'py' || $ext === 'js' || $ext === 'ts' || $ext === 'jsx' || $ext === 'tsx' || $ext === 'json' || $ext === 'xml' || $ext === 'css' || $ext === 'java' || $ext === 'rb' || $ext === 'go' || $ext === 'swift' || $ext === 'rs' || $ext === 'map') {
			return 'code';
		} else if ($ext === 'woff' || $ext === 'woff2' || $ext === 'ttf' || $ext === 'otf' || $ext === 'eot') {
			return 'font';
		} else if ($ext === 'pdf') {
			return 'file-pdf-o';
		} else if ($ext === 'doc' || $ext === 'docx' || $ext === 'odt') {
			return 'file-word-o';
		} else if ($ext === 'xls' || $ext === 'xlsx' || $ext === 'ods') {
			return 'file-excel-o';
		} else if ($ext === 'ppt' || $ext === 'pptx' || $ext === 'odp') {
			return 'file-powerpoint-o';
		}
	}
	return 'file-o';
}

foreach ($files as $file) {
	if ($file === '.') {
		continue;
	}
	$path = $dir . '/' . $file;

	$is_dir = is_dir($path);
	$ext = '';
	$type = get_icon($file, $is_dir);
	if ($file === '..') {
		$type = 'arrow-circle-o-up';
	} else if (!$is_dir) {
		$info = pathinfo($file);
		$ext = strtolower($info['extension'] ?? '.');
	}

	$size = $is_dir ? 0 : filesize($path);
	$size_text = '';
	if ($is_dir) {
		$size_text = '';
	} else if ($size > 1024 * 1024 * 1024) {
		$size_text = round($size / (1024 * 1024 * 1024), 2) . ' GiB';
	} else if ($size > 1024 * 1024) {
		$size_text = round($size / (1024 * 1024), 2) . ' MiB';
	} else if ($size > 1024) {
		$size_text = round($size / 1024, 2) . ' KiB';
	} else {
		$size_text = $size . ' bytes';
	}

	$next = [
		'name' => htmlentities($file),
		'mtime' => date('Y-m-d H:i:s', filemtime($path)),
		'type' => $type,
		'size' => $size,
		'ext' => $ext,
		'size_text' => $size_text,
	];
	if ($file === '..') {
		$up = $next;
	} else {
		$fileinfo[] = $next;
	}
}

$sort_by = $_GET['sort'] ?? $_GET['C'] ?? 'dir';
$sort_order = $_GET['order'] ?? $_GET['A'] ?? 'asc';
if ($sort_order === 'A') $sort_order = 'asc';
function sort_icon($col) {
	global $sort_by, $sort_order;
	if ($col === $sort_by) {
		return ' <i class="fa fa-caret-square-o-' . ($sort_order === 'asc' ? 'up' : 'down') . '"></i>';
	}
	return '';
}
function sort_link($col) {
	global $sort_by, $sort_order;
	if ($col === $sort_by && $sort_order === 'asc') {
		return './?sort=' . $col . '&order=desc';
	}
	if ($col === $sort_by && $sort_order === 'desc') {
		return './';
	}
	return './?sort=' . $col;
}

if ($sort_by === 'name' || $sort_by === 'N') {
	usort($fileinfo, fn($a, $b) => strcmp($a['name'], $b['name']) * ($sort_order === 'asc' ? 1 : -1));
} else if ($sort_by === 'size' || $sort_by === 'S') {
	usort($fileinfo, fn($a, $b) => ($a['size'] <=> $b['size']) * ($sort_order === 'asc' ? 1 : -1));
} else if ($sort_by === 'mtime' || $sort_by === 'M') {
	usort($fileinfo, fn($a, $b) => $a['mtime'] <=> $b['mtime']) * ($sort_order === 'asc' ? 1 : -1);
} else if ($sort_by === 'type') {
	usort($fileinfo, fn($a, $b) => $a['ext'] <=> $b['ext']) * ($sort_order === 'asc' ? 1 : -1);
} else { // name, dirs-first
	usort($fileinfo, fn($a, $b) => !!$a['ext'] <=> !!$b['ext']) * ($sort_order === 'asc' ? 1 : -1);
}

if ($up !== null && !$at_root) array_unshift($fileinfo, $up);

$title = 'Index of ' . $rel_dir;

?><!DOCTYPE html>
<html lang="en"><head>

	<meta charset="UTF-8" />

	<title><?= htmlentities($title) ?></title>

	<meta name="viewport" content="width=device-width" />
	<link rel="stylesheet" href="/dirindex/font-awesome.min.css" />

	<style>
		/*********************************************************
		 * Layout
		 *********************************************************/

		html, body {
			margin: 0;
			padding: 0;
			min-height: 100%;
		}

		html {
			color: white;
			font-family: Verdana,Helvetica,sans-serif;
			font-size: 11pt;
			background: #f0f0f0;
			color: #333333;
		}
		body {
			background: linear-gradient(to bottom, rgba(77, 93, 140, 0.6), rgba(77, 93, 140, 0.2) 80px, transparent 160px, transparent);
		}

		header {
			margin: 0;
			padding: 2px;
			/* background: rgba(255, 255, 255, .2);
			border-bottom: 1px solid rgba(255, 255, 255, .6); */
			text-align: center;
			height: 60px;
		}
		.nav-wrapper {
			width: 700px;
			margin: 0 auto;
			position: relative;
		}
		.nav {
			padding-left: 140px;
			padding-top: 5px;
		}
		.nav li {
			float: left;
			list-style-type: none;
		}
		.nav img {
			position: absolute;
			left: 0;
			top: 0;
		}
		.nav a, .nav a:visited {
			color: white;
			background: #3a4f88;
			background: linear-gradient(to bottom, #4c63a3, #273661);
			box-shadow: 0.5px 1px 2px rgba(255, 255, 255, 0.45), inset 0.5px 1px 1px rgba(255, 255, 255, 0.5);
			border: 1px solid #222c4a;
			text-shadow: black 0px -1px 0;
			padding: 8px 15px;
			font-weight: bold;
			text-decoration: none;
			border-radius: 0;
			margin-left: -1px;
			font-size: 11pt;
		}
		.dark .nav a, .dark .nav a:visited {
			/* make sure other styling doesn't override */
			color: white;
			background: #3a4f88;
			background: linear-gradient(to bottom, #4c63a3, #273661);
			border: 1px solid #222c4a;
			box-shadow: 0.5px 1px 2px rgba(255, 255, 255, 0.45), inset 0.5px 1px 1px rgba(255, 255, 255, 0.5);
		}
		.nav a:hover, .dark .nav a:hover {
			background: linear-gradient(to bottom, #5a77c7, #2f447f);
			border: 1px solid #222c4a;
		}
		.nav a:active, .dark .nav a:active {
			background: linear-gradient(to bottom, #273661, #4c63a3);
			box-shadow: 0.5px 1px 2px rgba(255, 255, 255, 0.45), inset 0.5px 1px -1px rgba(255, 255, 255, 0.5);
		}
		.nav a.cur, .nav a.cur:hover, .nav a.cur:active,
		.dark .nav a.cur, .dark .nav a.cur:hover, .dark .nav a.cur:active {
			color: #CCCCCC;
			background: rgba(79, 109, 148, 0.7);
			box-shadow: 0.5px 1px 2px rgba(255, 255, 255, 0.45);
			border: 1px solid #222c4a;
		}
		.nav a.nav-first {
			margin-left: 10px;
			border-top-left-radius: 8px;
			border-bottom-left-radius: 8px;
		}
		.nav a.nav-last {
			border-top-right-radius: 8px;
			border-bottom-right-radius: 8px;
		}
		.nav a.greenbutton {
			background: linear-gradient(to bottom, #4ca363, #276136);
		}
		.nav a.greenbutton:hover {
			background: linear-gradient(to bottom, #5ac777, #2f7f44);
		}
		.nav a.greenbutton:active {
			background: linear-gradient(to bottom, #276136, #4ca363);
			box-shadow: 0 1px 2px rgba(255, 255, 255, 0.45), inset 0.5px 1px -1px rgba(255, 255, 255, 0.5);
		}

		@media (max-width:700px) {
			.nav-wrapper {
				width: auto;
				display: inline-block;
			}
			.nav {
				padding-left: 135px;
			}
			.nav a {
				font-weight: normal;
				padding: 8px 7px;
			}
			.nav img {
				top: 10px;
			}
		}

		@media (max-width:554px) {
			header {
				height: 100px;
			}
			.nav {
				padding-left: 0;
				padding-top: 50px;
			}
			.nav img {
				top: 10px;
			}
			.nav a {
				padding: 8px 12px;
			}
			.nav a.nav-first {
				margin-left: 0;
			}
			.nav a.greenbutton {
				position: absolute;
				top: 10px;
				right: 0;
			}
		}
		@media (max-width:419px) {
			.nav a {
				padding: 8px 7px;
			}
		}
		@media (max-width:359px) {
			.nav-wrapper {
				padding-left: 5px;
			}
			.nav a {
				padding: 8px 4px;
			}
		}

		footer {
			clear: both;
			text-align: center;
			color: #888888;
			padding: 10px 0 10px 0;
		}
		footer p {
			margin: 10px 0;
		}
		footer a {
			color: #AAAAAA;
		}
		footer a:hover {
			color: #6688AA;
		}
		footer a.cur, footer a.cur:hover {
			color: #888888;
			font-weight: bold;
			text-decoration: none;
		}

		/*********************************************************
		 * Main
		 *********************************************************/
		.button {
			color: white;
			background: #3a4f88;
			background: linear-gradient(to bottom, #4c63a3, #273661);
			box-shadow: 0.5px 1px 2px rgba(255, 255, 255, 0.45), inset 0.5px 1px 1px rgba(255, 255, 255, 0.5);
			border: 1px solid #222c4a;
			padding: 3px 10px;
			text-shadow: black 0px -1px 0;
			border-radius: 10px;
			text-decoration: none;
			display: inline-block;
			font-family: Verdana,Helvetica,sans-serif;
			font-size: 11pt;
			cursor: pointer;
		}
		main {
			margin: 0 auto;
			padding: 0 15px 15px 15px;
			max-width: 800px;
			overflow-wrap: break-word;
		}
		a {
			color: #0073aa;
		}
		a:visited {
			color: #8000aa;
		}
		h1 {
			font-size: 20px;
			margin-bottom: 20px;
		}
		.parentlink {
			padding: 0 0 12px 0;
		}
		.parentlink a {
			text-decoration: none;
			display: block;
			border: 1px solid transparent;
			padding: 4px 8px 4px 40px;
			border-radius: 4px;
		}
		.parentlink a:hover {
			background: #e7ebee;
			border-color: #5f8a9e;
		}
		@media (prefers-color-scheme: dark) {
			html {
				background: #000;
				color: #ddd;
			}
			a {
				color:rgb(99, 174, 209);
			}
			a:visited {
				color:rgb(177, 123, 195);
			}
			.parentlink a:hover {
				background: #181818;
				border-color: #444;
			}
		}

		/*********************************************************
		 * Dirlist
		 *********************************************************/
		h1 a {
			font-weight: normal;
			text-decoration: none;
		}
		h1 a:hover {
			text-decoration: underline;
		}

		.dirlist {
			font-size: 14px;
			list-style-type: none;
			padding: 0;
		}
		.header {
			padding: 0 7px 0 39px;
			border-bottom: 1px solid #888888;
			background: #f0f0f0;
		}
		.parentlink {
			padding: 0 0 12px 0;
		}

		.header a, .header a:visited {
			text-decoration: none;
			padding: 5px 0;
			color: inherit;
		}
		.header a:hover {
			background: #dddddd;
		}
		a.row {
			text-decoration: none;
			display: block;
			border: 1px solid transparent;
			padding: 4px 8px 4px 40px;
			border-radius: 4px;
		}
		a.row:hover {
			background:#e7ebee;
			border-color:#5f8a9e;
		}
		a.row * {
			vertical-align: middle;
		}

		.icon {
			display: inline-block;
			width: 32px;
			margin-left: -32px;
			font-size: 20px;
			text-align: center;
			color: #555;
		}
		.icon.fa-arrow-circle-o-up, .icon.fa-folder-open {
			color: #3798c5;
		}
		.filename {
			display: inline-block;
			width: 50%;
			min-width: 260px;
			font-family: monospace;
		}
		.parentlink .filename {
			font-family: inherit;
			font-style: italic;
		}
		.filesize {
			display: inline-block;
			width: 20%;
			min-width: 80px;
			color: #666666;
		}
		.filemtime {
			display: inline-block;
			color: #666666;
			font-size: 0.9em;
			min-width: 150px;
		}
		.header .icon, .header .filename, .header .filesize, .header .filemtime {
			font-style: normal;
			font-family: inherit;
			font-size: inherit;
			color: inherit;
		}
		@media (prefers-color-scheme: dark) {
			.header {
				background: #000;
			}
			.header a:hover {
				background: #333;
			}
			.icon {
				color: #888;
			}
			a.row:hover {
				background: #181818;
				border-color: #444;
			}
			.filesize, .filemtime {
				color: #888;
			}
		}
	</style>
</head><body>

	<header>
		<div class="nav-wrapper"><ul class="nav">
			<li><a class="button nav-first" href="//pokemonshowdown.com/"><img src="//play.pokemonshowdown.com/pokemonshowdownbeta.png" srcset="//play.pokemonshowdown.com/pokemonshowdownbeta.png 1x, //play.pokemonshowdown.com/pokemonshowdownbeta@2x.png 2x" alt="Pok&eacute;mon Showdown" width="146" height="44" /> Home</a></li>
			<li><a class="button" href="//pokemonshowdown.com/dex/">Pok&eacute;dex</a></li>
			<li><a class="button" href="//replay.pokemonshowdown.com/">Replays</a></li>
			<li><a class="button" href="//pokemonshowdown.com/ladder/">Ladder</a></li>
			<li><a class="button nav-last" href="//pokemonshowdown.com/forums/">Forum</a></li>
			<li><a class="button greenbutton nav-first nav-last" href="//play.pokemonshowdown.com/">Play</a></li>
		</ul></div>
	</header>

	<main><h1>
		Index of
		<a href="/"><?= htmlentities($_SERVER['SERVER_NAME']) ?></a><?php

$path = '';
$pathparts = array_slice(explode('/', $rel_dir), 1, -1);
$lastpart = array_pop($pathparts);
foreach ($pathparts as $cur_dir) {
	$path .= '/' . $cur_dir;
	echo '<wbr />/';
	echo '<a href="' . htmlentities($path) . '/">' . htmlentities($cur_dir) . '</a>';
}
echo '<wbr />/' . htmlentities($lastpart) . '/';

?>

	</h1>

	<ul class="dirlist">
		<li class="header">
			<a class="icon" href="<?= sort_link('type') ?>">&nbsp;<?= sort_icon('type') ?>

			</a><a class="filename" href="<?= sort_link('name') ?>">Name<?= sort_icon('name') ?>

			</a><a class="filesize" href="<?= sort_link('size') ?>">Size<?= sort_icon('size') ?>

			</a><a class="filemtime" href="<?= sort_link('mtime') ?>">Last Modified<?= sort_icon('mtime') ?></a>
		</li>
<?php foreach ($fileinfo as $file) : ?>
		<li<?= $file['name'] === '..' ? ' class="parentlink"' : '' ?>>
			<a class="row" href="./<?= htmlentities($file['name']) ?>">
				<i class="icon fa fa-<?= $file['type'] ?>">
				</i><code class="filename"><?= $file['name'] === '..' ? '(Parent directory)' : htmlentities($file['name']) ?>

				</code><em class="filesize"><?= $file['size_text'] ?>

				</em><small class="filemtime"><?= $file['mtime'] ?></small>
			</a>
		</li>
<?php endforeach; ?>
	</ul>

	</main>

</body>
