<?php
return [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'authorize_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'profile_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scope' => 'openid email profile',
    ],
    'facebook' => [
        'client_id' => getenv('FACEBOOK_CLIENT_ID') ?: '',
        'client_secret' => getenv('FACEBOOK_CLIENT_SECRET') ?: '',
        'authorize_url' => 'https://www.facebook.com/v19.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v19.0/oauth/access_token',
        'profile_url' => 'https://graph.facebook.com/me?fields=id,email,first_name,last_name',
        'scope' => 'email,public_profile',
    ],
];
