<?php
/**
 * Header Include File
 * Sakam M/A JHS School Management System
 * 
 * Contains the HTML head, navigation, and common page elements
 */

// Include database connection
require_once 'db.php';

// Get current page for active menu highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get user info from session
$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['username'] ?? 'User';
$fullName = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Sakam M/A JHS School Management System">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../images/favicon.png">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Additional page-specific styles -->
    <?php if (isset($extraStyles)): ?>
    <style>
        <?php echo $extraStyles; ?>
    </style>
    <?php endif; ?>
</head>
<body>
    <?php if (isLoggedIn()): ?>
    <div class="wrapper">
        <!-- Sidebar Overlay (mobile) -->
        <div class="sidebar-overlay"></div>
        
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-school"></i> <?php echo SITE_NAME; ?></h2>
                <p>School Management System</p>
            </div>
            
            <nav class="sidebar-menu">
                <ul>
                    <li>
                        <a href="../dashboard.php" class="<?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    
                    <?php if ($userRole === 'admin'): ?>
                    <li>
                        <a href="../admin/students.php" class="<?php echo in_array($currentPage, ['students', 'add_student', 'edit_student', 'student_profile']) ? 'active' : ''; ?>">
                            <i class="fas fa-user-graduate"></i> Students
                        </a>
                    </li>
                    <li>
                        <a href="../admin/teachers.php" class="<?php echo in_array($currentPage, ['teachers', 'add_teacher', 'edit_teacher']) ? 'active' : ''; ?>">
                            <i class="fas fa-chalkboard-teacher"></i> Staff
                        </a>
                    </li>
                    <li>
                        <a href="../admin/classes.php" class="<?php echo in_array($currentPage, ['classes', 'add_class', 'edit_class']) ? 'active' : ''; ?>">
                            <i class="fas fa-door-open"></i> Classes
                        </a>
                    </li>
                    <li>
                        <a href="../admin/subjects.php" class="<?php echo in_array($currentPage, ['subjects', 'add_subject', 'edit_subject']) ? 'active' : ''; ?>">
                            <i class="fas fa-book"></i> Subjects
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <li>
                        <a href="../teacher/results.php" class="<?php echo in_array($currentPage, ['results', 'add_result', 'edit_result', 'view_results']) ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i> Results
                        </a>
                    </li>
                    <li>
                        <a href="../teacher/attendance.php" class="<?php echo in_array($currentPage, ['attendance', 'mark_attendance', 'attendance_report']) ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-check"></i> Attendance
                        </a>
                    </li>
                    <li>
                        <a href="../teacher/fees.php" class="<?php echo in_array($currentPage, ['fees', 'add_fee', 'record_payment']) ? 'active' : ''; ?>">
                            <i class="fas fa-money-bill-wave"></i> Fees
                        </a>
                    </li>
                    
                    <?php if ($userRole === 'admin'): ?>
                    <li>
                        <a href="../admin/timetable.php" class="<?php echo in_array($currentPage, ['timetable', 'add_timetable']) ? 'active' : ''; ?>">
                            <i class="fas fa-clock"></i> Timetable
                        </a>
                    </li>
                    <li>
                        <a href="../admin/reports.php" class="<?php echo $currentPage === 'reports' ? 'active' : ''; ?>">
                            <i class="fas fa-chart-bar"></i> Reports
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout (<?php echo escapeHTML($userName); ?>)
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="toggle-sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h3><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h3>
                </div>
                
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($fullName, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo escapeHTML($fullName); ?></span>
                    </div>
                    <div class="header-actions">
                        <a href="../auth/profile.php" title="Profile">
                            <i class="fas fa-user-circle"></i>
                        </a>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="page-content">
                <?php displayMessage(); ?>
    <?php else: ?>
    <!-- Login page layout -->
    <?php endif; ?>