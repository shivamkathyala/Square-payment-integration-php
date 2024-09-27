# Square Payment Integration in PHP

This repository provides an example of a custom Square payment integration using PHP, Square's API, and their Web SDK. The solution includes functionality for creating a payment, storing card information, and creating a customer record in Square. This integration is also embedded into a ClickFunnels page using an iframe.

## Features
- Collects payment data using Square Web SDK.
- Creates a payment using Square's API.
- Saves customer information and card details to Square.
- Logs all transactions and responses for easier debugging.
- Can be integrated into ClickFunnels through iframe embedding.

## Log Files
- The system generates log files (`logfile.log`) to track the payment and customer creation process. Logs are useful for debugging and can be found in the same directory as `payment.php`.

## Requirements
- PHP 7.4+
- Square API access (with credentials)
- A ClickFunnels account (for embedding the payment form)

