-- =====================================================
-- SAKAM M/A JHS SCHOOL MANAGEMENT SYSTEM
-- Database Schema
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS sakam_sms;
USE sakam_sms;

-- =====================================================
-- TABLES
-- =====================================================

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL UNIQUE,
    class_level INT NOT NULL,
    teacher_id INT,
    capacity INT DEFAULT 40,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Subjects table
CREATE TABLE IF NOT EXISTS subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL UNIQUE,
    subject_code VARCHAR(20),
    teacher_id INT,
    class_id INT,
    pass_mark INT DEFAULT 40,
    description TEXT,
    status ENUM('Active','Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    class_id INT NOT NULL,
    admission_date DATE NOT NULL,
    parent_name VARCHAR(100),
    parent_contact VARCHAR(20),
    parent_email VARCHAR(100),
    address TEXT,
    photo VARCHAR(255) DEFAULT 'default.png',
    status ENUM('Active', 'Inactive', 'Suspended', 'Graduated') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT,
    INDEX idx_student_id (student_id),
    INDEX idx_class (class_id)
) ENGINE=InnoDB;

-- Teachers/Staff table
CREATE TABLE IF NOT EXISTS teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL,
    date_of_birth DATE NOT NULL,
    subject_id INT,
    contact VARCHAR(20) NOT NULL,
    email VARCHAR(100) UNIQUE,
    address TEXT,
    qualification VARCHAR(100),
    hire_date DATE NOT NULL,
    role ENUM('Teacher', 'Head Teacher', 'Administrator', 'Support Staff') DEFAULT 'Teacher',
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    photo VARCHAR(255) DEFAULT 'default.png',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    INDEX idx_staff_id (staff_id),
    INDEX idx_subject (subject_id)
) ENGINE=InnoDB;

-- Subject Teachers junction table (many-to-many)
CREATE TABLE IF NOT EXISTS subject_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_teacher (subject_id, teacher_id),
    INDEX idx_subject (subject_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB;

-- Results table
CREATE TABLE IF NOT EXISTS results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    term ENUM('1st', '2nd', '3rd') NOT NULL,
    ca_score DECIMAL(5,2) DEFAULT 0,
    exam_score DECIMAL(5,2) DEFAULT 0,
    total_score DECIMAL(5,2) GENERATED ALWAYS AS (ca_score + exam_score) STORED,
    grade VARCHAR(2),
    position INT,
    remarks VARCHAR(50),
    entered_by INT,
    exam_date DATE,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_result (student_id, subject_id, academic_year, term),
    INDEX idx_student (student_id),
    INDEX idx_subject (subject_id),
    INDEX idx_term (academic_year, term)
) ENGINE=InnoDB;

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Excused') NOT NULL,
    remarks VARCHAR(100),
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_attendance (student_id, date),
    INDEX idx_student (student_id),
    INDEX idx_class (class_id),
    INDEX idx_date (date)
) ENGINE=InnoDB;

-- Fees table
CREATE TABLE IF NOT EXISTS fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    term ENUM('1st', '2nd', '3rd') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    balance DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - amount_paid) STORED,
    payment_status ENUM('Unpaid', 'Partial', 'Paid') DEFAULT 'Unpaid',
    due_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_fee (student_id, academic_year, term),
    INDEX idx_student (student_id),
    INDEX idx_academic (academic_year, term)
) ENGINE=InnoDB;

-- Fee payments table
CREATE TABLE IF NOT EXISTS fee_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_id INT NOT NULL,
    student_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('Cash', 'Bank Transfer', 'Mobile Money', 'Cheque') DEFAULT 'Cash',
    reference_number VARCHAR(50),
    received_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fee_id) REFERENCES fees(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES teachers(id) ON DELETE SET NULL,
    INDEX idx_fee (fee_id),
    INDEX idx_student (student_id)
) ENGINE=InnoDB;

