<?php
$d = __DIR__ . '/../Elite Publishing -fgma-images';
foreach (glob($d . '/*.png') as $f) {
    $s = getimagesize($f);
    printf("%-34s %5d x %5d\n", basename($f), $s[0], $s[1]);
}
