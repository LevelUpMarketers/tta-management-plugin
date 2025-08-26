# Event Sharing

Event pages feature simple social sharing buttons for Facebook and Instagram.
The icons appear in the hero section below the event meta list. Clicking an
icon opens a small share window with the exact event URL and a short message
that reads:

```
Check out this upcoming Trying To Adult event - {Event Name}, on {Event Date}, at {Event Time}
```

Facebook uses the `quote` parameter while Instagram opens its creation screen
with the caption pre-filled when possible. Each share link embeds the event
permalink and message via `data-share-url` and `data-share-message`
attributes so the JavaScript does not rely on the browser location.

The event template also outputs Open Graph tags (`og:type`, `og:title`,
`og:description`, `og:url`, `og:image`) so that Facebook previews the correct
permalink and featured image.

JavaScript for this behaviour lives in `assets/js/frontend/share/event-share.js`.
It is enqueued only when viewing the Event Page template via `TTA_Assets`.
The script uses `window.open()` to launch a 600x600 popup pointed at the
appropriate sharing URL.
