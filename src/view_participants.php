<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Engaging Portal | Event Participants</title>

    <link rel="stylesheet" href="css/styles.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- DataTables Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />

    <style>
        .card {
            width: 100%;
            border: none;
            box-shadow: 0 2px 2px rgba(0,0,0,.08), 0 0 6px rgba(0,0,0,.05);
        }
        .profilePictureThumbnail {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body class="admin-bg">
    <?php
        include 'db_controller.php';
        $conn->select_db("Alumni");
        
        session_start();
        include 'logged_admin.php';

        // Get event ID from URL parameter
        $eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        
        // Get event details
        $eventQuery = $conn->prepare("SELECT * FROM event_table WHERE id = ?");
        $eventQuery->bind_param("i", $eventId);
        $eventQuery->execute();
        $eventResult = $eventQuery->get_result();
        
        if ($eventResult->num_rows == 0) {
            echo "<div class='container mt-5'><h3>Event not found</h3></div>";
            exit();
        }
        
        $event = $eventResult->fetch_assoc();
        
        // Get participants for this event
        $participantsQuery = $conn->prepare("
            SELECT u.*, er.event_id 
            FROM event_registration_table er 
            JOIN user_table u ON er.participant_email = u.email 
            WHERE er.event_id = ?
            ORDER BY u.first_name, u.last_name
        ");
        $participantsQuery->bind_param("i", $eventId);
        $participantsQuery->execute();
        $participants = $participantsQuery->get_result();
    ?>

    <!-- Top nav bar -->
    <nav class="navbar sticky-top navbar-expand-lg" style="background-color: #002c59;">
        <div class="container">
            <a class="navbar-brand mx-0 mb-0 h1 text-light" href="main_menu_admin.php">Alumni Engaging Portal</a>
            <div class="text-light">
                <i class="bi bi-people me-2"></i>Event Participants
            </div>
        </div>
    </nav>

    <div class="container my-4">
        <!-- Event Information -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <img src="images/<?php echo htmlspecialchars($event['photo']); ?>" 
                             class="img-fluid rounded" 
                             style="width: 100px; height: 100px; object-fit: cover;" 
                             alt="Event Photo">
                    </div>
                    <div class="col">
                        <h2 class="card-title mb-2"><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p class="card-text mb-1">
                            <i class="bi bi-calendar me-2"></i>
                            <?php echo date('F j, Y', strtotime($event['event_date'])); ?>
                        </p>
                        <p class="card-text mb-1">
                            <i class="bi bi-geo-alt me-2"></i>
                            <?php echo htmlspecialchars($event['location']); ?>
                        </p>
                        <span class="badge <?php echo $event['type'] == 'Event' ? 'bg-success' : 'bg-warning'; ?>">
                            <?php echo htmlspecialchars($event['type']); ?>
                        </span>
                    </div>
                    <div class="col-auto">
                        <h4 class="text-primary mb-0"><?php echo $participants->num_rows; ?></h4>
                        <small class="text-muted">Registered</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Participants List -->
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Registered Participants</h4>
            </div>
            <div class="card-body">
                <?php if ($participants->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table id="participantsTable" class="table table-hover">
                            <thead>
                                <tr class="table-primary">
                                    <th>Profile</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>College</th>
                                    <th>Batch Year</th>
                                    <th>Hometown</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($participant = $participants->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php
                                                $profileImage = 'profile_images/';
                                                if (!empty($participant['profile_image'])) {
                                                    $profileImage .= $participant['profile_image'];
                                                } else {
                                                    $profileImage .= ($participant['gender'] == 'Male') ? 
                                                        'default-male-user-profile-icon.jpg' : 
                                                        'default-female-user-profile-icon.jpg';
                                                }
                                            ?>
                                            <img src="<?php echo $profileImage; ?>" 
                                                 class="profilePictureThumbnail" 
                                                 alt="Profile Picture">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($participant['email']); ?>">
                                                <?php echo htmlspecialchars($participant['email']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($participant['college_name'] ?? 'Not specified'); ?></td>
                                        <td><?php echo htmlspecialchars($participant['batch_year'] ?? 'Not specified'); ?></td>
                                        <td><?php echo htmlspecialchars($participant['hometown']); ?></td>
                                        <td>
                                            <a href="profile_detail.php?email=<?php echo urlencode($participant['email']); ?>&admin_view=1" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank">
                                                <i class="bi bi-eye me-1"></i>View Profile
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-people display-1 text-muted"></i>
                        <h5 class="text-muted mt-3">No participants registered yet</h5>
                        <p class="text-muted">Alumni will appear here once they register for this event.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="manage_events.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Manage Events
            </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#participantsTable').DataTable({
                paging: true,
                lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
                info: true,
                searching: true,
                responsive: true,
                order: [[1, "asc"]],
                columnDefs: [
                    {
                        "targets": [0, 6],
                        "orderable": false,
                    }
                ]
            });
        });
    </script>
</body>
</html>
