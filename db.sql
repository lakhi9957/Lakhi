CREATE DATABASE IF NOT EXISTS school_db;
USE school_db;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('student', 'teacher', 'admin'),
  verified BOOLEAN DEFAULT FALSE
);