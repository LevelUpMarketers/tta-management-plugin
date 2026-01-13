# TTA BI Dashboard

The **TTA BI Dashboard** appears as its own menu item in the WordPress admin. The existing Events, Members, and Predictive Analytics tabs have been intentionally cleared so the dashboards can be redesigned from scratch. The tabs remain visible, but they currently display placeholder messaging only.

## Current State

- **Event Revenue Info tab**: Lists archived events with a basic search/sort header and pagination. Rows expand accordion-style to show attendance and revenue metrics, including total signups, total attended, standard/premium member attendance (based on member records as of the event date), gross ticket sales, refunds issued, and net profit. A Monthly Overview section above the search controls aggregates the selected month’s totals across archived events, with a month selector that updates via AJAX.
- **Members tab**: Placeholder only while new UI and data logic are rebuilt.
- **Predictive Analytics tab**: Placeholder only while new UI and data logic are rebuilt.

No charting scripts or AJAX handlers are active for the dashboard at this time. New data sources, caching, and visualizations should be documented here as soon as the rebuild work begins.
