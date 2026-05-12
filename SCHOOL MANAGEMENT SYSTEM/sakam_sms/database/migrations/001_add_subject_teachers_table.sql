-- Add subject_teachers junction table for many-to-many teacher-subject relationship
-- This table is referenced by teacher_profile.php but was missing from the schema

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