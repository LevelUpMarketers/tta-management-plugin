# TTA BI Dashboard

The **TTA BI Dashboard** appears as its own menu item in the WordPress admin. The existing Events, Members, and Predictive Analytics tabs have been intentionally cleared so the dashboards can be redesigned from scratch. The tabs remain visible, but they currently display placeholder messaging only.

## Current State

- **Event Revenue Info tab**: Lists archived events with a basic search/sort header and pagination. Rows expand accordion-style to show attendance and revenue metrics, including total signups, total attended, standard/premium member attendance (based on member records as of the event date), gross ticket sales, refunds issued, and net profit. A Monthly Overview section above the search controls aggregates the selected month’s totals across archived events, with a month selector that updates via AJAX. A comparison toggle can show side-by-side totals for the last month/quarter/year versus the current period to date.
- **Members tab**: Member Metrics (totals and estimated monthly recurring revenue) plus a Monthly Overview (signups and cancellations). Metrics are calculated from the members and member history tables, with month selection handled via AJAX and cached for performance.
- **Predictive Analytics tab**: Placeholder only while new UI and data logic are rebuilt.

No charting scripts are active for the dashboard at this time. New data sources, caching, and visualizations should be documented here as soon as the rebuild work begins.
