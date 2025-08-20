# Alert Bar

The Alert Bar is a plugin-controlled element fixed to the bottom of the screen and injected into the DOM via JavaScript on every frontend page. It appears only in specific situations:

1. **Tickets in Cart** – displays a countdown for the soonest expiring reservation and a button leading back to checkout.
2. **Banned Member** – displays messaging based on ban type. Indefinitely banned members are directed to the contact page, while re-entry and timed bans show a link that adds the Re-Entry Ticket to the cart. Timed bans also state the number of weeks remaining and note that purchasing the ticket lifts the ban early.

Markup and assets are injected solely by the plugin and do not rely on theme templates.
