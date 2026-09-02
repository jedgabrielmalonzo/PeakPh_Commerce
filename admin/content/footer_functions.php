<?php
// Footer data management functions - no sessions or redirects

// Default footer data structure
$defaultFooterData = [
    'company_name' => 'Peak',
    'company_description' => 'Your ultimate destination for camping gear and outdoor equipment.',
    'contact_email' => 'contact@peakph.com',
    'contact_phone' => '+63 123 456 7890',
    'address' => 'Philippines',
    'facebook_link' => 'https://facebook.com/yourpage',
    'instagram_link' => 'https://instagram.com/yourpage',
    'youtube_link' => 'https://youtube.com/yourpage',
    'tiktok_link' => 'https://tiktok.com/@yourpage',
    'twitter_link' => '',
    'copyright_text' => '© 2025 Peak. All rights reserved.',
    'footer_links' => [
        'CUSTOMER SERVICE' => [
            'Contact Us' => 'pages/contact-us.php',
            'Return and Exchange' => '#returns',
            'Payment Methods' => '#payment'
        ],
        'SHOP AT PEAK' => [
            'Our Stores' => '#stores',
            'Delivery' => '#delivery',
            'Business Inquiries' => '#business',
            'Terms and Conditions' => 'pages/terms-and-conditions.php',
            'Privacy Policy' => 'pages/privacy-policy.php'
        ],
        'SERVICES' => [
            'Repairs' => '#repairs',
            'Buy Back' => '#buyback',
            'Click & Collect' => '#collect'
        ],
        'ABOUT US' => [
            'About Us' => 'pages/about-us.php',
            'Sustainability' => '#sustainability',
            'Certificate of Registration' => '#cert'
        ],
        'MORE' => [
            'Membership' => '#membership',
            'Share Your Ideas' => '#ideas',
            'Product Recall' => '#recall'
        ],
        'JOIN US' => [
            'Climbers Community' => 'pages/climbers-community.php'
        ]
    ]
];

// File path for footer data
$footerDataFile = __DIR__ . '/footer_settings.json';

// Function to get footer data
function getFooterData() {
    global $footerDataFile, $defaultFooterData;
    
    if (file_exists($footerDataFile)) {
        $data = json_decode(file_get_contents($footerDataFile), true);
        if ($data !== null) {
            // Merge with defaults to ensure all keys exist
            return array_merge($defaultFooterData, $data);
        }
    }
    
    return $defaultFooterData;
}

// Function to save footer data
function saveFooterData($data) {
    global $footerDataFile, $defaultFooterData;
    
    // Merge with defaults to preserve footer_links
    $fullData = array_merge($defaultFooterData, $data);
    
    return file_put_contents($footerDataFile, json_encode($fullData, JSON_PRETTY_PRINT)) !== false;
}
?>