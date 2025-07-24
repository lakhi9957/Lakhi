#!/usr/bin/env python3
"""
Tuition Center Management System
A simple GUI application for managing student records using Tkinter and SQLite.

Author: [Your Name]
Version: 1.1
Date: 2024
"""

import tkinter as tk
from tkinter import messagebox, ttk
import sqlite3
import os
import sys

class TuitionCenterApp:
    """Main application class for the Tuition Center Management System."""
    
    def __init__(self, master):
        """
        Initialize the application.
        
        Args:
            master: The root Tkinter window
        """
        self.master = master
        self.master.title("Tuition Center Management System")
        self.master.geometry("600x500")
        self.master.protocol("WM_DELETE_WINDOW", self.on_closing)
        
        # Initialize database
        self.db_path = "tuition_center.db"
        self.conn = None
        self.cursor = None
        
        try:
            self.setup_database()
            self.create_widgets()
            self.load_students()
        except Exception as e:
            messagebox.showerror("Initialization Error", f"Failed to initialize application: {str(e)}")
            sys.exit(1)
    
    def setup_database(self):
        """Set up the SQLite database connection and create tables if they don't exist."""
        try:
            self.conn = sqlite3.connect(self.db_path)
            self.cursor = self.conn.cursor()
            
            # Create students table if it doesn't exist
            self.cursor.execute("""
                CREATE TABLE IF NOT EXISTS students (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL,
                    class_name TEXT NOT NULL,
                    phone TEXT NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            """)
            self.conn.commit()
            
        except sqlite3.Error as e:
            raise Exception(f"Database setup failed: {str(e)}")
    
    def create_widgets(self):
        """Create and arrange all GUI widgets."""
        # Title
        title_label = tk.Label(self.master, text="Tuition Center Management System", 
                              font=("Arial", 16, "bold"))
        title_label.pack(pady=10)
        
        # Input frame
        input_frame = tk.Frame(self.master)
        input_frame.pack(pady=10, padx=20, fill=tk.X)
        
        # Name input
        tk.Label(input_frame, text="Student Name:", font=("Arial", 10)).grid(row=0, column=0, sticky=tk.W, pady=5)
        self.entry_name = tk.Entry(input_frame, width=30, font=("Arial", 10))
        self.entry_name.grid(row=0, column=1, pady=5, padx=(10, 0))
        
        # Class input
        tk.Label(input_frame, text="Class:", font=("Arial", 10)).grid(row=1, column=0, sticky=tk.W, pady=5)
        self.entry_class = tk.Entry(input_frame, width=30, font=("Arial", 10))
        self.entry_class.grid(row=1, column=1, pady=5, padx=(10, 0))
        
        # Phone input
        tk.Label(input_frame, text="Phone Number:", font=("Arial", 10)).grid(row=2, column=0, sticky=tk.W, pady=5)
        self.entry_phone = tk.Entry(input_frame, width=30, font=("Arial", 10))
        self.entry_phone.grid(row=2, column=1, pady=5, padx=(10, 0))
        
        # Button frame
        button_frame = tk.Frame(self.master)
        button_frame.pack(pady=10)
        
        tk.Button(button_frame, text="Add Student", command=self.add_student, 
                 bg="#4CAF50", fg="white", font=("Arial", 10)).pack(side=tk.LEFT, padx=5)
        tk.Button(button_frame, text="Update Student", command=self.update_student, 
                 bg="#2196F3", fg="white", font=("Arial", 10)).pack(side=tk.LEFT, padx=5)
        tk.Button(button_frame, text="Delete Student", command=self.delete_student, 
                 bg="#f44336", fg="white", font=("Arial", 10)).pack(side=tk.LEFT, padx=5)
        tk.Button(button_frame, text="Clear Fields", command=self.clear_fields, 
                 bg="#FF9800", fg="white", font=("Arial", 10)).pack(side=tk.LEFT, padx=5)
        
        # Search frame
        search_frame = tk.Frame(self.master)
        search_frame.pack(pady=10, padx=20, fill=tk.X)
        
        tk.Label(search_frame, text="Search:", font=("Arial", 10)).pack(side=tk.LEFT)
        self.search_var = tk.StringVar()
        self.search_var.trace("w", self.on_search)
        search_entry = tk.Entry(search_frame, textvariable=self.search_var, width=30, font=("Arial", 10))
        search_entry.pack(side=tk.LEFT, padx=(10, 0))
        
        # Student list (using Treeview for better display)
        list_frame = tk.Frame(self.master)
        list_frame.pack(pady=10, padx=20, fill=tk.BOTH, expand=True)
        
        # Create Treeview
        columns = ("ID", "Name", "Class", "Phone")
        self.tree = ttk.Treeview(list_frame, columns=columns, show="headings", height=12)
        
        # Define column headings and widths
        self.tree.heading("ID", text="ID")
        self.tree.heading("Name", text="Name")
        self.tree.heading("Class", text="Class")
        self.tree.heading("Phone", text="Phone")
        
        self.tree.column("ID", width=50)
        self.tree.column("Name", width=200)
        self.tree.column("Class", width=100)
        self.tree.column("Phone", width=150)
        
        # Scrollbar for the treeview
        scrollbar = ttk.Scrollbar(list_frame, orient=tk.VERTICAL, command=self.tree.yview)
        self.tree.configure(yscrollcommand=scrollbar.set)
        
        # Pack treeview and scrollbar
        self.tree.pack(side=tk.LEFT, fill=tk.BOTH, expand=True)
        scrollbar.pack(side=tk.RIGHT, fill=tk.Y)
        
        # Bind selection event
        self.tree.bind("<<TreeviewSelect>>", self.on_select)
    
    def add_student(self):
        """Add a new student to the database."""
        name = self.entry_name.get().strip()
        class_name = self.entry_class.get().strip()
        phone = self.entry_phone.get().strip()
        
        if not all([name, class_name, phone]):
            messagebox.showwarning("Input Error", "All fields are required!")
            return
        
        try:
            self.cursor.execute(
                "INSERT INTO students (name, class_name, phone) VALUES (?, ?, ?)",
                (name, class_name, phone)
            )
            self.conn.commit()
            messagebox.showinfo("Success", "Student added successfully!")
            self.clear_fields()
            self.load_students()
        except sqlite3.Error as e:
            messagebox.showerror("Database Error", f"Failed to add student: {str(e)}")
    
    def update_student(self):
        """Update the selected student's information."""
        selected_item = self.tree.selection()
        if not selected_item:
            messagebox.showwarning("Selection Error", "Please select a student to update.")
            return
        
        student_id = self.tree.item(selected_item[0])["values"][0]
        name = self.entry_name.get().strip()
        class_name = self.entry_class.get().strip()
        phone = self.entry_phone.get().strip()
        
        if not all([name, class_name, phone]):
            messagebox.showwarning("Input Error", "All fields are required!")
            return
        
        try:
            self.cursor.execute(
                "UPDATE students SET name=?, class_name=?, phone=? WHERE id=?",
                (name, class_name, phone, student_id)
            )
            self.conn.commit()
            messagebox.showinfo("Success", "Student updated successfully!")
            self.clear_fields()
            self.load_students()
        except sqlite3.Error as e:
            messagebox.showerror("Database Error", f"Failed to update student: {str(e)}")
    
    def delete_student(self):
        """Delete the selected student from the database."""
        selected_item = self.tree.selection()
        if not selected_item:
            messagebox.showwarning("Selection Error", "Please select a student to delete.")
            return
        
        student_data = self.tree.item(selected_item[0])["values"]
        student_name = student_data[1]
        
        if messagebox.askyesno("Confirm Delete", f"Are you sure you want to delete {student_name}?"):
            try:
                student_id = student_data[0]
                self.cursor.execute("DELETE FROM students WHERE id=?", (student_id,))
                self.conn.commit()
                messagebox.showinfo("Success", "Student deleted successfully!")
                self.load_students()
            except sqlite3.Error as e:
                messagebox.showerror("Database Error", f"Failed to delete student: {str(e)}")
    
    def clear_fields(self):
        """Clear all input fields."""
        self.entry_name.delete(0, tk.END)
        self.entry_class.delete(0, tk.END)
        self.entry_phone.delete(0, tk.END)
    
    def load_students(self, search_term=""):
        """Load students from the database into the treeview."""
        # Clear existing items
        for item in self.tree.get_children():
            self.tree.delete(item)
        
        try:
            if search_term:
                # Search in name, class, or phone
                self.cursor.execute("""
                    SELECT id, name, class_name, phone FROM students 
                    WHERE name LIKE ? OR class_name LIKE ? OR phone LIKE ?
                    ORDER BY name
                """, (f"%{search_term}%", f"%{search_term}%", f"%{search_term}%"))
            else:
                self.cursor.execute("SELECT id, name, class_name, phone FROM students ORDER BY name")
            
            students = self.cursor.fetchall()
            
            for student in students:
                self.tree.insert("", tk.END, values=student)
                
        except sqlite3.Error as e:
            messagebox.showerror("Database Error", f"Failed to load students: {str(e)}")
    
    def on_select(self, event):
        """Handle student selection in the treeview."""
        selected_item = self.tree.selection()
        if selected_item:
            student_data = self.tree.item(selected_item[0])["values"]
            # Fill the form with selected student data
            self.clear_fields()
            self.entry_name.insert(0, student_data[1])
            self.entry_class.insert(0, student_data[2])
            self.entry_phone.insert(0, student_data[3])
    
    def on_search(self, *args):
        """Handle search functionality."""
        search_term = self.search_var.get()
        self.load_students(search_term)
    
    def on_closing(self):
        """Handle application closing - properly close database connection."""
        try:
            if self.conn:
                self.conn.close()
        except sqlite3.Error:
            pass  # Ignore errors when closing
        finally:
            self.master.destroy()

def main():
    """Main function to run the application."""
    try:
        root = tk.Tk()
        app = TuitionCenterApp(root)
        root.mainloop()
    except Exception as e:
        print(f"Application failed to start: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()