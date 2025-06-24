<?php
$members = [];
for ($i = 1; $i <= 10; $i++) {
    $members[] = [
        'wpuserid'              => $i,
        'first_name'            => 'Member' . $i,
        'last_name'             => 'Example',
        'email'                 => 'sample_member_' . $i . '@example.com',
        'profileimgid'          => 0,
        'joined_at'             => date('Y-m-d H:i:s', strtotime('-' . ($i * 3) . ' days')),
        'address'               => $i . ' Main St - - Richmond - VA - 2323' . $i,
        'phone'                 => '555-000-' . sprintf('%04d', $i),
        'dob'                   => '1990-01-0' . (($i % 9) + 1),
        'member_type'           => 'member',
        'membership_level'      => ($i % 3 === 0) ? 'premium' : (($i % 2) ? 'basic' : 'free'),
        'facebook'              => 'https://facebook.com/member' . $i,
        'linkedin'              => 'https://linkedin.com/in/member' . $i,
        'instagram'             => 'https://instagram.com/member' . $i,
        'twitter'               => 'https://twitter.com/member' . $i,
        'biography'             => 'Sample biography for member ' . $i,
        'notes'                 => '',
        'interests'             => 'Music, Hiking',
        'opt_in_marketing_email'=> 1,
        'opt_in_marketing_sms'  => 0,
        'opt_in_event_email'    => 1,
        'opt_in_event_sms'      => 0,
        'hide_event_attendance' => 0,
    ];
}
return $members;
