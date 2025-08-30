# Homepage Shortcode

The `[tta_homepage]` shortcode renders the Trying to Adult RVA homepage layout. It includes:

- A sidebar with site stats, the next upcoming event, newest member information, current month birthdays and partner ads.
- A main content area with an introduction carousel, a 2×2 grid of upcoming events, and a 2×2 grid of recent past events sourced from both the active events table and the `tta_events_archive` table.

## Usage

Add the shortcode to any page:

```
[tta_homepage]
```

Required assets are enqueued automatically.

## Notes

- The counters animate from 0 to **65,000+** followers, **21** leadership team members and **665** events.
- "Our Next Event" links to the event, shows its image, name, date, time, address and a real-time countdown with icons.
- Event grids use landscape cards arranged in two columns by two rows.
- The intro image area cycles through community photos. Images selected under **TTA Settings → Slider Images** appear on the homepage and Become a Member page.
- The partner section mirrors the ad component used on event pages, including icons and contact details.
