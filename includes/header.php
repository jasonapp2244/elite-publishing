<?php
declare(strict_types=1);

/**
 * Main-site header.
 *
 * The markup moved to components/site-header.php when the landing pages were
 * standardised on the same chrome — this file stays as the main site's entry
 * point so no page had to change its include, and so there is one obvious
 * place to add anything that belongs only to the main site.
 *
 * The LP equivalent is includes/lp-header.php. Both render the same component.
 */

require __DIR__ . '/components/site-header.php';