-- Timetable table
CREATE TABLE IF NOT EXISTS timetable (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    teacher_id INT,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    room VARCHAR(20),
    academic_year VARCHAR(20) NOT NULL,
    term ENUM('1st', '2nd', '3rd') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_slot (class_id, day_of_week, start_time, academic_year, term),
    INDEX idx_class (class_id),
    INDEX idx_day (day_of_week)
) ENGINE=InnoDB;

-- Users table (for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL,
    teacher_id INT,
    email VARCHAR(100),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB;

-- Activity log table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- DEFAULT DATA
-- =====================================================

-- Insert default classes
INSERT INTO classes (class_name, class_level) VALUES 
('JHS 1', 1),
('JHS 2', 2),
('JHS 3', 3);

-- Insert default subjects
INSERT INTO subjects (subject_name, subject_code) VALUES 
('Mathematics', 'MATH'),
('English Language', 'ENG'),
('Science', 'SCI'),
('Social Studies', 'SST'),
('Religious and Moral Education', 'RME'),
('Information and Communication Technology', 'ICT'),
('French', 'FRN'),
('Basic Design and Technology', 'BDT'),
('Visual Arts', 'VA'),
('Music', 'MUS'),
('Physical Education', 'PE'),
('Home Economics', 'HE');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role, email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@sakamsms.edu.gh');

-- Insert sample teachers
INSERT INTO teachers (staff_id, first_name, last_name, gender, date_of_birth, subject_id, contact, email, qualification, hire_date, role) VALUES
('TCH001', 'John', 'Mensah', 'Male', '1980-05-15', 1, '0241234567', 'john.mensah@sakamsms.edu.gh', 'M.Ed Mathematics', '2015-01-10', 'Head Teacher'),
('TCH002', 'Mary', 'Akosua', 'Female', '1985-08-20', 2, '0242345678', 'mary.akosua@sakamsms.edu.gh', 'BA English', '2016-03-15', 'Teacher'),
('TCH003', 'Peter', 'Kofi', 'Male', '1982-11-10', 3, '0243456789', 'peter.kofi@sakamsms.edu.gh', 'B.Sc Science', '2014-09-01', 'Teacher'),
('TCH004', 'Sarah', 'Adomako', 'Female', '1988-04-25', 4, '0244567890', 'sarah.adomako@sakamsms.edu.gh', 'MA Social Studies', '2017-07-20', 'Teacher'),
('TCH005', 'James', 'Owusu', 'Male', '1975-12-01', 6, '0245678901', 'james.owusu@sakamsms.edu.gh', 'B.Sc ICT', '2013-02-14', 'Administrator');

-- Insert teacher user accounts
INSERT INTO users (username, password, role, teacher_id, email) VALUES 
('j.mensah', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 1, 'john.mensah@sakamsms.edu.gh'),
('m.akosua', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 2, 'mary.akosua@sakamsms.edu.gh'),
('p.kofi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 3, 'peter.kofi@sakamsms.edu.gh'),
('s.adomako', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 4, 'sarah.adomako@sakamsms.edu.gh'),
('j.owusu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', 5, 'james.owusu@sakamsms.edu.gh');

-- Insert sample students
INSERT INTO students (student_id, first_name, last_name, gender, date_of_birth, class_id, admission_date, parent_name, parent_contact, parent_email, address) VALUES
('STU001', 'Emmanuel', 'Kofi', 'Male', '2012-03-15', 1, '2024-01-10', 'Kofi Mensah', '0201234567', 'kofi.mensah@email.com', 'P.O. Box 123, Sakam'),
('STU002', 'Grace', 'Akosua', 'Female', '2012-07-22', 1, '2024-01-10', 'Akosua Serwaa', '0202345678', 'akosua.serwaa@email.com', 'P.O. Box 456, Sakam'),
('STU003', 'Daniel', 'Owusu', 'Male', '2011-11-08', 2, '2023-01-15', 'Owusu Amponsah', '0203456789', 'owusu.amponsah@email.com', 'P.O. Box 789, Sakam'),
('STU004', 'Esther', 'Adomaa', 'Female', '2011-05-30', 2, '2023-01-15', 'Adomaa Yaa', '0204567890', 'adomaa.yaa@email.com', 'P.O. Box 234, Sakam'),
('STU005', 'Michael', 'Agyeman', 'Male', '2010-09-12', 3, '2022-01-08', 'Agyeman Kwaku', '0205678901', 'agye.kwaku@email.com', 'P.O. Box 567, Sakam'),
('STU006', 'Ruth', 'Baba', 'Female', '2010-02-28', 3, '2022-01-08', 'Baba Mary', '0206789012', 'baba.mary@email.com', 'P.O. Box 890, Sakam');

-- Insert sample results
INSERT INTO results (student_id, subject_id, academic_year, term, ca_score, exam_score, grade, entered_by) VALUES
(1, 1, '2024-2025', '1st', 15, 65, 'B', 1),
(1, 2, '2024-2025', '1st', 12, 55, 'C', 2),
(1, 3, '2024-2025', '1st', 18, 70, 'A', 3),
(2, 1, '2024-2025', '1st', 18, 75, 'A', 1),
(2, 2, '2024-2025', '1st', 14, 60, 'B', 2),
(2, 3, '2024-2025', '1st', 16, 68, 'B', 3),
(3, 1, '2024-2025', '1st', 20, 80, 'A', 1),
(3, 2, '2024-2025', '1st', 16, 72, 'A', 2),
(3, 3, '2024-2025', '1st', 19, 78, 'A', 3);

-- Insert sample attendance
INSERT INTO attendance (student_id, class_id, date, status, marked_by) VALUES
(1, 1, '2025-04-21', 'Present', 1),
(2, 1, '2025-04-21', 'Present', 1),
(3, 2, '2025-04-21', 'Absent', 2),
(4, 2, '2025-04-21', 'Present', 2),
(5, 3, '2025-04-21', 'Present', 3),
(6, 3, '2025-04-21', 'Late', 3),
(1, 1, '2025-04-22', 'Present', 1),
(2, 1, '2025-04-22', 'Present', 1),
(3, 2, '2025-04-22', 'Present', 2),
(4, 2, '2025-04-22', 'Excused', 2);

-- Insert sample fees
INSERT INTO fees (student_id, academic_year, term, total_amount, amount_paid, payment_status, due_date) VALUES
(1, '2024-2025', '1st', 350.00, 350.00, 'Paid', '2024-02-28'),
(2, '2024-2025', '1st', 350.00, 200.00, 'Partial', '2024-02-28'),
(3, '2024-2025', '1st', 350.00, 350.00, 'Paid', '2024-02-28'),
(4, '2024-2025', '1st', 350.00, 0.00, 'Unpaid', '2024-02-28'),
(5, '2024-2025', '1st', 350.00, 350.00, 'Paid', '2024-02-28'),
(6, '2024-2025', '1st', 350.00, 150.00, 'Partial', '2024-02-28');

-- =====================================================
-- VIEWS
-- =====================================================

-- View for student results with details
CREATE OR REPLACE VIEW v_student_results AS
SELECT 
    r.id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.student_id,
    sub.subject_name,
    r.academic_year,
    r.term,
    r.ca_score,
    r.exam_score,
    r.total_score,
    r.grade,
    r.remarks
FROM results r
JOIN students s ON r.student_id = s.id
JOIN subjects sub ON r.subject_id = sub.id;

-- View for attendance summary
CREATE OR REPLACE VIEW v_attendance_summary AS
SELECT 
    a.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.class_id,
    c.class_name,
    a.date,
    a.status
FROM attendance a
JOIN students s ON a.student_id = s.id
JOIN classes c ON s.class_id = c.id;

-- View for fee status
CREATE OR REPLACE VIEW v_fee_status AS
SELECT 
    f.id,
    f.student_id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.student_id AS student_number,
    c.class_name,
    f.academic_year,
    f.term,
    f.total_amount,
    f.amount_paid,
    f.balance,
    f.payment_status,
    f.due_date
FROM fees f
JOIN students s ON f.student_id = s.id
JOIN classes c ON s.class_id = c.id;