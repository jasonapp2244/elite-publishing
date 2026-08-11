<?php
declare(strict_types=1);

/**
 * Privacy Policy — SPEC §C.6, copy §D.8.
 *
 * Long-form document: h1, then alternating h2 + paragraph / bulleted list,
 * left-aligned across the full measure. Plain page background — no mint
 * gradient, no hero image, no CTA.
 */

require_once __DIR__ . '/includes/config.php';

$pageKey         = 'privacy';
$pageTitle       = 'Privacy Policy';
$pageDescription = 'How Elite Publishing collects, uses, shares and protects the personal '
                 . 'information of the authors who work with us.';
$pageCss         = ['css/p-core.css'];

require __DIR__ . '/includes/head.php';

// SPEC §D.8, transcribed verbatim.
$blocks = [
    [
        'h' => 'Your Privacy Matters to Us',
        'p' => 'We are committed to protecting your personal information and ensuring your experience with our services is safe, secure, and transparent.',
    ],
    [
        'h'    => 'Information We Collect',
        'p'    => 'When you use our website or services, we may collect the following information:',
        'list' => [
            'Name and contact details (email, phone number)',
            'Project details you share with us',
            'Billing or payment information (if applicable)',
            'Communication history with our team',
        ],
    ],
    [
        'h'    => 'How We Use Your Information',
        'p'    => 'We use your information to:',
        'list' => [
            'Provide book writing, editing, and publishing services',
            'Communicate with you about your project',
            'Process payments and invoices',
            'Improve our services and customer experience',
        ],
    ],
    [
        'h' => 'Data Protection',
        'p' => 'We take your privacy seriously and implement strict security measures to protect your personal data from unauthorized access, misuse, or disclosure.',
    ],
    [
        'h' => 'Information Sharing',
        'p' => 'We do not sell, rent, or trade your personal information. Your data is only shared with trusted team members involved in your project.',
    ],
    [
        'h' => 'Third-Party Services',
        'p' => 'We may use third-party platforms (such as payment processors or publishing platforms) that have their own privacy policies. We are not responsible for their practices.',
    ],
    [
        'h'    => 'Your Rights',
        'p'    => 'You have the right to:',
        'list' => [
            'Request access to your personal data',
            'Ask for corrections or updates',
            'Request deletion of your information',
        ],
    ],
    [
        'h' => 'Updates to This Policy',
        'p' => 'We may update this Privacy Policy from time to time. Any changes will be posted on this page.',
    ],
    [
        'h' => 'Contact Us',
        'p' => 'If you have any questions about this Privacy Policy, please contact us through our website.',
    ],
];
?>

<section class="doc" aria-labelledby="doc-h">
  <div class="container-ep">
    <h1 class="doc__title" id="doc-h">Privacy Policy</h1>

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
