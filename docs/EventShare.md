# Event Sharing

Event pages feature simple social sharing buttons for Facebook and Instagram.
The icons appear in the hero section below the event meta list.
Clicking an icon opens a small share window with the event URL and title
already populated.

JavaScript for this behaviour lives in `assets/js/frontend/share/event-share.js`.
It is enqueued only when viewing the Event Page template via `TTA_Assets`.
The script uses `window.open()` to launch a 600x600 popup pointed at the
appropriate sharing URL.
