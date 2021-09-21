<?php

function redirect(string $url)
{
    header("Location: $url");
    exit;
}

function requireDir(string $dir)
{
    $files = scandir($dir);
    sort($files, SORT_STRING | SORT_FLAG_CASE);
    foreach ($files as $file)
        if ($file != ".." && $file != "." && $file != "public")
            if (is_dir($dir . "/$file"))
                requireDir($dir . "/$file");
            else
                require_once $dir . "/$file";
}

foreach (scandir(dirname(__FILE__)) as $file)
    if ($file != ".." && $file != "." && is_dir(dirname(__FILE__) . "/$file"))
        requireDir(dirname(__FILE__) . "/$file");