<?php
declare(strict_types=1);
foreach (['homepage', 'our-books', 'about-our-company'] as $p) {
    foreach (glob(__DIR__ . "/../_figma-ref/{$p}__*.jpg") as $f) {
        $s = getimagesize($f);
        printf("%-28s %5d x %5d\n", basename($f), $s[0], $s[1]);
    }
}
