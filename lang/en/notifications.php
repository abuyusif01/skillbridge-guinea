<?php

return [
    'mail' => [
        'greeting' => 'Hello :name,',
        'salutation' => 'The SkillBridge Guinea team',
    ],

    'password_reset' => [
        'subject' => 'Reset your password',
        'greeting' => 'Hello :name,',
        'intro' => 'You are receiving this email because a password reset was requested for your SkillBridge Guinea account.',
        'action' => 'Choose a new password',
        'expiry' => 'This link will expire in :count minutes.',
        'ignore' => 'If you did not request a password reset, no further action is required.',
        'salutation' => 'The SkillBridge Guinea team',
    ],

    'booking_request' => [
        'title' => 'New booking request',
        'message' => ':customer sent you a :trade request for :date.',
        'description' => 'Description: :description',
        'action' => 'View the request',
    ],

    'booking' => [
        'accepted' => [
            'title' => 'Your request was accepted',
            'message' => ':worker accepted your :trade request. Contact them to agree on the details.',
        ],
        'declined' => [
            'title' => 'Your request was declined',
            'message' => ':worker is not available for your :trade request. You can search for another professional.',
        ],
        'completed' => [
            'title' => 'Work completed',
            'message' => ':worker marked your :trade job as completed. Leave a review to help other customers.',
        ],
        'updated' => [
            'title' => 'Your booking was updated',
            'message' => 'The status of your :trade request has changed.',
        ],
        'action' => 'View my booking',
    ],

    'booking_cancelled' => [
        'title' => 'Request cancelled by the customer',
        'message' => ':customer cancelled their :trade request for :date.',
        'action' => 'View my dashboard',
    ],

    'review_received' => [
        'title' => 'New review received',
        'message' => ':customer rated you :rating/5. “:comment”',
        'no_comment' => 'No written comment.',
        'action' => 'View my profile',
    ],
];
