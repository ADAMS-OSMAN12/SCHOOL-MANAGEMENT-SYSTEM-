<?php
/**
 * Dashboard Page
 * Sakam M/A JHS School Management System
 * 
 * Main dashboard showing statistics and overview
 */

// Require login
require_once 'includes/functions.php';
requireLogin();

// Get user info
$userId = getUserId();
$userRole = getUserRole();
$fullName = getUserFullName();

// Page title
$pageTitle = 'Dashboard';

// Get statistics
$totalStudents = countRecords('students', "status = 'Active'");
$totalTeachers = countRecords('teachers', "status = 'Active'");
$totalClasses = countRecords('classes');
$totalSubjects = countRecords('subjects');

// Get today's attendance
$today = date('Y-m-d');
$todayAttendance = fetchOne("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent
    FROM attendance 
    WHERE date = ?
", [$today]);

$presentToday = $todayAttendance['present'] ?? 0;
$absentToday = $todayAttendance['absent'] ?? 0;
$totalMarked = $presentToday + $absentToday;

// Get fee statistics
$feeStats = fetchOne("
    SELECT 
        SUM(total_amount) as total_fees,
        SUM(amount_paid) as total_collected,
        SUM(total_amount - amount_paid) as total_balance
    FROM fees
    WHERE academic_year = '2024-2025'
");

$totalFees = $feeStats['total_fees'] ?? 0;
$totalCollected = $feeStats['total_collected'] ?? 0;
$totalBalance = $feeStats['total_balance'] ?? 0;

// Get recent activities
$recentActivities = fetchAll("
    SELECT al.*, u.username 
    FROM activity_log al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");

// Get recent students
$recentStudents = fetchAll("
    SELECT s.*, c.class_name 
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    ORDER BY s.created_at DESC
    LIMIT 5
");

// Get upcoming fee payments
$upcomingFees = fetchAll("
    SELECT f.*, s.first_name, s.last_name, c.class_name
    FROM fees f
    JOIN students s ON f.student_id = s.id
    JOIN classes c ON s.class_id = c.id
    WHERE f.payment_status != 'Paid' AND f.due_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY f.due_date
    LIMIT 5
");

// Include header
include 'includes/header.php';
?>

<!-- Dashboard Content -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-info">
            <h4>Total Students</h4>
            <h2><?php echo number_format($totalStudents); ?></h2>
            <p>Active enrollment</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-info">
            <h4>Teachers</h4>
            <h2><?php echo number_format($totalTeachers); ?></h2>
            <p>Active staff</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-door-open"></i>
        </div>
        <div class="stat-info">
            <h4>Classes</h4>
            <h2><?php echo number_format($totalClasses); ?></h2>
            <p>Active classes</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon purple">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-info">
            <h4>Subjects</h4>
            <h2><?php echo number_format($totalSubjects); ?></h2>
            <p>Available subjects</p>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h4>Today's Attendance</h4>
            <h2><?php echo $presentToday; ?> / <?php echo $totalStudents; ?></h2>
            <p><?php echo $totalStudents > 0 ? round(($presentToday / $totalStudents) * 100) : 0; ?>% present</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon <?php echo $totalBalance > 0 ? 'red' : 'green'; ?>">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div class="stat-info">
            <h4>Fee Collection</h4>
            <h2>₵<?php echo number_format($totalCollected, 2); ?></h2>
            <p class="<?php echo $totalBalance > 0 ? 'negative' : ''; ?>">₵<?php echo number_format($totalBalance, 2); ?> outstanding</p>
        </div>
    </div>
    
    <?php if ($userRole === 'admin'): ?>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fas fa-user-plus"></i>
        </div>
        <div class="stat-info">
            <h4>New Students</h4>
            <h2><?php echo count($recentStudents); ?></h2>
            <p>This month</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-info">
            <h4>Pending Fees</h4>
            <h2><?php echo count($upcomingFees); ?></h2>
            <p>Due within 7 days</p>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Content Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px;">
    <!-- Recent Students -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user-graduate"></i> Recent Students</h3>
            <a href="admin/students.php" class="btn btn-sm btn-outline">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentStudents)): ?>
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h3>No students yet</h3>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentStudents as $student): ?>
                    <tr>
                        <td><?php echo escapeHTML($student['first_name'] . ' ' . $student['last_name']); ?></td>
                        <td><?php echo escapeHTML($student['class_name']); ?></td>
                        <td><span class="badge badge-<?php echo $student['status'] === 'Active' ? 'success' : 'warning'; ?>"><?php echo $student['status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Activities -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history"></i> Recent Activities</h3>
        </div>
        <div class="card-body">
            <?php if (empty($recentActivities)): ?>
            <div class="empty-state">
                <i class="fas fa-history"></i>
                <h3>No activities yet</h3>
            </div>
            <?php else: ?>
            <div class="recent-activity">
                <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?php echo $activity['action'] === 'Login' ? 'sign-in-alt' : 'cog'; ?>"></i>
                    </div>
                    <div class="activity-details">
                        <h5><?php echo escapeHTML($activity['action']); ?></h5>
                        <p><?php echo escapeHTML($activity['description']); ?> - <?php echo escapeHTML($activity['username']); ?></p>
                        <small><?php echo formatDateTime($activity['created_at']); ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<?php if ($userRole === 'admin'): ?>
<div class="card mt-3">
    <div class="card-header">
        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <a href="admin/add_student.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add Student
            </a>
            <a href="admin/add_teacher.php" class="btn btn-success">
                <i class="fas fa-user-tie"></i> Add Teacher
            </a>
            <a href="teacher/results.php" class="btn btn-warning">
                <i class="fas fa-clipboard-list"></i> Enter Results
            </a>
            <a href="teacher/attendance.php" class="btn btn-info" style="background: var(--secondary-color); color: white;">
                <i class="fas fa-calendar-check"></i> Mark Attendance
            </a>
            <a href="admin/reports.php" class="btn btn-outline">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="card mt-3">
    <div class="card-header">
        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
    </div>
    <div class="card-body">
        <div style="display: flex; flex-wrap: wrap; gap: 15px;">
            <a href="teacher/results.php" class="btn btn-primary">
                <i class="fas fa-clipboard-list"></i> Enter Results
            </a>
            <a href="teacher/attendance.php" class="btn btn-success">
                <i class="fas fa-calendar-check"></i> Mark Attendance
            </a>
            <a href="teacher/fees.php" class="btn btn-warning">
                <i class="fas fa-money-bill-wave"></i> Manage Fees
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Include footer
include 'includes/footer.php';
?>