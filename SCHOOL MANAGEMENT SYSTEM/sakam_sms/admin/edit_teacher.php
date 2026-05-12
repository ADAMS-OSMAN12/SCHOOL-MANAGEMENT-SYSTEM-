<?php
/**
 * Edit Teacher Page
 * Sakam M/A JHS School Management System
 * 
 * Form to edit existing teacher
 */

// Require admin access
require_once '../includes/functions.php';
requireAdmin();

// Page title
$pageTitle = 'Edit Teacher';

// Get teacher ID
$teacherId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get teacher data
$teacher = fetchOne("SELECT * FROM teachers WHERE id = ?", [$teacherId]);

if (!$teacher) {
    setMessage('Teacher not found.', 'error');
    redirect('teachers.php');
}

// Get subjects for dropdown
$subjects = getAllSubjects();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        // Sanitize and validate input
        $staffId = sanitizeInput($_POST['staff_id'] ?? '');
        $firstName = sanitizeInput($_POST['first_name'] ?? '');
        $lastName = sanitizeInput($_POST['last_name'] ?? '');
        $gender = $_POST['gender'] ?? '';
        $dob = $_POST['date_of_birth'] ?? '';
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $subjectId = $_POST['subject_id'] ?? '';
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $hireDate = $_POST['hire_date'] ?? '';
        $address = sanitizeInput($_POST['address'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        // Validation
        $errors = [];

        if (empty($staffId)) {
            $errors[] = 'Staff ID is required.';
        } elseif ($staffId !== $teacher['staff_id'] && recordExists('teachers', 'staff_id = ? AND id != ?', [$staffId, $teacherId])) {
            $errors[] = 'Staff ID already exists.';
        }

        if (empty($firstName)) {
            $errors[] = 'First name is required.';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required.';
        }

        if (empty($gender)) {
            $errors[] = 'Gender is required.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        if (empty($phone)) {
            $errors[] = 'Phone number is required.';
        }

        if (empty($hireDate)) {
            $errors[] = 'Hire date is required.';
        }

        if (empty($errors)) {
            // Update teacher
            $data = [
                'staff_id' => $staffId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'email' => $email,
                'contact' => $phone,
                'subject_id' => $subjectId ?: null,
                'qualification' => $qualification,
                'hire_date' => $hireDate,
                'address' => $address,
                'status' => $status
            ];

            $result = update('teachers', $data, "id = $teacherId");

            if ($result) {
                // Log activity
                logActivity(getUserId(), 'Update Teacher', "Updated teacher: $firstName $lastName");

                // Set success message and redirect
                setMessage('Teacher updated successfully!', 'success');
                redirect('teachers.php');
            } else {
                $error = 'Failed to update teacher. Please try again.';
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
    <h1><i class="fas fa-user-edit"></i> Edit Teacher</h1>
    <a href="teachers.php" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Teachers
    </a>
</div>

<!-- Error Message -->
<?php if (isset($error)): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span><?php echo $error; ?></span>
</div>
<?php endif; ?>

<!-- Edit Teacher Form -->
<div class="card">
    <div class="card-header">
        <h3>Teacher Information</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="" data-validate>
<input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

             <div class="form-row">
                 <div class="form-group">
                     <label for="staff_id">Staff ID *</label>
                     <input type="text" id="staff_id" name="staff_id" class="form-control"
                            value="<?php echo escapeHTML($teacher['staff_id']); ?>" required>
                 </div>

                 <div class="form-group">
                     <label for="subject_id">Subject</label>
                     <select id="subject_id" name="subject_id" class="form-control">
                         <option value="">Select Subject</option>
                         <?php foreach ($subjects as $subject): ?>
                         <option value="<?php echo $subject['id']; ?>"
                             <?php echo $subject['id'] == $teacher['subject_id'] ? 'selected' : ''; ?>>
                             <?php echo escapeHTML($subject['subject_name']); ?>
                         </option>
                         <?php endforeach; ?>
                     </select>
                 </div>
             </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" class="form-control" 
                           value="<?php echo escapeHTML($teacher['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" class="form-control" 
                           value="<?php echo escapeHTML($teacher['last_name']); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-control" required>
                        <option value="Male" <?php echo $teacher['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $teacher['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_of_birth">Date of Birth</label>
                    <input type="date" id="date_of_birth" name="date_of_birth" class="form-control" 
                           value="<?php echo $teacher['date_of_birth']; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo escapeHTML($teacher['email']); ?>" required>
                </div>
                
<div class="form-group">
                     <label for="phone">Phone *</label>
                     <input type="tel" id="phone" name="phone" class="form-control"
                            value="<?php echo escapeHTML($teacher['contact']); ?>" required>
                 </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="qualification">Qualification</label>
                    <input type="text" id="qualification" name="qualification" class="form-control" 
                           value="<?php echo escapeHTML($teacher['qualification']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="hire_date">Hire Date *</label>
                    <input type="date" id="hire_date" name="hire_date" class="form-control" 
                           value="<?php echo $teacher['hire_date']; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-control">
                        <option value="Active" <?php echo $teacher['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $teacher['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" class="form-control" 
                          rows="3"><?php echo escapeHTML($teacher['address']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Teacher
                </button>
                <a href="teachers.php" class="btn btn-outline">
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