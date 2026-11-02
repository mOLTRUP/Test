<?php
if (!defined('ABSPATH')) { exit; }
if (!headers_sent()) { header('X-Robots-Tag: noindex, nofollow, noarchive', true); }
$demo_url = esc_url(plugins_url('presentation.html', dirname(__DIR__) . '/itd-ledelsesdemo.php'));
?><!doctype html>
<html lang="da">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow,noarchive">
<title>ITD Salgsadministration – låst ledelsesdemo</title>
<style>html,body{margin:0;padding:0;width:100%;height:100%;background:#e6ecf4;overflow:hidden}*{box-sizing:border-box}iframe{display:block;width:100%;height:100vh;height:100dvh;border:0;background:#e6ecf4}</style>
</head>
<body>
<iframe src="<?php echo $demo_url; ?>" title="ITD Salgsadministration – fiktiv præsentationsversion" loading="eager" referrerpolicy="no-referrer" allow="fullscreen">Låst demo.</iframe>
</body>
</html>
