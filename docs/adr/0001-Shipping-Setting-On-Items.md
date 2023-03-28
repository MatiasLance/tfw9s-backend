# 1. Shipping Setting on Items

Date: 2023-03-28

## Status

Accepted

## Context

Client:
I don't quite understand the 'SHIPPING' settings. I am able to open a new one (e.g. Items below 10Kg) but can I then allocate different Shipping Settings for specific products? I do have products that are below 10Kg, Below 15Kg and Below 20Kg. Happy to create all 3 settings but I noticed that when I edit the shipping for a specific item, it also changes ALL other items shipping.

The person is asking for clarification on how to use the Shipping Settings. They have created different settings based on weight limits for items, but they want to know if they can assign different shipping settings for specific products. However, when they try to edit the shipping for one item, it changes the shipping for all other items as well, which they find confusing.

## Decision

As checked, there is no shipping setting fetched over the backend upon clicking Edit Product.

We need to include the selectedShippingSettingId field on the backend. Add as payload parameter upon creating the Item on the frontend.

Applying the command `php artisan make:migration AddShippingIdOnItemsTable` will append the `selected_shippingid` column to the Items Table.


## Consequences

User can now select a different shipping setting per Item.
