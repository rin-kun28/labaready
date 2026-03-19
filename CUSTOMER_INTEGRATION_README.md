# Customer Integration Update

## Overview
The laundry system now automatically creates a new customer record in the `customers` table for each laundry transaction and links them via `customer_id`.

## Changes Made

### 1. Database Schema Update Required
**File:** `database/add_customer_id_to_laundry_list.sql`

You need to run this SQL script in phpMyAdmin to add the `customer_id` column to the `laundry_list` table:

```sql
ALTER TABLE `laundry_list` 
ADD COLUMN `customer_id` INT(11) NULL AFTER `id`,
ADD INDEX `fk_customer_id` (`customer_id`);
```

**Steps to apply:**
1. Open phpMyAdmin
2. Select your `laundry_db` database
3. Go to the SQL tab
4. Copy and paste the SQL from `add_customer_id_to_laundry_list.sql`
5. Click "Go" to execute

### 2. Code Changes
**File:** `ajax.php` - `save_laundry` action

#### For New Laundry Entries:
- Creates a new customer record in the `customers` table
- Captures the new customer's ID
- Stores the `customer_id` in the `laundry_list` table (if column exists)

#### For Editing Laundry Entries:
- Also creates a new customer record (each transaction gets a new customer entry)
- Updates the `customer_id` in the `laundry_list` table

## How It Works

### New Laundry Transaction Flow:
1. User enters customer name and phone number
2. System inserts a new record into `customers` table
3. System captures the new customer's auto-generated ID
4. System inserts laundry record with the `customer_id` linking to the customer

### Data Structure:
```
customers table:
- id (auto-increment)
- name
- phone
- email (optional)
- address (optional)
- date_created

laundry_list table:
- id (auto-increment)
- customer_id (NEW - links to customers.id)
- customer_name
- customer_number
- ... other fields
```

## Benefits

1. **Complete Customer History**: Every transaction creates a customer record, building a complete history
2. **Data Relationship**: The `customer_id` creates a proper relational link between laundry transactions and customers
3. **Future Analytics**: Can easily query all transactions for a specific customer
4. **Backward Compatible**: Code checks if `customer_id` column exists before using it

## Testing

After running the SQL migration:

1. Create a new laundry entry with customer name and phone
2. Check the `customers` table - you should see a new record
3. Check the `laundry_list` table - the `customer_id` should match the customer's ID
4. Each new laundry entry will create a new customer record

## Notes

- **Each transaction creates a new customer record** - This is by design to track every interaction
- The system is backward compatible - it will work even if you haven't added the `customer_id` column yet
- Customer name and phone are still stored in `laundry_list` for redundancy and quick access
