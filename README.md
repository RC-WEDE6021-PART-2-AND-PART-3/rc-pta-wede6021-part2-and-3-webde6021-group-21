# 👕 Pastimes - Vintage Clothing Marketplace
## 📖 Project Overview

**Pastimes** is a vintage clothing marketplace web application developed as a Portfolio of Evidence (POE) project for The Independent Institute of Education (IIE). 

Demostration video link: https://youtu.be/WWY--VYfZUw?si=dfj34YESDPTN8fpR

### Purpose
The platform connects buyers with sellers, providing a seamless shopping experience where users can:
- Browse and search for vintage clothing
- Purchase items securely
- Sell their own items
- Communicate with other users
- Track orders and manage purchases

---

## ✨ Features

### 👤 User Features

| Feature | Description | Status |
|---------|-------------|--------|
| **Registration** | Users can create accounts with validation | ✅ Complete |
| **Login/Logout** | Secure authentication with remember me option | ✅ Complete |
| **Product Browsing** | View all products with images and descriptions | ✅ Complete |
| **Search & Filters** | Search by name, description, and price range | ✅ Complete |
| **Product Details** | View full product information and seller details | ✅ Complete |
| **Add to Cart** | Add items to shopping cart | ✅ Complete |
| **Edit Cart** | Update quantities or remove items | ✅ Complete |
| **Checkout** | Complete purchase with order confirmation | ✅ Complete |
| **Order History** | View all past orders with tracking | ✅ Complete |
| **Product Reviews** | Rate and review purchased products | ✅ Complete |
| **Wishlist** | Save favorite items for later | ✅ Complete |
| **Messaging** | Communicate with sellers and buyers | ✅ Complete |

### 👨‍💼 Seller Features

| Feature | Description | Status |
|---------|-------------|--------|
| **Seller Dashboard** | Manage products and track sales | ✅ Complete |
| **Add Products** | Upload products with images and descriptions | ✅ Complete |
| **Edit/Delete Products** | Update or remove listings | ✅ Complete |
| **Order Management** | Update order status | ✅ Complete |
| **Sell Requests** | Submit items for admin approval | ✅ Complete |
| **Sales Statistics** | View revenue and sales data | ✅ Complete |

### 🔧 Admin Features

| Feature | Description | Status |
|---------|-------------|--------|
| **Admin Dashboard** | Overview of platform activity | ✅ Complete |
| **User Management** | Add, edit, delete, verify users | ✅ Complete |
| **Product Management** | Add, edit, delete any product | ✅ Complete |
| **Seller Verification** | Approve seller accounts | ✅ Complete |
| **Seller Requests** | Approve/reject sell requests | ✅ Complete |
| **Communication** | Send messages to all users | ✅ Complete |
| **Activity Logs** | Monitor admin actions | ✅ Complete |

---

## 🚀 Added Features

### 1. ⭐ Product Reviews & Ratings

#### Overview
Product Reviews & Ratings allow customers to share their experiences with products they have purchased. This feature builds trust within the community and helps other buyers make informed decisions.

#### How It Works
1. **Authenticated users** can leave reviews on products they've purchased
2. **Rating System**: Users rate products on a scale of 1-5 stars
3. **Review Comments**: Users can write detailed feedback about their experience
4. **Verified Purchases**: Only users who have actually bought the product can review it
5. **Average Rating**: Each product displays its average star rating on the product listing page
6. **Review Display**: All reviews are shown on the product details page, sorted by newest first

#### Benefits
- ✅ Builds trust between buyers and sellers
- ✅ Helps customers make informed purchasing decisions
- ✅ Provides feedback to sellers to improve their products
- ✅ Increases user engagement on the platform
- ✅ Creates a sense of community

#### Database Structure
```sql
tblreviews
├── review_id (Primary Key)
├── user_id (Foreign Key → tbluser)
├── clothes_id (Foreign Key → tblclothes)
├── rating (1-5)
├── comment (TEXT)
└── review_date (DATETIME)

2. ❤️ Wishlist / Favorites
Overview
The Wishlist feature allows users to save products they are interested in for future purchases. It serves as a "shopping bucket list" where users can bookmark items they like and come back to them later.

How It Works
Add to Wishlist: Users click a heart icon on any product to save it to their wishlist

View Wishlist: Users can view all saved items on a dedicated wishlist page

Remove from Wishlist: Users can remove items they no longer want

Add to Cart: Users can add wishlist items directly to their shopping cart

Availability Check: Shows if wishlist items are still in stock

Price Change Alerts: Notifies users if a wishlist item's price changes

Benefits
✅ Users can save items for later purchase

✅ Increases user return visits to the site

✅ Encourages users to make purchases

✅ Creates a personalized shopping experience

✅ Acts as a bookmarking system for favorite items

Database Structure
sql
tblwishlist
├── wishlist_id (Primary Key)
├── user_id (Foreign Key → tbluser)
├── clothes_id (Foreign Key → tblclothes)
└── added_date (DATETIME)

3. 📦 Order Tracking
Overview
Order Tracking allows customers to monitor the status of their orders from purchase to delivery. This feature provides transparency and peace of mind throughout the delivery process.

How It Works
Order Status Updates: Orders progress through defined stages (Pending → Processing → Shipped → Delivered)

Seller Updates Status: Sellers update order status as they process shipments

Timeline View: Customers see the chronological progress of their order

Estimated Delivery Date: Shows expected delivery date

Tracking Number: Sellers can add tracking numbers for courier services

Notifications: Customers receive status updates via the platform

Order Status Flow
text
📝 PENDING
   ↓
⚙️ PROCESSING (Seller preparing the item)
   ↓
📦 SHIPPED (Item has been dispatched)
   ↓
✅ DELIVERED (Item has been received)

Benefits 
✅ Customers know exactly where their order is

✅ Reduces customer queries to sellers

✅ Improves customer satisfaction

✅ Professional shopping experience

✅ Builds trust and reliability

Database Structure
sql
tblorders (Updated)
├── order_id (Primary Key)
├── user_id (Foreign Key → tbluser)
├── order_number (VARCHAR)
├── total_amount (DECIMAL)
├── order_date (DATETIME)
├── status (ENUM: pending, processing, shipped, delivered)
├── tracking_number (VARCHAR) ← NEW
├── estimated_delivery (DATE) ← NEW
└── delivery_date (DATETIME) ← 


📊 Summary Table of Added Features
Feature	Users	Main Purpose	Key Benefit
⭐ Product Reviews & Ratings	Buyers	Share and read product experiences	Build trust
❤️ Wishlist	Buyers	Save favorite items for later	Increase engagement
📦 Order Tracking	Buyers & Sellers	Track order progress	Transparency