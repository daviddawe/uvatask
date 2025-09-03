# uvatask Project

## Setup & Run
To run this project, you can use the built-in PHP server. Navigate to the project's public directory and execute the following command:

```
php -S localhost:8000 router.php
```

This will start the server, and you can access the application at `http://localhost:8000`.
The `index.html` file is located in the `public` directory.

## Design Decisions
- **JSON vs SQLite**: JSON was chosen for simplicity and speed of development. It allows for quick reads and writes without the overhead of a database management system.
- **Idempotency on POST /pay**: The implementation checks if the invoice is already marked as paid. If so, it returns a 200 status with `already_paid: true`. This ensures that repeated requests do not change the state unexpectedly.
- **Error Handling**: The API returns appropriate HTTP status codes for different error scenarios, such as 404 for nonexistent IDs, 400 for bad JSON, and 405 for wrong methods.

## Data Structure
The seed data for invoices is stored in `data/invoices.json`. This file contains an array of invoice objects, each with fields such as id, customer, amount, currency, status, due_date, updated_at and deleted_at. The data is updated directly in this JSON file.

## Limitations & TODOs
- **Validation**: Input validation is minimal, stronger checks are needed in production.
- **Concurrency**: The current implementation does not handle concurrent writes robustly. Future improvements could include using a database or implementing a locking mechanism.
- **Security**: There is no CSRF protection or strong validation in place. These should be considered for production use.
- **Testing**: Unit tests are included, but only for the pay method (pay_test.php). Additional tests for all API endpoints would improve reliability.


## Useful tips
- To quickly test paying an invoice from the command line (PowerShell):
```
Invoke-WebRequest -Uri "http://localhost:8000/api/invoices/1/pay" -Method POST
```
- To run the included unit test for the pay method (default targets id 1):
``` 
php tests/pay_test.php <id>
```

## Time Spent
Approximately 7-8 hours were spent on this project, including planning, coding, and testing.