<?php
$events = [];
$start  = strtotime('+1 day');
$names  = [
    'Dinner at Crawleys',
    'Roller Skating',
    "Buffet & Besties at King's Korner",
    'Museum Tour',
    'Paint Night',
    'Rooftop Drinks',
    'Trivia Tuesday',
    'Karaoke Night',
];
$venues = [
    'Crawleys Diner',
    'Rollerdome',
    "King's Korner Catering and Restaurant",
    'City Museum',
    'Arts Studio',
    'Sky Bar',
    'Corner Pub',
    'Sing Lounge',
];
$addresses = [
    '400 Sample Ave - - Richmond - Virginia - 23230',
    '500 Sample St - - Richmond - Virginia - 23231',
    '7511 Airfield Dr - - North Chesterfield - Virginia - 23237',
    '10 Museum Way - - Richmond - Virginia - 23238',
    '22 Paint Rd - - Richmond - Virginia - 23233',
    '99 Sky St - - Richmond - Virginia - 23220',
    '77 Pub Ln - - Richmond - Virginia - 23222',
    '88 Sing St - - Richmond - Virginia - 23225',
];
for ($i = 1; $i <= 24; $i++) {
    $idx   = ($i - 1) % count($names);
    $date  = date('Y-m-d', strtotime('+' . ($i * 3) . ' days', $start));
    $events[] = [
        'ute_id'               => 'sample_event_' . $i,
        'name'                 => $names[$idx] . ' #' . $i,
        'date'                 => $date,
        'baseeventcost'        => 20.00,
        'discountedmembercost' => 15.00,
        'premiummembercost'    => 10.00,
        'address'              => $addresses[$idx],
        'type'                 => ($i % 2) ? 'paid' : 'free',
        'time'                 => '18:00|20:00',
        'venuename'            => $venues[$idx],
        'venueurl'             => 'https://example.com/' . preg_replace('/[^a-z0-9]+/i','-', strtolower($venues[$idx])),
        'url2'                 => 'https://facebook.com/' . preg_replace('/[^a-z0-9]+/i','-', strtolower($venues[$idx])),
        'url3'                 => 'https://yelp.com/' . preg_replace('/[^a-z0-9]+/i','-', strtolower($venues[$idx])),
        'url4'                 => 'https://instagram.com/' . preg_replace('/[^a-z0-9]+/i','-', strtolower($venues[$idx])),
        'mainimageid'          => 0,
        'otherimageids'        => '',
        'waitlistavailable'    => ($i % 5 === 0) ? 0 : 1,
        'all_day_event'        => ($i % 3 === 0) ? 1 : 0,
        'virtual_event'        => ($i % 4 === 0) ? 1 : 0,
        'refundsavailable'     => ($i % 6 === 0) ? 0 : 1,
        'hosts'                => 'Host ' . $i,
        'volunteers'           => 'Volunteer ' . $i,
        'host_notes'           => '',
        'discountcode'         => '{"code":"SAVE' . $i . '","type":"flat","amount":2}',
        'created_at'           => date('Y-m-d H:i:s'),
        'updated_at'           => date('Y-m-d H:i:s'),
    ];
}
return $events;
