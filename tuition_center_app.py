import tkinter as tk
from tkinter import messagebox
import sqlite3

# Connect to SQLite database
conn = sqlite3.connect("tuition_center.db")
cursor = conn.cursor()

# Create table if not exists
cursor.execute("""
    CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        class_name TEXT,
        phone TEXT
    )
""")
conn.commit()

# Add student to database
def add_student():
    name = entry_name.get()
    class_name = entry_class.get()
    phone = entry_phone.get()
    if name and class_name and phone:
        cursor.execute("INSERT INTO students (name, class_name, phone) VALUES (?, ?, ?)", (name, class_name, phone))
        conn.commit()
        messagebox.showinfo("Success", "Student added!")
        clear_fields()
        view_students()
    else:
        messagebox.showwarning("Input Error", "All fields are required.")

# View all students
def view_students():
    listbox.delete(0, tk.END)
    cursor.execute("SELECT * FROM students")
    for row in cursor.fetchall():
        listbox.insert(tk.END, row)

# Delete selected student
def delete_student():
    selected = listbox.curselection()
    if selected:
        student = listbox.get(selected)
        cursor.execute("DELETE FROM students WHERE id=?", (student[0],))
        conn.commit()
        messagebox.showinfo("Deleted", "Student deleted.")
        view_students()
    else:
        messagebox.showwarning("Select", "Select a student to delete.")

# Clear input fields
def clear_fields():
    entry_name.delete(0, tk.END)
    entry_class.delete(0, tk.END)
    entry_phone.delete(0, tk.END)

# GUI Setup
root = tk.Tk()
root.title("Tuition Center App")
root.geometry("500x450")

tk.Label(root, text="Name").pack()
entry_name = tk.Entry(root)
entry_name.pack()

tk.Label(root, text="Class").pack()
entry_class = tk.Entry(root)
entry_class.pack()

tk.Label(root, text="Phone").pack()
entry_phone = tk.Entry(root)
entry_phone.pack()

tk.Button(root, text="Add Student", command=add_student).pack(pady=5)
tk.Button(root, text="Delete Selected", command=delete_student).pack(pady=5)
tk.Button(root, text="View All Students", command=view_students).pack(pady=5)

listbox = tk.Listbox(root, width=50)
listbox.pack(pady=10)

# Start with student list
view_students()

root.mainloop()

# Close DB when done (optional)
conn.close()