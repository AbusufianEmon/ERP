# 🏢 Multi-Branch ERP System (Inventory & Sales Management)

## 📌 Overview

This project is a web-based Enterprise Resource Planning (ERP) system designed to manage inventory, sales, purchasing, and financial operations across multiple branches. It provides role-based access control for different organizational roles such as CEO, Branch Manager, Inventory Manager, Executive, and Accounts personnel.

The system streamlines business operations by integrating stock management, sales tracking, supplier handling, and financial records into a centralized platform.

---

## 🚀 Features

### 🔐 Authentication & Authorization

* Secure login system with session management
* Role-based dashboards:

  * CEO
  * Inventory Manager
  * Branch Manager
  * Executive
  * Accounts

---

### 📦 Inventory Management

* Product and category management
* Stock tracking per branch
* Batch/lot-based inventory system
* Faulty item tracking and loss calculation

---

### 🏬 Multi-Branch Management

* Manage multiple branches
* Branch-to-branch product transfers
* Transfer approval workflow

---

### 🛒 Sales Management

#### Direct Sales

* Customer-based sales
* Invoice generation
* Discount and due tracking

#### Corporate Sales

* Corporate client management
* Quotation system with approval
* Conversion from quotation to sales

---

### 📥 Purchasing System

* Supplier management
* Purchase order tracking
* Supplier invoice handling
* Purchase return management

---

### 💰 Financial Management

* Expense tracking by category
* Cash deposit recording
* Basic financial reporting support

---

### 👥 Customer Management

* Individual customer records
* Corporate customer profiles
* Ledger and due tracking

---

## 🗄️ Database Design

The system uses a relational database with key tables:

* `user` – user accounts and roles
* `branches` – branch information
* `product`, `category` – product catalog
* `product_stock` – inventory tracking
* `direct_sales`, `corporate_sales` – sales data
* `purchase_order`, `supplier` – procurement
* `expenses`, `cash_deposits` – financial records
* `product_transfer` – inter-branch logistics

---

## 🛠️ Technologies Used

* **Backend:** PHP
* **Database:** MySQL (MariaDB)
* **Frontend:** HTML, CSS, Bootstrap
* **JavaScript:** jQuery
* **Server:** XAMPP / Apache

---

## ⚙️ Installation

1. Clone the repository:

```bash
git clone https://github.com/your-username/erp-system.git
```

2. Move project to `htdocs` (XAMPP)

3. Import database:

* Open phpMyAdmin
* Create database `inventory`
* Import the provided `.sql` file

4. Configure database:

* Update `dbcon.php` with your credentials

5. Run project:

```
http://localhost/your-project-folder
```

---

## 🔑 Demo Credentials

| Role    | Email                                           | Password |
| ------- | ----------------------------------------------- | -------- |
| CEO     | [example@gmail.com](mailto:example@gmail.com)   | password |
| Manager | [example2@gmail.com](mailto:example2@gmail.com) | password |

---

## 📈 Future Improvements

* Password hashing and security enhancements
* REST API integration
* Reporting dashboard with charts
* Codebase refactoring (MVC architecture)
* Role-based permission granularity

---

## 📄 License

This project is for educational purposes.
