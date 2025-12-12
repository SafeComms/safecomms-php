# SafeComms PHP SDK

Official PHP client for the SafeComms API.

SafeComms is a powerful content moderation platform designed to keep your digital communities safe. It provides real-time analysis of text to detect and filter harmful content, including hate speech, harassment, and spam.

**Get Started for Free:**
We offer a generous **Free Tier** for all users, with **no credit card required**. Sign up today and start protecting your community immediately.

## Installation

Install via Composer:

```bash
composer require safecomms/safecomms-php
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use SafeComms\SafeCommsClient;

$client = new SafeCommsClient('your-api-key');

try {
    // Moderate text
    $result = $client->moderateText(
        content: 'Some text to check',
        language: 'en',
        replace: false,
        pii: false,
        replaceSeverity: null,
        moderationProfileId: 'prof_123'
    );

    print_r($result);

    // Get usage
    $usage = $client->getUsage();
    print_r($usage);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```
