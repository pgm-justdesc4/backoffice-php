# Custom Backoffice with PHP and MySQL

This project involves creating a custom backoffice for managing a **Music Record Label**. The application is built using PHP and MySQL and allows the management of artists, albums, tracks, products, orders, and customers. The backoffice uses an MVC architecture, providing a structured layout and easy database management.

## Requirements

This backoffice includes the following essential features:

1. **Database Design**
   - A database model with at least 6 tables, including one junction table to establish many-to-many relationships (e.g., artists linked to albums, albums linked to tracks).
   - An export of the database in `database.sql`.

2. **Backoffice Structure**
   - **Layout and CSS**: The backoffice uses Tailwind for a customizable layout.
   - **Dashboard**: The homepage displays 2 key statistics (e.g., total albums per year, orders per month) visualized with Chart.js.
   - **CRUD Operations**: Full CRUD (Create, Read, Update, Delete) functionality is implemented.
   - **File Upload**: One form allows file uploads (product images).
   - **Relationship Management**: Supports one-to-many relationships (e.g., linking tracks to products).

3. **Optional Enhancements**
   - Many-to-many relationships managed.
   - A file manager to view and organize uploaded files.
   - Filtering and sorting on list pages (products).

4. **Security**
   - SQL Injection Prevention: Ensures secure queries to prevent SQL injection vulnerabilities.

5. **API**
   - A public API allows retrieving data.
   - Supports data addition via API (e.g., adding new artists).

## Author
Justin Descan


[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/RN63TLFQ)
