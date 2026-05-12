<?php
// One-time script to create the missing subject_teachers table
require_once __DIR__ . '/../../../sakam_sms/config.php';

$conn = getDBConnection();

$sql = "CREATE TABLE IF NOT EXISTS subject_teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    teacher_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_subject_teacher (subject_id, teacher_id),
    INDEX idx_subject (subject_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table subject_teachers created successfully (or already exists).\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();