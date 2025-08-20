<?php
$tickets = [];
for ($i = 1; $i <= 24; $i++) {
    $tickets[] = [
        'event_ute_id'        => 'sample_event_' . $i,
        'event_name'          => 'Sample Event ' . $i,
        'ticket_name'         => 'General Admission',
        'waitlist_id'         => 0,
        'ticketlimit'         => 50,
        'memberlimit'         => ($i % 3 === 0) ? 1 : 2,
        'baseeventcost'        => 20.00,
        'discountedmembercost' => 15.00,
        'premiummembercost'    => 10.00,
    ];
}
return $tickets;
