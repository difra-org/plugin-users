<?php

// main config.php configuration section example with default values

return [
    'auth' => [
        // Enable logins
        'logins' => true,
        // New users registration
        'registration' => true,
        // Ask password twice on registration
        'password2' => true,
        // Minimum login length
        'login_min' => 1,
        // Maximum login length
        'login_max' => 80,
        // Display the same error on bad login and bad password
        'single_error' => false,
        // Auto login user when account activation link clicked
        'login_on_activate' => false,
        // Enable end user license agreement acceptance
        'eula' => false,
        // Account activation method: 'none' => auto activation, 'email' => email link, 'moderate' => by moderator
        'confirmation' => 'email'
    ]
];