<?php
/**
 * Student Profile Page
 * Sakam M/A JHS School Management System
 * 
 * View detailed student information
 */

// Require login
require_once '../includes/functions.php';
requireLogin();

// Page title
$pageTitle = 'Student Profile';

// Get student ID
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get student data with class info
$student = fetchOne("
    SELECT s.*, c.class_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    WHERE s.id = ?
", [$studentId]);

if (!$student) {
    setMessage('Student not found.', 'error');
    redirect('students.php');
}

// Get student's results
$results = fetchAll("
    SELECT r.*, sub.subject_name, sub.subject_code
    FROM results r
    JOIN subjects sub ON r.subject_id = sub.id
    WHERE r.student_id = ?
    ORDER BY r.academic_year DESC, r.term DESC, sub.subject_name
", [$studentId]);

// Get student's attendance
$attendance = fetchAll("
    SELECT * FROM attendance 
    WHERE student_id = ?
    ORDER BY date DESC
    LIMIT 30
", [$studentId]);

// Get attendance summary
$attendanceSummary = fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late
    FROM attendance 
    WHERE student_id = ?
", [$studentId]);

// Get student's fee records
$fees = fetchAll("
    SELECT f.*, t.term_name
    FROM fees f
    LEFT JOIN terms t ON f.term = t.id
    WHERE f.student_id = ?
    ORDER BY f.academic_year DESC, f.term DESC
", [$studentId]);

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-user-graduate"></i> Student Profile</h1>
    <div>
        <a href="students.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
        <a href="edit_student.php?id=<?php echo $studentId; ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit Student
        </a>
    </div>
</div>

<!-- Student Info Card -->
<div class="card">
    <div class="card-header">
        <h3>Personal Information</h3>
        <span class="badge badge-<?php 
            echo $student['status'] === 'Active' ? 'success' : 
                ($student['status'] === 'Suspended' ? 'danger' : 
                ($student['status'] === 'Graduated' ? 'primary' : 'warning')); 
        ?>"><?php echo $student['status']; ?></span>
    </div>
    <div class="card-body">
        <div class="profile-grid">
            <div class="profile-item">
                <label>Student ID</label>
                <span><?php echo escapeHTML($student['student_id']); ?></span>
            </div>
            <div class="profile-item">
                <label>Full Name</label>
                <span><?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name']); ?></span>
            </div>
            <div class="profile-item">
                <label>Gender</label>
                <span><?php echo $student['gender']; ?></span>
            </div>
            <div class="profile-item">
                <label>Date of Birth</label>
                <span><?php echo formatDate($student['date_of_birth']); ?></span>
            </div>
            <div class="profile-item">
                <label>Class</label>
                <span><?php echo escapeHTML($student['class_name']); ?></span>
            </div>
            <div class="profile-item">
                <label>Admission Date</label>
                <span><?php echo formatDate($student['admission_date']); ?></span>
            </div>
            <div class="profile-item">
                <label>Parent/Guardian Name</label>
                <span><?php echo escapeHTML($student['parent_name'] ?: 'N/A'); ?></span>
            </div>
            <div class="profile-item">
                <label>Parent Contact</label>
                <span><?php echo escapeHTML($student['parent_contact'] ?: 'N/A'); ?></span>
            </div>
            <div class="profile-item">
                <label>Parent Email</label>
                <span><?php echo escapeHTML($student['parent_email'] ?: 'N/A'); ?></span>
            </div>
            <div class="profile-item">
                <label>Address</label>
                <span><?php echo escapeHTML($student['address'] ?: 'N/A'); ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tabs for Results, Attendance, Fees -->
<div class="tabs">
    <button class="tab-btn active" data-tab="results">
        <i class="fas fa-clipboard-list"></i> Results
    </button>
    <button class="tab-btn" data-tab="attendance">
        <i class="fas fa-calendar-check"></i> Attendance
    </button>
    <button class="tab-btn" data-tab="fees">
        <i class="fas fa-money-bill-wave"></i> Fees
    </button>
</div>

<!-- Results Tab -->
<div id="results" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <h3>Academic Results</h3>
        </div>
        <div class="card-body">
            <?php if (empty($results)): ?>
            <div class="empty-state">
                <i class="fas fa-clipboard-list"></i>
                <h3>No results yet</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Academic Year</th>
                        <th>Term</th>
                        <th>Subject</th>
                        <th>CA (40%)</th>
                        <th>Exam (60%)</th>
                        <th>Total</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $result): ?>
                    <tr>
                        <td><?php echo $result['academic_year']; ?></td>
                        <td>Term <?php echo $result['term']; ?></td>
                        <td><?php echo escapeHTML($result['subject_name']); ?></td>
                        <td><?php echo $result['ca_score']; ?></td>
                        <td><?php echo $result['exam_score']; ?></td>
                        <td><strong><?php echo $result['total_score']; ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo getGradeClass($result['grade']); ?>">
                                <?php echo $result['grade']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Attendance Tab -->
<div id="attendance" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Attendance Summary</h3>
        </div>
        <div class="card-body">
            <?php if ($attendanceSummary['total'] > 0): ?>
            <div class="stats-grid" style="margin-bottom: 20px;">
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Present</h4>
                        <h2><?php echo $attendanceSummary['present']; ?></h2>
                        <p><?php echo round(($attendanceSummary['present'] / $attendanceSummary['total']) * 100); ?>%</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Absent</h4>
                        <h2><?php echo $attendanceSummary['absent']; ?></h2>
                        <p><?php echo round(($attendanceSummary['absent'] / $attendanceSummary['total']) * 100); ?>%</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Late</h4>
                        <h2><?php echo $attendanceSummary['late']; ?></h2>
                        <p><?php echo round(($attendanceSummary['late'] / $attendanceSummary['total']) * 100); ?>%</p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (empty($attendance)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-check"></i>
                <h3>No attendance records</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?php echo formatDate($record['date']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $record['status'] === 'Present' ? 'success' : 
                                    ($record['status'] === 'Absent' ? 'danger' : 'warning'); 
                            ?>">
                                <?php echo $record['status']; ?>
                            </span>
                        </td>
                        <td><?php echo escapeHTML($record['notes'] ?: '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Fees Tab -->
<div id="fees" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3>Fee Records</h3>
        </div>
        <div class="card-body">
            <?php if (empty($fees)): ?>
            <div class="empty-state">
                <i class="fas fa-money-bill-wave"></i>
                <h3>No fee records</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Academic Year</th>
                        <th>Term</th>
                        <th>Total Amount</th>
                        <th>Amount Paid</th>
                        <th>Balance</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fees as $fee): ?>
                    <tr>
                        <td><?php echo $fee['academic_year']; ?></td>
                        <td>Term <?php echo $fee['term']; ?></td>
                        <td>₵<?php echo number_format($fee['total_amount'], 2); ?></td>
                        <td>₵<?php echo number_format($fee['amount_paid'], 2); ?></td>
                        <td class="<?php echo $fee['total_amount'] - $fee['amount_paid'] > 0 ? 'text-danger' : 'text-success'; ?>">
                            <strong>₵<?php echo number_format($fee['total_amount'] - $fee['amount_paid'], 2); ?></strong>
                        </td>
                        <td><?php echo formatDate($fee['due_date']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $fee['payment_status'] === 'Paid' ? 'success' : 
                                    ($fee['payment_status'] === 'Partial' ? 'warning' : 'danger'); 
                            ?>">
                                <?php echo $fee['payment_status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>