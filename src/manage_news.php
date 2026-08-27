<?php
// Start output buffering to prevent header issues
ob_start();

// Start session and include files BEFORE any HTML output
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");

// Initialize variables
$pendingCount = 0;

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_account']) || empty($_SESSION['logged_account']) || $_SESSION['logged_account']['type'] !== 'admin') {
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    // Start new session for flash message
    session_start();
    $_SESSION['flash_mode'] = "alert-warning";
    $_SESSION['flash'] = "You must log in as admin to access this page.";
    header('Location: login.php');
    exit();
} 

// Get pending account count for admin dashboard
$result = $conn->query("SELECT COUNT(*) AS count FROM account_table WHERE status = 'Pending' AND type != 'admin'");
if ($result && $row = $result->fetch_assoc()) {
    $pendingCount = (int)$row['count'];
}

// Handle DELETE request (using POST with action parameter for better compatibility)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    header('Content-Type: application/json');
    
    try {
        if (!isset($_POST['id']) || empty($_POST['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid ID provided']);
            exit;
        }

        $id = intval($_POST['id']);
        
        // Get news details before deletion
        $stmt = $conn->prepare("SELECT * FROM event_table WHERE id = ? AND type = 'News'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Remove image from storage if it's not a default image
            $uploadDir = "images/";
            if ($row['photo'] != "default_events.jpg" && $row['photo'] != "default_news.png" && file_exists($uploadDir . $row['photo'])) {
                unlink($uploadDir . $row['photo']);
            }

            // Delete the news
            $deleteNews = $conn->prepare("DELETE FROM event_table WHERE id = ? AND type = 'News'");
            $deleteNews->bind_param("i", $id);
            
            if ($deleteNews->execute()) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'News article deleted successfully.'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete news from database']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'News article not found']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit;
}

// Build query filters for news only
$filterSearch = "";

// Search filter for News
if (isset($_GET['search']) && $_GET['search'] != "") {
    $trimSearch = strtolower(trim($_GET['search']));
    $filterSearch = "AND (
        LOWER(title) LIKE '%$trimSearch%' OR 
        LOWER(location) LIKE '%$trimSearch%' OR 
        LOWER(description) LIKE '%$trimSearch%'
    )";
}

