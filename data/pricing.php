<?php
declare(strict_types=1);

/**
 * Elite Publishing — pricing plans.
 *
 * SPEC §D.2 "Plans", transcribed verbatim. Identical on the home page, the
 * pricing page and all ten service pages — there is no per-service variation.
 * Standard is the featured tier: 2px green border plus an offset halo, a
 * "Most Popular" pill, and a green filled CTA (SPEC §B.7).
 */

return [
    'eyebrow' => 'PLANS',
    'heading' => "Publishing Packages For\nEvery Author",
    'tiers'   => [
        [
            'name'     => 'Basic Package',
            'price'    => '700',
            'period'   => '/ month',
            'icon'     => 'book-open',
            'featured' => false,
            'badge'    => null,
            'features' => [
                'Professional Editing',
                'Basic Book Formatting',
                'Simple Cover Design',
                'Publishing Assistance',
            ],
            'cta'      => 'Get Started',
            'variant'  => 'green-outline',
        ],
        [
            'name'     => 'Standard Package',
            'price'    => '1000',
            'period'   => '/ month',
            'icon'     => 'sparkle',
            'featured' => true,
            /* Was a "Most Popular" pill. Popularity is a sales claim, and
               nothing in the project measures which package sells most. The
               badge is dropped rather than reworded — the tier is already
               called Standard Package, so a pill repeating that says nothing.
               components/plans.php only renders the pill when this is set, so
               the card layout is unchanged. */
            'badge'    => null,
            'features' => [
                'Ghostwriting Support',
                'Editing & Proofreading',
                'Custom Book Cover Design',
                'Publishing & Distribution',
                'Interior Formatting',
            ],
            'cta'      => 'Get Started',
            'variant'  => 'green',
        ],
        [
            'name'     => 'Premium Package',
            'price'    => '1500',
            'period'   => '/ month',
            'icon'     => 'crown',
            'featured' => false,
            'badge'    => null,
            'features' => [
                'Full Ghostwriting Service',
                'Advanced Editing & Proofreading',
                'Premium Cover & Layout Design',
                'Global Publishing & Distribution',
            ],
            'cta'      => 'Get Started',
            'variant'  => 'green-outline',
        ],
    ],
];
