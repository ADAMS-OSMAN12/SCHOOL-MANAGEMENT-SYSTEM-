<?php
/**
 * Reports Page
 * Sakam M/A JHS School Management System
 * 
 * Generate and view various school reports
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Reports';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-chart-bar"></i> Reports</h1>
    <a href="../index.php" class="btn btn-outline">
        <i class="fas fa-home"></i> Back to Dashboard
    </a>
</div>

<!-- Reports Grid -->
<div class="reports-grid">
    
    <!-- Student Reports -->
    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-user-graduate"></i>
            <h3>Student Reports</h3>
        </div>
        <div class="report-card-body">
            <p>Generate reports related to student performance and demographics</p>
            <div class="report-actions">
                <a href="student_performance.php" class="btn btn-sm">Performance Report</a>
                <a href="student_demographics.php" class="btn btn-sm">Demographics Report</a>
                <a href="attendance_report.php" class="btn btn-sm">Attendance Report</a>
                <a href="fee_report.php" class="btn btn-sm">Fee Payment Report</a>
            </div>
        </div>
    </div>
    
    <!-- Teacher Reports -->
    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-chalkboard-teacher"></i>
            <h3>Teacher Reports</h3>
        </div>
        <div class="report-card-body">
            <p>Generate reports related to staff and teaching</p>
            <div class="report-actions">
                <a href="teacher_workload.php" class="btn btn-sm">Workload Report</a>
                <a href="teacher_attendance.php" class="btn btn-sm">Attendance Report</a>
                <a href="subject_allocation.php" class="btn btn-sm">Subject Allocation</a>
                <a href="teacher_performance.php" class="btn btn-sm">Performance Report</a>
            </div>
        </div>
    </div>
    
    <!-- Class Reports -->
    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-chalkboard"></i>
            <h3>Class Reports</h3>
        </div>
        <div class="report-card-body">
            <p>Generate reports related to classes and scheduling</p>
            <div class="report-actions">
                <a href="class_size.php" class="btn btn-sm">Class Size Report</a>
                <a href="timetable_report.php" class="btn btn-sm">Timetable Report</a>
                <a href="subject_distribution.php" class="btn btn-sm">Subject Distribution</a>
                <a href="class_performance.php" class="btn btn-sm">Class Performance</a>
            </div>
        </div>
    </div>
    
    <!-- Financial Reports -->
    <div class="report-card">
        <div class="report-card-header">
            <i class="fas fa-money-bill-wave"></i>
            <h3>Financial Reports</h3>
        </div>
        <div class="report-card-body">
            <p>Generate reports related to school finances</p>
            <div class="report-actions">
                <a href="fee_collection.php" class="btn btn-sm">Fee Collection</a>
                <a href="outstanding_fees.php" class="btn btn-sm">Outstanding Fees</a>
                <a href="expense_report.php" class="btn btn-sm">Expense Report</a>
                <a href="financial_summary.php" class="btn btn-sm">Financial Summary</a>
            </div>
        </div>
    </div>
    
</div>

<?php
// Include footer
include '../includes/footer.php';
?>