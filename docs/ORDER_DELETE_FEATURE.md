# Order Permanent Delete Feature

## Overview
Added a permanent delete functionality for orders in the admin panel with comprehensive warnings and audit logging.

## Features Implemented

### 1. Delete Button
- **Location**: Admin Orders page (`admin/orders.php`)
- **Icon**: Red trash icon (🗑️) next to each order
- **Availability**: Available for ALL orders (unlike cancel which has restrictions)

### 2. Confirmation Modal
When an admin clicks the delete button, a warning modal appears with:
- ⚠️ **Critical Action Warning** - Clearly labeled as a critical operation
- **Order ID Display** - Shows which order is about to be deleted
- **Detailed Warnings**:
  - Action cannot be undone
  - All order data will be permanently removed
  - Payment records and transaction logs will be deleted
  - Customer loses access to order information
  - Deletion will be logged for audit purposes
- **Recommendation**: Suggests canceling instead of deleting to maintain records

### 3. Delete Handler (`admin/delete_order.php`)
The backend handler performs:
- **Authentication Check** - Ensures only authorized admins can delete
- **Transaction Safety** - Uses MySQL transactions to ensure data integrity
- **Cascading Deletion** in proper order:
  1. Payment logs (linked to payments)
  2. Payments (linked to order)
  3. Order items
  4. Main order record
- **Audit Logging** - Records deletion in `admin_logs` table
- **Error Handling** - Rollback on failure, JSON response to frontend

### 4. Admin Logs Table
New `admin_logs` table tracks:
- Admin who performed the action
- Action type (e.g., 'order_deleted')
- Detailed information about what was deleted
- Timestamp of action
- Optional: IP address and user agent

**Log Format Example:**
```
Order #ORD-2025-12345 permanently deleted by admin.
Customer: John Doe (john@example.com)
Amount: ₱1,234.56
Status: Pending
```

### 5. Visual Feedback
- **Loading State** - Button shows spinner while processing
- **Success Notification** - Green alert appears on successful deletion
- **Error Notification** - Red alert appears if deletion fails
- **Auto-Reload** - Page refreshes after successful deletion to show updated list

## UI/UX Features

### Warning Indicators
- 🚨 Red danger-themed modal header
- ⚠️ Yellow warning alert box with icon
- 💡 Blue informational alert with recommendation
- Clear visual hierarchy emphasizing severity

### Button States
- **Default**: Red background with trash icon
- **Loading**: Disabled with spinner animation
- **Cancel**: Gray button to abort action

### Notifications
- Fixed position (top-right corner)
- Auto-dismiss after 5 seconds
- Manual dismiss available
- Color-coded (green for success, red for error)

## Security & Safety

### Multi-Layer Protection
1. **Backend Authentication** - Requires admin session
2. **Confirmation Modal** - Requires explicit user confirmation
3. **Transaction Safety** - Rollback on any error
4. **Audit Trail** - All deletions logged with details
5. **JSON Response** - Secure API communication

### Audit Compliance
Every deletion creates a permanent log entry containing:
- Who deleted it (admin email/ID)
- When it was deleted (timestamp)
- What was deleted (order details)
- Why context might be relevant (order status, amount)

## Database Schema

### Admin Logs Table Structure
```sql
CREATE TABLE admin_logs (
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  admin_id INT(11) DEFAULT NULL,
  admin_email VARCHAR(255) NOT NULL,
  action VARCHAR(100) NOT NULL,
  details TEXT DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_admin_id (admin_id),
  INDEX idx_action (action),
  INDEX idx_created_at (created_at)
);
```

## Installation

### 1. Run the SQL Migration
```bash
# Option A: Using the separate file
mysql -u root -p peakph_db < admin/create_admin_logs_table.sql

# Option B: Re-run complete setup (safe - uses IF NOT EXISTS)
mysql -u root -p < database_setup.sql
```

### 2. Verify Installation
```sql
USE peakph_db;
SHOW TABLES LIKE 'admin_logs';
DESCRIBE admin_logs;
```

## Usage

### For Admins
1. Navigate to **Admin > Orders**
2. Find the order to delete
3. Click the **red trash icon** (🗑️)
4. Read the warning carefully
5. Click **"Yes, Permanently Delete"** to confirm
6. Wait for success notification
7. Page reloads with order removed

### For Developers
The delete endpoint accepts POST requests:
```javascript
fetch('delete_order.php', {
  method: 'POST',
  body: 'order_id=' + orderId
})
.then(response => response.json())
.then(data => {
  // data.success (boolean)
  // data.message (string)
  // data.order_id (string, if successful)
});
```

## Best Practices

### When to Delete
- ✅ Test/dummy orders
- ✅ Fraudulent orders after investigation complete
- ✅ Duplicate orders (keep one, delete duplicates)
- ✅ Data cleanup during maintenance

### When NOT to Delete
- ❌ Cancelled orders (use Cancel instead)
- ❌ Refunded orders (keep for records)
- ❌ Disputed orders (keep for investigation)
- ❌ Completed orders (keep for history)

**Recommendation**: Use the "Cancel Order" function instead of delete to maintain historical records.

## Troubleshooting

### Delete Button Not Working
1. Check browser console for JavaScript errors
2. Verify `delete_order.php` exists in admin folder
3. Ensure MySQL user has DELETE permissions
4. Check admin authentication is active

### Modal Not Appearing
1. Verify Bootstrap JS is loaded
2. Check for JavaScript conflicts
3. Ensure modal HTML is present in page
4. Open browser console for errors

### Logs Not Being Created
1. Check if `admin_logs` table exists
2. Verify INSERT permissions on admin_logs table
3. Check `$_SESSION['admin_email']` is set
4. Review error logs for SQL errors

## Files Modified/Created

### New Files
- `admin/delete_order.php` - Backend delete handler
- `admin/create_admin_logs_table.sql` - Table migration script
- `docs/ORDER_DELETE_FEATURE.md` - This documentation

### Modified Files
- `admin/orders.php` - Added delete button and modal UI
- `database_setup.sql` - Added admin_logs table creation

## Future Enhancements

### Possible Additions
- [ ] Restore deleted orders from backup
- [ ] Bulk delete with multi-select
- [ ] Delete filters (date range, status)
- [ ] Email notification to customer on delete
- [ ] Admin logs viewer interface
- [ ] Export logs to CSV/PDF
- [ ] Soft delete option (mark as deleted, don't remove)
- [ ] IP address tracking in logs
- [ ] Two-factor authentication for deletions

## Support
For questions or issues with this feature, contact the development team or check the admin logs table for audit information.
