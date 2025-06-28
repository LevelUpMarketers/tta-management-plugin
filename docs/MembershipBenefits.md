# Membership Levels and Event Messages

This plugin supports three membership levels:

| Level   | Description |
|---------|-------------|
| Free    | Basic account with no recurring fee. Can attend Open events. |
| Basic   | Paid membership that unlocks events requiring at least a Basic membership. |
| Premium | Highest tier with additional discounts and perks. Required for Premium events. |

## Ticket Context Messages

Depending on the event type and visitor status, the **Get Your Tickets Now** section displays different guidance and may disable ticket controls. The table below summarizes the messaging logic.

| Event Type | Visitor Status | Message Summary | Controls Disabled? |
|------------|----------------|-----------------|-------------------|
| Open       | Not logged in  | "Open Event - Log in here for the best experience. Don't have an account? Create one here!" | No |
| Open       | Free member    | "Open Event - Thanks for being a Member, NAME! Did you know that by upgrading your membership, you'll receive discounts and other perks? Upgrade your membership here!" | No |
| Open       | Basic member   | "Open Event - Thanks for being a Basic Member, NAME! Did you know that by upgrading your membership to Premium, you'll receive even more discounts and perks? Click here to upgrade!" | No |
| Open       | Premium member | "Open Event - Thanks for being a Premium Member, NAME! Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? Click here for more info!" | No |
| Basic Required | Not logged in | "Basic Membership Required - Log in here to purchase tickets. Don't have an account? Create one here!" | Yes |
| Basic Required | Free member | "Basic Membership Required - Hey NAME, you'll need to upgrade to at least a Basic Membership to purchase tickets for this event. Click here to upgrade!" | Yes |
| Basic Required | Basic member | "Basic Membership Required - Thanks for being a Basic Member, NAME! Did you know that by upgrading your membership to Premium, you'll receive even more discounts and perks? Click here to upgrade!" | No |
| Basic Required | Premium member | "Basic Membership Required - Thanks for being a Premium Member, NAME! Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? Click here for more info!" | No |
| Premium Required | Not logged in | "Premium Membership Required - Log in here to purchase tickets. Don't have an account? Create one here!" | Yes |
| Premium Required | Free member | "Premium Membership Required - Hey NAME, you'll need to upgrade to a Premium Membership to purchase tickets for this event. Click here to upgrade!" | Yes |
| Premium Required | Basic member | "Premium Membership Required - Hey NAME, thanks for being a Basic Member! This event is only available to Premium Members though. Click here to upgrade to attend this event and receive even more discounts and perks!" | Yes |
| Premium Required | Premium member | "Premium Membership Required - Thanks for being a Premium Member, NAME! Did you know that by referring someone new to Trying to Adult, you can receive a referral bonus, including free events? Click here for more info!" | No |

When controls are disabled, hovering over a quantity field or the **Get Tickets** button shows a tooltip explaining the membership requirement.
Disabled controls are dimmed with an overlay rather than reduced opacity so these tooltips remain fully visible.
