<?php
// Gestion des rappels SMS (exemple avec l'API Twilio, à personnaliser avec tes identifiants)
function ib_send_sms($to, $message) {
    $sid = get_option('ib_twilio_sid');
    $token = get_option('ib_twilio_token');
    $from = get_option('ib_twilio_from');
    if (!$sid || !$token || !$from) return;
    $url = 'https://api.twilio.com/2010-04-01/Accounts/'.$sid.'/Messages.json';
    $data = [
        'From' => $from,
        'To' => $to,
        'Body' => $message
    ];
    $args = [
        'body' => $data,
        'headers' => [
            'Authorization' => 'Basic ' . base64_encode($sid . ':' . $token)
        ]
    ];
    wp_remote_post($url, $args);
}