// Query the database for news only
$allNews = $conn->query("SELECT * FROM event_table WHERE type = 'News' $filterSearch ORDER BY event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News | Admin Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <!-- DataTables Bootstrap 5 fixedHeader -->
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.bootstrap5.min.css" />
    <!-- Modern Design System -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <!-- DataTables Bootstrap 5 -->
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Bootstrap 5 fixedHeader -->
    <script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <script>
        // Display description when opening rows' slider
        function format(row) {
            return ('<div class="slider"><div class="row"> <div class="col-auto ms-3 my-3 me-0 pe-0"><div class="image-table-container"><img src="images/'+row[7]+'" class="img-fluid" alt="news_photo" style="max-width: 150px; border-radius: 8px;"></div></div> <div class="col mx-0 px-0"><p class="fw-light ps-4 pe-5 pt-2 pb-3">'+row[6]+'</p></div> </div></div>');
        }

        // DataTables init
        var table = $('#newsTable').DataTable({
            paging: true,
            lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
            info: true,
            searching: false,
            responsive: true,
            fixedHeader: {
                header: true,
                headerOffset: $('.navbar').height()
            },
            pagingType: 'full_numbers',
            order: [[1, "desc"]],
            columnDefs: [
                {
                    "targets": [0, 6],
                    "orderable": false,
                },
                {
                    "targets": '_all',
                    "createdCell": function (td, cellData, rowData, row, col) {
                        $(td).css('padding', '15px');
                    }
                },
            ],
        });

        // Onclick listener for the description displayer
        $('#newsTable tbody').on('click', 'a.dt-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
        
            if (row.child.isShown()) {
                // This row is already open - close it
                $('div.slider', row.child()).slideUp( function () {
                    row.child.hide();
                    tr.removeClass('shown');
                });
                $(this).html('<i class="bi bi-chevron-down"></i>');
            }
            else {
                // Open this row
                row.child( format(row.data()), 'no-padding' ).show();
                tr.addClass('shown');
                $('div.slider', row.child()).slideDown();
                $(this).html('<i class="bi bi-chevron-up"></i>');
            }
        });

        <?php
            // Populate the DataTables with data from DB
            if ($allNews && $allNews->num_rows > 0) {
                while ($row = $allNews->fetch_assoc()) {
                    // Dropdown actions to edit and delete
                    $actionDropdown = '
                        <div class="dropstart me-4">
                            <div class="float-end" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots text-secondary"></i>
                            </div>
                            <ul class="dropdown-menu dropdown-menu-normal mt-1 px-2">
                                <li><a class="dropdown-item py-2 pe-5" href="edit_event.php?id='.$row['id'].'">
                                    <i class="bi bi-pencil me-3"></i><div class="fw-medium">Edit</div>
                                </a></li>
                                <li><button type="button" class="dropdown-item py-2 pe-5" onclick="deleteNews('.$row['id'].', \''.$row['title'].'\', this)">
                                    <i class="bi bi-trash me-3 text-danger"></i><div class="fw-medium text-danger">Delete</div>
                                </button></li>
                            </ul>
                        </div>
                    ';

                    // Status badge
                    $status = '<span class="badge text-bg-warning">Published</span>';

                    // Expandable rows to display more information
                    $expandCollapse = '<a class="dt-control text-secondary" style="cursor: pointer;"><i class="bi bi-chevron-down"></i></a>';

                    // Add the row
                    echo "table.row.add([`$expandCollapse`, {$row['id']}, '".addslashes($row['title'])."', '".date('d/m/Y', strtotime($row['event_date']))."', '".addslashes($row['location'])."', `$status`, `$actionDropdown`, '".addslashes($row['description'])."', '{$row['photo']}']).draw();\n";
                }
            }
        ?>

        // JS alert function
        function displayFlashMessage(mode, message) {
            const flashMessageContainer = document.createElement('div');
            flashMessageContainer.innerHTML = `
                <div class="alert ${mode} mt-4 py-2 fade-out-alert d-flex align-items-center position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                    <i class="bi ${mode === 'alert-success' ? "bi-check-circle" : (mode === 'alert-warning' ? "bi-exclamation-triangle" : "bi-info-circle")} me-2"></i>
                    <div>${message}</div>
                </div>
            `;
            document.body.appendChild(flashMessageContainer);

            setTimeout(() => {
                flashMessageContainer.remove();
            }, 5000);
        }
        
        // Function to delete the selected news article
        function deleteNews(id, title, row) {
            if (confirm(`Are you sure you want to delete the news article "${title}"?`)) {
                // Show loading state
                const button = row;
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Deleting...';
                button.disabled = true;
                
                // Use POST instead of DELETE for better compatibility
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                
                axios.post(`manage_news.php`, formData)
                .then(response => {
                    if (response.data.success) {
                        // Gracefully remove the row from the DataTables
                        var rowToRemove = $(row).parents('tr');
                        rowToRemove.fadeOut(250, () => {
                            table.row(rowToRemove).remove().draw();
                        });
                        displayFlashMessage("alert-success", response.data.message);
                    } else {
                        displayFlashMessage("alert-danger", response.data.error || `Error deleting news article.`);
                        // Restore button
                        button.innerHTML = originalText;
                        button.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    let errorMessage = `Error deleting news article.`;
                    
                    if (error.response && error.response.data && error.response.data.error) {
                        errorMessage = error.response.data.error;
                    }
                    
                    displayFlashMessage("alert-danger", errorMessage);
                    
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.fade-out-alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
        
        // Dashboard functions
        function refreshPage() {
            location.reload();
        }
        
        function exportData() {
            // Create CSV export
            const table = $('#eventTable').DataTable();
            const data = table.data().toArray();
            
            let csv = 'ID,Title,Type,Date,Location\n';
            data.forEach(row => {
                csv += `${row[1]},"${row[2]}","${row[3]}","${row[4]}","${row[5]}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'news_articles_' + new Date().toISOString().split('T')[0] + '.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
        
        function filterRecent() {
            // Filter to show recent news
            window.location.href = '?filterTime=Upcoming';
        }
        
        function filterPast() {
            // Filter to show past news
            window.location.href = '?filterTime=Past';
        }
        
        // Add animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s ease';
            observer.observe(el);
        });
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>