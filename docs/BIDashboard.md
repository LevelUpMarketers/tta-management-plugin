# TTA BI Dashboard

The **TTA BI Dashboard** appears as its own menu item in the WordPress admin and surfaces business intelligence metrics for managers. Data is fetched via the `tta_bi_data` AJAX action and rendered with D3.js. Charts are organised into **Event Sales & Tickets**, **Membership Metrics** and **Predictive Analytics** tabs. Most charts include timeframe selectors for the last month, last 3 months, 6, 12 or 24 months. The predictive tab instead offers forecasts 1 week, 1 month, 3 months or 6 months into the future. Changing a selector reloads only that chart without a full page refresh. Optional *Compare previous period* checkboxes overlay data from the prior timeframe. Charts feature hover tooltips displaying numbers and dollar amounts with helpful grid lines and animations.

## Available Charts

The dashboard now displays multiple sections:

1. **Subscription Status** – bar chart of active, cancelled and payment-problem subscriptions.
2. **New Member Signups** – line chart showing signups per month for the selected timeframe.
3. **Monthly Revenue** – line chart of total revenue from all transactions for the chosen period.
4. **Ticket Sales Per Year** – bar chart summarising yearly event revenue.
5. **Average Tickets per Event** – monthly average tickets sold per event for the current year.
6. **Cumulative Revenue** – running total of all revenue for the selected period.
7. **Membership Levels** – pie chart of members by current level.
8. **Monthly Churn Rate** – percentage of members who cancelled each month.
9. **Predicted Revenue** – simple forecast for next month based on the recent average.

The AJAX response includes arrays for each dataset:

```json
{
  "subs": [{"label":"Active","count":10}],
  "signups": [{"label":"Jul","count":5}],
  "revenue": [{"label":"2025-01","amount":1200}],
  "ticket_sales": [{"label":"2025","amount":5000}],
  "avg_tickets": [{"label":"01","count":15}],
  "by_level": [{"label":"premium","count":50}],
  "cumulative": [{"label":"2025-01","amount":1200}],
  "churn": [{"label":"2025-02","rate":2.3}],
  "prediction": {"label":"2025-08","amount":1500}
}
```
If a comparison is requested, additional arrays like `revenue_prev` or `signups_prev` contain data for the preceding period.

Charts are drawn asynchronously without page reloads. Additional metrics can be added by extending the AJAX handler and appending new chart containers in the view.
Each chart features helpful hover tooltips and grid lines. Bar charts animate when loading, line charts show circular markers at each point, and pie charts include a colour-coded legend.
