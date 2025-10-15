<?php
// Security: Prevent direct access
if (!defined('PANEL_ACCESS')) {
    die('Direct access not permitted');
}
?>
        </div> <!-- content-wrapper -->
        
        <footer class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo PANEL_TITLE; ?> | Server: <?php echo SERVER_IP; ?></p>
        </footer>
    </div> <!-- main-content -->
    
    <script src="assets/js/main.js"></script>
</body>
</html>
