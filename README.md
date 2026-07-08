# 💼 Financial Obligation and Disbursement System

A custom-built financial management system developed with CodeIgniter 4. This application manages financial obligations and disbursements, with modules for user roles, master data, and budgeting workflows including approvals, uploads, and printable reports.

## ✨ Features

### 🔐 User Management
- Manual user registration and login
- Role-based access (Admin, Encoder, Approver, etc.)
- Secure authentication with session handling

### 📚 Master Data Modules
- **Payees**: Add, update, and manage payee profiles
- **Projects**: Manage project entries with budget tracking
- **UACS**: Support for Unified Accounts Code Structure
- **Divisions**: Department and unit listings

### 💰 Budget Modules

#### Entry
- Create new obligations with complete reference tagging (project, division, UACS, payee)

#### Record List
- View and search obligation records with filters and pagination

#### Approval
- Multi-stage review and approval process
- Status tracking with comments (Pending, Approved, Rejected)

#### Uploading
- Upload CSV or Excel files to create multiple obligations at once
- Error handling and feedback for uploads

#### Printing
- Generate printable obligation reports and disbursement vouchers in PDF format

---

## 🛠️ Tech Stack

- **Backend**: PHP (CodeIgniter 4 - manually installed)
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Database**: MySQL
- **PDF Generation**: FPDF or TCPDF (depending on implementation)

---

## 🚀 Installation Guide

### Prerequisites
- PHP 8.x or newer
- MySQL Server

### Manual Setup

```bash
# 1. Clone the repository
git clone https://github.com/kylealino/fods.git
cd fods

# 2. Manually create a MySQL database
# Example: CREATE DATABASE fods;

# 3. Configure database connection in .env
# database.default.hostname = localhost
# database.default.database = financial_system
# database.default.username = your_user
# database.default.password = your_password

# 7. Run the system using a local server or your preferred environment
