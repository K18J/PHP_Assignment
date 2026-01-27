<img width="1920" height="998" alt="image" src="https://github.com/user-attachments/assets/c25b569f-be7d-49c3-88fb-a34c3d2a7acf" />
<img width="1920" height="451" alt="image" src="https://github.com/user-attachments/assets/5040e25c-1cb3-435d-98ff-430c31600888" />
<img width="1903" height="864" alt="image" src="https://github.com/user-attachments/assets/769364b7-980d-4d1d-8d96-72038788c6a8" />
<img width="1920" height="517" alt="image" src="https://github.com/user-attachments/assets/e2cc331d-e1a5-444f-852f-a2a27743eef0" />


# Order Management System with Inventory

A robust PHP-based Order Management System with advanced inventory control, featuring optimistic locking, reservation management, and warehouse allocation.

## Features

- **Order Management**: Create and manage orders with multiple line items
- **Inventory Control**: Real-time inventory tracking across multiple warehouses
- **Stock Reservation**: Reserve inventory for orders with expiration handling
- **Optimistic Locking**: Prevent race conditions using version-based concurrency control
- **Multi-Warehouse Support**: Manage stock across different warehouse locations
- **Stock Transfers**: Transfer inventory between warehouses
- **Stock Adjustments**: Handle inventory adjustments with reason tracking
- **PDO Repository Pattern**: Clean architecture with repository and service layers

### Inventory Reservation

When an order is placed:
1. Inventory is reserved (not yet committed)
2. A reservation record is created with an expiration time
3. Reserved quantity is tracked separately from on-hand quantity
4. Expired reservations can be cleaned up automatically

### Stock Levels

- **quantity_on_hand**: Physical stock available
- **quantity_reserved**: Stock reserved for orders (soft lock)
- **Available**: `quantity_on_hand - quantity_reserved`


**Note**: This system demonstrates best practices including:
- Repository pattern
- Service layer architecture
- Optimistic concurrency control
- Transaction management
- PDO prepared statements
- Type safety (PHP 8.1+)
- PSR-4 autoloading
