<?php

return [
    'api_key' => getenv('RESEND_API_KEY') ?: 're_Fh6X1rKo_JzjtWaAfUfRiEQs5HHxE4VsV',
    'from' => getenv('RESEND_FROM_EMAIL') ?: 'SolarPower Energy Corporation <solar@solarpower.com.ph>',
    'reply_to' => getenv('RESEND_REPLY_TO') ?: 'solar@solarpower.com.ph',
];
