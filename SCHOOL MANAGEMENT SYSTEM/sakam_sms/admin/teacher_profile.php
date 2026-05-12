<?php
/**
 * Teacher Profile Page
 * Sakam M/A JHS School Management System
 * 
 * View detailed teacher information
 */

// Require login
require_once '../includes/functions.php';
requireLogin();

// Page title
$pageTitle = 'Teacher Profile';

// Get teacher ID
$teacherId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get teacher data
$teacher = fetchOne("SELECT * FROM teachers WHERE id = ?", [$teacherId]);

if (!$teacher) {
    setMessage('Teacher not found.', 'error');
    redirect('teachers.php');
}

// Get teacher's subjects
$subjects = fetchAll("
    SELECT * FROM subjects 
    WHERE teacher_id = ? OR id IN (
        SELECT subject_id FROM subject_teachers WHERE teacher_id = ?
    )
", [$teacherId, $teacherId]);

// Get teacher's classes
$classTeacher = fetchOne("SELECT * FROM classes WHERE teacher_id = ?", [$teacherId]);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chalkboard-teacher"></i> Teacher Profile</h1>
    <div>
        <a href="teachers.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Teachers
        </a>
        <a href="edit_teacher.php?id=<?php echo $teacherId; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Teacher
        </a>
    </div>
</div>

<!-- Teacher Info Card -->
<div class="card">
    <div class="card-header">
        <h3>Personal Information</h3>
        <span class="badge badge-<?php echo $teacher['status'] === 'Active' ? 'success' : 'warning'; ?>">
            <?php echo $teacher['status']; ?>
        </span>
    </div>
    <div class="card-body">
        <div class="profile-grid">
            <div class="profile-item">
                <label>Employee ID</label>
                <span><?php echo escapeHTML($teacher['staff_id']); ?></span>
            </div>
            <div class="profile-item">
                <label>Full Name</label>
                <span><?php echo escapeHTML($teacher['first_name'] . ' ' . $teacher['last_name']); ?></span>
            </div>
            <div class="profile-item">
                <label>Gender</label>
                <span><?php echo $teacher['gender']; ?></span>
            </div>
            <div class="profile-item">
                <label>Date of Birth</label>
                <span><?php echo $teacher['date_of_birth'] ? formatDate($teacher['date_of_birth']) : 'N/A'; ?></span>
            </div>
            <div class="profile-item">
                <label>Email</label>
                <span><?php echo escapeHTML($teacher['email']); ?></span>
            </div>
            <div class="profile-item">
                <label>Phone</label>
                <span><?php echo escapeHTML($teacher['contact']); ?></span>
            </div>
            <div class="profile-item">
                <label>Subject Specialty</label>
                    <?php 
    if (!empty($teacher['subject_id'])) {
        $subject = fetchOne("SELECT subject_name FROM subjects WHERE id = ?", [$teacher['subject_id']]);
        echo escapeHTML($subject['subject_name'] ?? 'N/A');
    } else {
        echo 'N/A';
    }
    ?>
            </div>
            <div class="profile-item">
                <label>Qualification</label>
                <span><?php echo escapeHTML($teacher['qualification'] ?: 'N/A'); ?></span>
            </div>
            <div class="profile-item">
                <label>Hire Date</label>
                <span><?php echo formatDate($teacher['hire_date']); ?></span>
            </div>
            <div class="profile-item">
                <label>Address</label>
                <span><?php echo escapeHTML($teacher['address'] ?: 'N/A'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Teaching Assignments -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
    <!-- Class Assignment -->
    <div class="card">
        <div class="card-header">
            <h3>Class Assignment</h3>
        </div>
        <div class="card-body">
            <?php if ($classTeacher): ?>
            <div class="info-box">
                <i class="fas fa-door-open"></i>
                <div>
                    <h4><?php echo escapeHTML($classTeacher['class_name']); ?></h4>
                    <p>Class Teacher</p>
                </div>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-door-open"></i>
                <h3>No class assigned</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Subjects -->
    <div class="card">
        <div class="card-header">
            <h3>Subjects</h3>
        </div>
        <div class="card-body">
            <?php if (empty($subjects)): ?>
            <div class="empty-state">
                <i class="fas fa-book"></i>
                <h3>No subjects assigned</h3>
            </div>
            <?php else: ?>
            <div class="tag-list">
                <?php foreach ($subjects as $subject): ?>
                <span class="tag"><?php echo escapeHTML($subject['subject_name']); ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>