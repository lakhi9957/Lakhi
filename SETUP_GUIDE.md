# 🚀 Quick Setup Guide - Fix Assignment Class Selection

## ⚠️ **Assignment "Select Class" Not Working?**

If your assignment page shows empty class dropdown, follow these steps:

### **1. Import Sample Data**

**Option A: Using Command Line**
```bash
mysql -u your_username -p tuition_center < sample_data.sql
```

**Option B: Using phpMyAdmin**
1. Open phpMyAdmin
2. Select `tuition_center` database
3. Go to "Import" tab
4. Choose `sample_data.sql` file
5. Click "Go"

### **2. Create Your First Data (Manual Setup)**

If you prefer to create data manually:

#### **Step 1: Add Subjects**
1. Login as admin
2. Go to **Admin Panel > Subjects**
3. Add subjects like:
   - Mathematics (MATH101)
   - Physics (PHY101)
   - Chemistry (CHEM101)

#### **Step 2: Add Teachers**
1. Go to **Admin Panel > Teachers**
2. Add teachers with their details

#### **Step 3: Create Classes**
1. Go to **Admin Panel > Classes**
2. Create classes by combining:
   - Class name (e.g., "Grade 10 Mathematics - A")
   - Subject (select from dropdown)
   - Teacher (select from dropdown)
   - Section, room, etc.

#### **Step 4: Now Create Assignments**
1. Go to **Admin Panel > Assignments**
2. The "Select Class" dropdown should now show your classes!

### **3. Verify Everything Works**

✅ **Check these URLs:**
- `http://localhost/your-project/admin/subjects.php` - Should show subjects
- `http://localhost/your-project/admin/classes.php` - Should show classes  
- `http://localhost/your-project/admin/assignments.php` - Should show class dropdown working

### **4. Sample Login Credentials**

**Admin:**
- Email: `admin@tuitioncenter.com`
- Password: `password`

**Sample Teachers:** (after importing sample data)
- Email: `sarah.johnson@tuitioncenter.com`
- Password: `password`

**Sample Students:** (after importing sample data)
- Email: `john.smith@student.com`
- Password: `password`

### **5. Troubleshooting**

**If class dropdown is still empty:**

1. **Check database connection** in `config.php`
2. **Verify database name** is `tuition_center`
3. **Check if tables exist:**
   ```sql
   USE tuition_center;
   SHOW TABLES;
   SELECT * FROM subjects;
   SELECT * FROM classes;
   ```

4. **Check for errors** in browser console or PHP error logs

### **6. File Structure Check**

Ensure you have these files:
```
your-project/
├── admin/
│   ├── assignments.php ✅
│   ├── classes.php ✅  
│   ├── subjects.php ✅
│   ├── teachers.php ✅
│   └── dashboard.php ✅
├── uploads/
│   ├── assignments/ ✅
│   └── submissions/ ✅
├── config.php ✅
├── db.sql ✅
├── sample_data.sql ✅
└── index.html ✅
```

### **7. Success Indicators**

When everything is working, you should see:
- ✅ Subjects list in subjects page
- ✅ Classes list showing subject names and teachers
- ✅ Assignment creation form with populated class dropdown
- ✅ Ability to create assignments successfully

### **🎉 You're All Set!**

Your assignment management system should now be fully functional with working class selection!

Need help? Check:
1. Database has data (`SELECT * FROM classes`)
2. PHP errors in browser console
3. File permissions for uploads folder