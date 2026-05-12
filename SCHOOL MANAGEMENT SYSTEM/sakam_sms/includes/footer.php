<?php
/**
 * Footer Include File
 * Sakam M/A JHS School Management System
 * 
 * Contains closing tags and common JavaScript
 */

if (isLoggedIn()): 
?>
            </div>
            <!-- End Page Content -->
        </main>
        <!-- End Main Content -->
    </div>
    <!-- End Wrapper -->
    <?php endif; ?>

    <!-- jQuery (optional, for legacy code) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    
    <!-- Main JavaScript -->
    <script src="../js/script.js"></script>
    
    <!-- Additional page-specific scripts -->
    <?php if (isset($extraScripts)): ?>
    <script>
        <?php echo $extraScripts; ?>
    </script>
    <?php endif; ?>
    
    <!-- Session message handler -->
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('<?php echo addslashes($_SESSION['message']); ?>', '<?php echo $_SESSION['message_type'] ?? 'info'; ?>');
        });
    </script>
    <?php 
        unset($_SESSION['message'], $_SESSION['message_type']);
    endif; 
    ?>
</body>
</html>