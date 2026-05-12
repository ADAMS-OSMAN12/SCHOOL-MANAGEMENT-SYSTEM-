<?php
/**
 * Edit Class Page
 * Sakam M/A JHS School Management System
 * 
 * Form to edit existing class
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Edit Class';

// Get class ID
$classId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get class data
$class = fetchOne("SELECT * FROM classes WHERE id = ?", [$classId]);

if (!$class) {
    setMessage('Class not found.', 'error');
    redirect('classes.php');
}

// Get teachers for dropdown
$teachers = getAllTeachers();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $className = sanitizeInput($_POST['class_name'] ?? '');
        $classLevel = (int)$_POST['class_level'] ?? 0;
        $teacherId = $_POST['teacher_id'] ?? '';
        $capacity = (int)$_POST['capacity'] ?? 40;
        $status = $_POST['status'] ?? 'Active';
        
        // Validation
        $errors = [];
        
        if (empty($className)) {
            $errors[] = 'Class name is required.';
        } elseif ($className !== $class['class_name'] && recordExists('classes', 'class_name = ? AND id != ?', [$className, $classId])) {
            $errors[] = 'Class name already exists.';
        }
        
        if ($classLevel < 1 || $classLevel > 12) {
            $errors[] = 'Class level must be between 1 and 12.';
        }
        
        if (empty($errors)) {
            // Update class
            $data = [
                'class_name' => $className,
                'class_level' => $classLevel,
                'teacher_id' => $teacherId ?: null,
                'capacity' => $capacity,
                'status' => $status
            ];
            
            $result = update('classes', $data, "id = $classId");
            
            if ($result) {
                // Log activity
                logActivity(getUserId(), 'Update Class', "Updated class: $className");
                
                // Set success message and redirect
                setMessage('Class updated successfully!', 'success');
                redirect('classes.php');
            } else {
                $error = 'Failed to update class. Please try again.';
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Include header
include '../includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <h1><i class="fas fa-edit"></i> Edit Class</h1>
    <a href="classes.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Classes
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Edit Class Form -->
<div class="card">
    <div class="card-header">
        <h3>Class Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="class_name">Class Name *</label>
                    <input type="text" id="class_name" name="class_name" class="form-control" 
                           value="<?php echo escapeHTML($class['class_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="class_level">Class Level *</label>
                    <input type="number" id="class_level" name="class_level" class="form-control" 
                           value="<?php echo $class['class_level']; ?>" min="1" max="12" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="teacher_id">Class Teacher</label>
                    <select id="teacher_id" name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach ($teachers as $teacher): ?>
                        <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher['id'] == $class['teacher_id'] ? 'selected' : ''; ?>>
                            <?php echo escapeHTML($teacher['first_name'] . ' ' . $teacher['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" id="capacity" name="capacity" class="form-control" 
                           value="<?php echo $class['capacity']; ?>" min="1" max="100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Active" <?php echo $class['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $class['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Class
                </button>
                <a href="classes.php" class="btn btn-outline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>