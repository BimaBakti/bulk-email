<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Daily Email Limit
    |--------------------------------------------------------------------------
    | Maximum emails per day. Gmail allows 500, we use 400 as safety buffer.
    */
    'daily_limit' => (int) env('DAILY_EMAIL_LIMIT', 400),

    /*
    |--------------------------------------------------------------------------
    | Hourly Email Limit
    |--------------------------------------------------------------------------
    | Maximum emails per hour to avoid bursts.
    */
    'hourly_limit' => (int) env('HOURLY_EMAIL_LIMIT', 50),

    /*
    |--------------------------------------------------------------------------
    | Delay Between Emails (seconds)
    |--------------------------------------------------------------------------
    | Seconds to wait between each email send. Recommended: 10-15 seconds.
    */
    'delay_between_emails' => (int) env('DELAY_BETWEEN_EMAILS', 15),

    /*
    |--------------------------------------------------------------------------
    | Max Retry Attempts
    |--------------------------------------------------------------------------
    | Number of times to retry sending a failed email.
    */
    'max_retry_attempts' => (int) env('MAX_RETRY_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Warning Threshold (%)
    |--------------------------------------------------------------------------
    | Show warning when daily quota usage reaches this percentage.
    */
    'warning_threshold' => 80,

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    | Number of emails per batch when dispatching jobs.
    */
    'batch_size' => 10,

    /*
    |--------------------------------------------------------------------------
    | Disposable Email Domains
    |--------------------------------------------------------------------------
    | List of known disposable/temporary email domains to reject.
    */
    'disposable_domains' => [
        'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwaway.email',
        'yopmail.com', 'sharklasers.com', 'guerrillamailblock.com', 'grr.la',
        'dispostable.com', 'trashmail.com', 'mailnesia.com', '10minutemail.com',
        'temp-mail.org', 'fakeinbox.com', 'maildrop.cc',
    ],
];
