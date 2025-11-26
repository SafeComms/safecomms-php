# SafeComms PHP SDK

Official PHP client for the SafeComms API.

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
