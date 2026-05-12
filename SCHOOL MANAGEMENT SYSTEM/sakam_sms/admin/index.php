<?php
/**
 * Admin Dashboard
 * Sakam M/A JHS School Management System
 * 
 * Main admin panel with navigation to all sections
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Admin Dashboard';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
    <a href="../index.php" class="btn btn-outline">
        <i class="fas fa-school"></i> Visit School Site
    </a>
</div>

<!-- Admin Navigation -->
<div class="admin-dashboard">
    <div class="dashboard-grid">
        
        <!-- Teachers Section -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fas fa-user-tie"></i>
                <h3>Teachers</h3>
            </div>
            <div class="dashboard-card-body">
                <p>Manage teacher information and accounts</p>
                <div class="dashboard-actions">
                    <a href="teachers.php" class="btn btn-sm">View All Teachers</a>
                    <a href="add_teacher.php" class="btn btn-sm btn-primary">Add New Teacher</a>
                </div>
            </div>
        </div>
        
        <!-- Students Section -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fas fa-user-graduate"></i>
                <h3>Students</h3>
            </div>
            <div class="dashboard-card-body">
                <p>Manage student records and profiles</p>
                <div class="dashboard-actions">
                    <a href="students.php" class="btn btn-sm">View All Students</a>
                    <a href="add_student.php" class="btn btn-sm btn-primary">Add New Student</a>
                </div>
            </div>
        </div>
        
        <!-- Subjects Section -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fas fa-book"></i>
                <h3>Subjects</h3>
            </div>
            <div class="dashboard-card-body">
                <p>Manage school subjects and curriculum</p>
                <div class="dashboard-actions">
                    <a href="subjects.php" class="btn btn-sm">View All Subjects</a>
                    <a href="add_subject.php" class="btn btn-sm btn-primary">Add New Subject</a>
                </div>
            </div>
        </div>
        
        <!-- Classes Section -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fas fa-chalkboard"></i>
                <h3>Classes</h3>
            </div>
            <div class="dashboard-card-body">
                <p>Manage class schedules and assignments</p>
                <div class="dashboard-actions">
                    <a href="classes.php" class="btn btn-sm">View All Classes</a>
                    <a href="add_class.php" class="btn btn-sm btn-primary">Add New Class</a>
                </div>
            </div>
        </div>
        
        <!-- Timetable Section -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <i class="fas fa-clock"></i>
                <h3>Timetable</h3>
            </div>
            <div class="dashboard-card-body">
                <p>Manage class schedules and periods</p>
                <div class="dashboard-actions">
                    <a href="timetable.php" class="btn btn-sm">View Timetable</a>
                    <a href="add_timetable.php" class="btn btn-sm btn-primary">Add Timetable Entry</a>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- Quick Stats (Optional) -->
    <div class="dashboard-stats">
        <div class="stat-card">
            <i class="fas fa-user-tie stat-icon"></i>
            <div class="stat-info">
                <h3 class="stat-number">-</h3>
                <p>Total Teachers</p>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-user-graduate stat-icon"></i>
            <div class="stat-info">
                <h3 class="stat-number">-</h3>
                <p>Total Students</p>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-book stat-icon"></i>
            <div class="stat-info">
                <h3 class="stat-number">-</h3>
                <p>Total Subjects</p>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-chalkboard stat-icon"></i>
            <div class="stat-info">
                <h3 class="stat-number">-</h3>
                <p>Total Classes</p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>