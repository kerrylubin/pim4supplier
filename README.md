# PIM 4 Suppliers

Product Information Management system that will synchronize Products with Akeneo.

This system will give the opportunities for Suppliers to add their products in the database.

## MAIN FEATURES
- Add / Import products from Suppliers
- Overview the products from Suppliers
- Edit products from suppliers
- Approve products from suppliers to be synched to Main PIM (Akeneo)
- Import schematically products from suppliers 
- Export schematically products to Akeneo

## USER ROLES
- `super_admin` 
- `supplier_admin`
- `supplier_user`

### super_admin
This is the Super Admin user that will be able to do anything in the system.
This user has the opportunity to do the following:
- create / edit / remove `Suppliers` *
- create / edit / remove `super_admins`
- create / edit / remove `supplier_admins`
- create / edit / remove `supplier_users`
- create / edit / remove `Products`
- create / edit / remove `Categories` *
- create / edit / remove `Mapping profiles`
- create / edit / remove `Product attributes` *
- `export products manually`

### supplier_admin
The supplier admin is the main account of the Supplier. Every supplier has the opportunity to do the following:
- edit current `Supplier` *
- create / edit / remove `supplier_users`
- create / edit / remove `Products`
- create / edit / remove `Mapping profile`
- `import products manually`

### supplier_user
The supplier admin is the main account of the Supplier. Every supplier has the opportunity to do the following:
- read current `Supplier` *
- create / edit / remove `Products`
- create / edit / remove `Mapping profile`
- `import products manually`
