<?php
declare(strict_types=1);

/**
 * Terms & Conditions — SPEC §C.7, copy §D.9.
 *
 * Same structure as the Privacy Policy: h1, then alternating h2 + paragraph /
 * bulleted list, left-aligned across the full measure, plain background.
 *
 * DECISIONS §11 bug 5: the second "Intellectual Property" bullet carries a
 * LEADING SPACE in the design. It is reproduced verbatim below, and the list
 * is rendered with `white-space: pre-wrap` so the space survives HTML's
 * whitespace collapsing exactly as it appears in the export. Do not trim it.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'terms';
$pageTitle       = 'Terms & Conditions';
$pageDescription = 'The terms that apply when you use Elite Publishing services: payments, '
                 . 'delivery, revisions, intellectual property and confidentiality.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';

// SPEC §D.9, transcribed verbatim.
$blocks = [
    [
        'h' => 'Please Read Carefully Before Using Our Services',
        'p' => 'By accessing our website or using our services, you agree to the following terms and conditions. If you do not agree, please do not use our services.',
    ],
    [
        'h' => 'Services',
        'p' => 'We provide professional book writing, editing, proofreading, design, and publishing services. All services are delivered based on the package or agreement selected by the client.',
    ],
    [
        'h'    => 'Payments',
        'list' => [
            'All payments must be made according to the agreed pricing plan or invoice.',
            'Work will begin only after receiving the initial payment (if applicable).',
            'Payments are non-refundable once the project has started.',
        ],
    ],
    [
        'h'    => 'Project Delivery',
        'list' => [
            'Delivery timelines are estimated and may vary depending on project complexity.',
            'We aim to deliver high-quality work within the agreed timeframe.',
            'Delays caused by missing client feedback or information are not our responsibility.',
        ],
    ],
    [
        'h'    => 'Revisions',
        'list' => [
            'We offer a limited number of revisions based on the selected package.',
            'Additional revisions may incur extra charges.',
            'Revisions must be requested within the agreed revision period.',
        ],
    ],
    [
        'h'    => 'Intellectual Property',
        'list' => [
            'Upon full payment, the final content belongs to the client.',
            ' We reserve the right to showcase completed work in our portfolio unless otherwise agreed.',
        ],
    ],
    [
        'h' => 'Confidentiality',
        'p' => 'We respect your privacy. All client information, manuscripts, and project details are kept strictly confidential and are not shared with third parties.',
    ],
    [
        'h' => 'Limitation of Liability',
        'p' => 'We are not responsible for any losses or damages arising from the use of our services or published content.',
    ],
    [
        'h' => 'Changes to Terms',
        'p' => 'We may update these Terms & Conditions at any time. Continued use of our services means you accept any updates.',
    ],
    [
        'h' => 'Contact Us',
        'p' => 'If you have any questions about these Terms, please contact us through our website.',
    ],
];
?>

<section class="doc" aria-labelledby="doc-h">
  <div class="container-ep">
    <h1 class="doc__title" id="doc-h">Terms &amp; Conditions</h1>

    <?php foreach ($blocks as $block): ?>
      <div class="doc__block">
        <h2 class="doc__h"><?= esc($block['h']) ?></h2>
        <?php if (!empty($block['p'])): ?>
          <p class="doc__p"><?= esc($block['p']) ?></p>
        <?php endif; ?>
        <?php if (!empty($block['list'])): ?>
          <ul class="doc__list">
            <?php foreach ($block['list'] as $item): ?>
<li><?= esc($item) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
