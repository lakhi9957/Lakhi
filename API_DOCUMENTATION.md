# Tuition Center Management System - API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Installation & Setup](#installation--setup)
3. [Error Resolution](#error-resolution)
4. [API Reference](#api-reference)
5. [Database Schema](#database-schema)
6. [Usage Examples](#usage-examples)
7. [Troubleshooting](#troubleshooting)

## Overview

The Tuition Center Management System is a desktop GUI application built with Python's Tkinter library for managing student records. It provides a simple interface for adding, updating, deleting, and searching student information stored in a SQLite database.

### Features
- ✅ Add new students
- ✅ Update existing student information
- ✅ Delete students with confirmation
- ✅ Search functionality across all fields
- ✅ Professional GUI with table view
- ✅ Error handling and validation
- ✅ Proper database connection management

### System Requirements
- Python 3.6+
- tkinter (python3-tk package)
- sqlite3 (included with Python)

## Installation & Setup

### 1. Install Required Dependencies

#### On Ubuntu/Debian:
```bash
sudo apt update
sudo apt install python3-tk
```

#### On CentOS/RHEL:
```bash
sudo yum install tkinter
# or for newer versions:
sudo dnf install python3-tkinter
```

#### On Windows:
tkinter comes pre-installed with Python from python.org

#### On macOS:
```bash
# If using Homebrew
brew install python-tk
```

### 2. Run the Application
```bash
python3 tuition_center_app.py
```

## Error Resolution

### Common Error: `ModuleNotFoundError: No module named 'tkinter'`

**Solution:**
```bash
# Ubuntu/Debian
sudo apt install python3-tk

# Test installation
python3 -c "import tkinter; print('tkinter installed successfully')"
```

### Other Common Issues:

1. **Database Permission Error**
   - Ensure write permissions in the application directory
   - Run with appropriate user permissions

2. **GUI Display Issues**
   - For headless servers, install X11 forwarding or use VNC
   - Set DISPLAY environment variable if using SSH

## API Reference

### Class: `TuitionCenterApp`

The main application class that handles the GUI and database operations.

#### Constructor

```python
def __init__(self, master):
    """
    Initialize the application.
    
    Args:
        master (tk.Tk): The root Tkinter window
        
    Raises:
        Exception: If database initialization fails
    """
```

#### Database Methods

##### `setup_database()`
```python
def setup_database(self):
    """
    Set up the SQLite database connection and create tables if they don't exist.
    
    Raises:
        sqlite3.Error: If database connection or table creation fails
    """
```

##### `add_student()`
```python
def add_student(self):
    """
    Add a new student to the database.
    
    Validates input fields and inserts a new student record.
    Shows success/error messages to the user.
    
    Validation:
        - All fields (name, class, phone) must be non-empty
        - Strips whitespace from inputs
    
    Side Effects:
        - Clears input fields on success
        - Refreshes the student list display
        - Shows messagebox with result
    """
```

##### `update_student()`
```python
def update_student(self):
    """
    Update the selected student's information.
    
    Requires:
        - A student must be selected in the treeview
        - All input fields must be filled
    
    Side Effects:
        - Updates database record
        - Clears input fields on success
        - Refreshes the student list display
    """
```

##### `delete_student()`
```python
def delete_student(self):
    """
    Delete the selected student from the database.
    
    Requires:
        - A student must be selected in the treeview
        - User confirmation via dialog
    
    Side Effects:
        - Removes record from database
        - Refreshes the student list display
        - Shows confirmation dialog
    """
```

##### `load_students(search_term="")`
```python
def load_students(self, search_term=""):
    """
    Load students from the database into the treeview.
    
    Args:
        search_term (str, optional): Filter students by name, class, or phone
    
    Side Effects:
        - Clears and repopulates the treeview
        - Orders results by student name
    """
```

#### GUI Event Handlers

##### `on_select(event)`
```python
def on_select(self, event):
    """
    Handle student selection in the treeview.
    
    Args:
        event: Tkinter selection event
    
    Side Effects:
        - Populates input fields with selected student data
        - Enables update/delete operations
    """
```

##### `on_search(*args)`
```python
def on_search(self, *args):
    """
    Handle search functionality with real-time filtering.
    
    Args:
        *args: Variable arguments from StringVar trace
    
    Side Effects:
        - Filters displayed students based on search term
        - Updates treeview in real-time
    """
```

##### `clear_fields()`
```python
def clear_fields(self):
    """
    Clear all input fields.
    
    Side Effects:
        - Empties name, class, and phone entry widgets
    """
```

##### `on_closing()`
```python
def on_closing(self):
    """
    Handle application closing - properly close database connection.
    
    Side Effects:
        - Closes database connection safely
        - Destroys the main window
    """
```

## Database Schema

### Table: `students`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | INTEGER | PRIMARY KEY, AUTOINCREMENT | Unique student identifier |
| `name` | TEXT | NOT NULL | Student's full name |
| `class_name` | TEXT | NOT NULL | Student's class/grade |
| `phone` | TEXT | NOT NULL | Student's phone number |
| `created_at` | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Record creation time |

### SQL Schema
```sql
CREATE TABLE IF NOT EXISTS students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    class_name TEXT NOT NULL,
    phone TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Usage Examples

### 1. Basic Usage
```python
import tkinter as tk
from tuition_center_app_improved import TuitionCenterApp

# Create main window
root = tk.Tk()

# Initialize application
app = TuitionCenterApp(root)

# Start the GUI event loop
root.mainloop()
```

### 2. Adding a Student
1. Fill in the "Student Name" field
2. Enter the class/grade
3. Provide phone number
4. Click "Add Student"

### 3. Updating a Student
1. Click on a student in the list to select them
2. Modify the fields as needed
3. Click "Update Student"

### 4. Searching Students
1. Type in the search box
2. Results filter automatically as you type
3. Search works across name, class, and phone fields

### 5. Deleting a Student
1. Select a student from the list
2. Click "Delete Student"
3. Confirm the deletion in the dialog

## Troubleshooting

### GUI Not Displaying

**Problem**: Application runs but no window appears
**Solution**: 
```bash
# Check if X11 is available
echo $DISPLAY

# For SSH connections, use X11 forwarding
ssh -X username@hostname

# Alternative: Use VNC or remote desktop
```

### Database Locked Error

**Problem**: `database is locked` error
**Solution**:
```bash
# Check for existing connections
lsof tuition_center.db

# Kill any hanging processes
pkill -f tuition_center_app.py

# Delete lock file if exists
rm -f tuition_center.db-lock
```

### Performance Issues

**Problem**: Slow loading with many records
**Solution**:
- Consider implementing pagination for large datasets
- Add database indexes:
```sql
CREATE INDEX idx_student_name ON students(name);
CREATE INDEX idx_student_class ON students(class_name);
```

### Search Not Working

**Problem**: Search doesn't return expected results
**Solution**:
- Search is case-insensitive and uses LIKE operator
- Uses partial matching (substring search)
- Searches across name, class, and phone fields

## Advanced Configuration

### Custom Database Path
```python
# Modify the db_path in __init__
self.db_path = "/custom/path/tuition_center.db"
```

### Window Customization
```python
# Modify window properties in __init__
self.master.geometry("800x600")  # Larger window
self.master.resizable(True, True)  # Allow resizing
```

### Adding New Fields

To add new student fields:

1. **Update Database Schema**:
```sql
ALTER TABLE students ADD COLUMN email TEXT;
ALTER TABLE students ADD COLUMN address TEXT;
```

2. **Update GUI**:
```python
# Add new entry widgets in create_widgets()
self.entry_email = tk.Entry(input_frame)
```

3. **Update Database Methods**:
```python
# Modify INSERT and UPDATE queries
self.cursor.execute(
    "INSERT INTO students (name, class_name, phone, email) VALUES (?, ?, ?, ?)",
    (name, class_name, phone, email)
)
```

## Security Considerations

1. **SQL Injection Prevention**: Uses parameterized queries
2. **Input Validation**: Validates all user inputs
3. **Error Handling**: Graceful error handling prevents crashes
4. **Database Connection**: Proper connection management prevents locks

## Performance Tips

1. **Database Indexing**: Add indexes for frequently searched fields
2. **Connection Pooling**: For multiple instances, consider connection pooling
3. **Batch Operations**: For bulk inserts, use executemany()
4. **Memory Management**: Application properly closes database connections

## License

This application is provided as-is for educational purposes. Feel free to modify and distribute according to your needs.

---

**Last Updated**: 2024
**Version**: 1.1
**Author**: Development Team