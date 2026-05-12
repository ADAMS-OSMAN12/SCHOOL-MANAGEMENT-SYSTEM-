<?php
/**
 * Timetable Management
 * Sakam M/A JHS School Management System
 * 
 * Manage class timetables and schedules
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Timetable';

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-clock"></i> Timetable</h1>
    <a href="add_timetable.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Timetable Entry
    </a>
</div>

<!-- Timetable Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" action="" class="filter-group">
            <select name="class_id" class="form-control">
                <option value="">All Classes</option>
                <?php 
                $classes = getAllClasses();
                foreach ($classes as $class): ?>
                <option value="<?php echo $class['id']; ?>" 
                    <?php echo isset($_GET['class_id']) && $_GET['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                    <?php echo escapeHTML($class['class_name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <select name="day" class="form-control">
                <option value="">All Days</option>
                <option value="Monday" <?php echo isset($_GET['day']) && $_GET['day'] == 'Monday' ? 'selected' : ''; ?>>Monday</option>
                <option value="Tuesday" <?php echo isset($_GET['day']) && $_GET['day'] == 'Tuesday' ? 'selected' : ''; ?>>Tuesday</option>
                <option value="Wednesday" <?php echo isset($_GET['day']) && $_GET['day'] == 'Wednesday' ? 'selected' : ''; ?>>Wednesday</option>
                <option value="Thursday" <?php echo isset($_GET['day']) && $_GET['day'] == 'Thursday' ? 'selected' : ''; ?>>Thursday</option>
                <option value="Friday" <?php echo isset($_GET['day']) && $_GET['day'] == 'Friday' ? 'selected' : ''; ?>>Friday</option>
            </select>
            
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Filter
            </button>
            
            <a href="timetable.php" class="btn btn-outline">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Timetable Display -->
<div class="card">
    <div class="card-header">
        <h3>School Timetable</h3>
    </div>
    <div class="card-body">
        <?php 
        // Build query
        $where = [];
        $params = [];
        
        if (isset($_GET['class_id']) && $_GET['class_id'] != '') {
            $where[] = "t.class_id = ?";
            $params[] = $_GET['class_id'];
        }
        
        if (isset($_GET['day']) && $_GET['day'] != '') {
            $where[] = "t.day = ?";
            $params[] = $_GET['day'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $orderBy = "ORDER BY t.day, t.start_time";
        
        // Get timetable entries
        $timetable = fetchAll("
            SELECT t.*, c.class_name, s.subject_name, 
                   CONCAT(t.first_name, ' ', t.last_name) as teacher_name
            FROM timetable t
            LEFT JOIN classes c ON t.class_id = c.id
            LEFT JOIN subjects s ON t.subject_id = s.id
            LEFT JOIN teachers t ON t.teacher_id = t.id
            $whereClause
            $orderBy
        ", $params);
        
        if (empty($timetable)): ?>
        <div class="empty-state">
            <i class="fas fa-clock"></i>
            <h3>No timetable entries found</h3>
            <p>Try adjusting your filters or add a new timetable entry.</p>
            <a href="add_timetable.php" class="btn btn-primary mt-2">Add Timetable Entry</a>
        </div>
        <?php else: ?>
        <div class="timetable-container">
            <table class="timetable-table">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Room</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($timetable as $entry): ?>
                    <tr>
                        <td><?php echo escapeHTML($entry['day']); ?></td>
                        <td>
                            <?php echo escapeHTML($entry['start_time']); ?> - 
                            <?php echo escapeHTML($entry['end_time']); ?>
                        </td>
                        <td><?php echo escapeHTML($entry['class_name']); ?></td>
                        <td><?php echo escapeHTML($entry['subject_name']); ?></td>
                        <td><?php echo escapeHTML($entry['teacher_name']); ?></td>
                        <td><?php echo escapeHTML($entry['room_number']); ?></td>
                        <td class="table-actions">
                            <a href="edit_timetable.php?id=<?php echo $entry['id']; ?>" class="edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button onclick="deleteRecord(<?php echo $entry['id']; ?>, 'timetable')" class="delete-btn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Delete</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this timetable entry? This action cannot be undone.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline modal-cancel">Cancel</button>
            <a href="" id="deleteLink" class="btn btn-danger">Delete</a>
        </div>
    </div>
</div>

<script>
function deleteRecord(id, type) {
    const modal = document.getElementById('deleteModal');
    const deleteLink = document.getElementById('deleteLink');
    
    deleteLink.href = 'delete_' + type + '.php?id=' + id + '&token=<?php echo generateCSRFToken(); ?>';
    modal.classList.add('active');
}
</script>

<?php
// Include footer
include '../includes/footer.php';
?>