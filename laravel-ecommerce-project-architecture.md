# Laravel E-Commerce Project Architecture

## Overview

Production-ready Laravel E-Commerce application using:

- Laravel 13
- Laravel Breeze
- Spatie Laravel Permission
- MySQL
- Laravel Queues
- Laravel Notifications
- Laravel Policies
- Service Layer Architecture

---

# Project Modules

## Authentication
- Register
- Login
- Logout
- Forgot Password
- Email Verification
- Profile Management

## User Management
- Customers
- Admins
- Managers
- Roles
- Permissions

## Catalog
- Categories
- Brands
- Products
- Product Images
- Product Variants
- Attributes

## Cart
- Guest Cart
- Customer Cart
- Cart Merge

## Checkout
- Billing Address
- Shipping Address
- Shipping Method
- Payment Method

## Orders
- Order Management
- Order Status Tracking
- Invoices
- Shipments

## Inventory
- Stock Management
- Inventory Transactions

## Marketing
- Coupons
- Wishlist
- Reviews

---

# Roles

## Super Admin
Full Access

## Admin
Manage Store

## Manager
Manage Products and Orders

## Customer
Purchase Products

---

# Permissions

product.view
product.create
product.edit
product.delete

category.view
category.create
category.edit
category.delete

brand.view
brand.create
brand.edit
brand.delete

order.view
order.update
order.cancel
order.refund

customer.view
customer.edit
customer.delete

settings.view
settings.edit

---

# Database Tables

## users
id
name
email
phone
password
status

## addresses
id
user_id
type
first_name
last_name
phone
country
state
city
postcode
address1
address2
is_default

## categories
id
parent_id
name
slug
description
image
status

## brands
id
name
slug
logo
status

## products
id
category_id
brand_id
sku
name
slug
short_description
description
price
special_price
stock
weight
status

## product_images
id
product_id
path
position

## product_variants
id
product_id
sku
size
color
price
stock

## carts
id
user_id

## cart_items
id
cart_id
product_id
variant_id
qty
price

## coupons
id
code
type
value
start_date
end_date

## orders
id
order_number
user_id
address_id
coupon_id
subtotal
discount_amount
tax_amount
shipping_amount
grand_total
payment_status
order_status

## order_items
id
order_id
product_id
variant_id
product_name
sku
price
qty
total

## payments
id
order_id
transaction_id
payment_method
amount
status

## shipments
id
order_id
tracking_number
carrier
status

## wishlists
id
user_id
product_id

## reviews
id
user_id
product_id
rating
comment

## inventory_transactions
id
product_id
type
qty
note
created_by

---

# Relationships

User hasMany Addresses

User hasMany Orders

User hasMany Reviews

User hasMany Wishlists

Category hasMany Products

Category belongsTo Parent Category

Brand hasMany Products

Product belongsTo Category

Product belongsTo Brand

Product hasMany Images

Product hasMany Variants

Cart belongsTo User

Cart hasMany Cart Items

Order belongsTo User

Order hasMany Order Items

Order hasOne Payment

Order hasOne Shipment

---

# Customer Workflow

Register
→ Verify Email
→ Login
→ Browse Products
→ Product Detail
→ Add To Cart
→ Checkout
→ Payment
→ Order Created
→ Order Tracking
→ Delivery

---

# Admin Workflow

Create Category
→ Create Brand
→ Create Product
→ Upload Images
→ Manage Inventory
→ Receive Orders
→ Process Orders
→ Ship Orders
→ Complete Orders

---

# Order Status

pending

confirmed

processing

packed

shipped

out_for_delivery

delivered

cancelled

refunded

---

# Recommended Folder Structure

app/
├── Models
├── Services
├── Repositories
├── Events
├── Listeners
├── Notifications
├── Policies
├── Jobs
├── Enums
├── Helpers
├── Traits
├── Http
│   ├── Controllers
│   │   ├── Admin
│   │   ├── Customer
│   │   └── API
│   ├── Requests
│   ├── Resources
│   └── Middleware
├── Modules
│   ├── Catalog
│   ├── Cart
│   ├── Checkout
│   ├── Order
│   ├── Inventory
│   ├── Customer
│   └── Payment

database/
├── migrations
├── seeders
└── factories

routes/
├── web.php
├── admin.php
├── api.php

resources/
├── views
├── js
├── css

tests/
├── Feature
└── Unit

---

# Future Enhancements

- Multi Vendor Marketplace
- AI Product Search
- AI Chatbot
- Product Recommendations
- Wallet System
- Loyalty Points
- Multi Warehouse
- Multi Currency
- Multi Language
- Mobile API using Sanctum
