<?php
    session_start();
    include 'db_controller.php';
    $conn->select_db("Alumni");

    // Check if this is an admin viewing a profile
    $isAdminView = isset($_GET['admin_view']) && $_GET['admin_view'] == '1' && isset($_SESSION['logged_account']['type']) && $_SESSION['logged_account']['type'] == 'admin';
    
    if ($isAdminView) {
        include 'logged_admin.php';
    } else {
        include 'logged_user.php';
    }
    
    include 'get_alumnus_by_email.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile | Alumni Portal</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            padding-bottom: 2rem;
        }
        
        .profile-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 2rem;
            color: white;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(30%, -30%);
        }
        
        .profile-picture-container {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 1.5rem;
        }
        
        .profile-picture {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .info-label {
            font-size: 0.875rem;
            color: #6B7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1rem;
            color: #1F2937;
            font-weight: 600;
        }
        
        .action-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .alumni-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .alumni-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.15);
        }
        
        .alumni-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }
        
        .search-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .search-input {
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 1rem;
        }
        
        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .breadcrumb {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1rem 1.5rem;
        }
        
        .breadcrumb-item a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.8);
        }
        
        @media (max-width: 768px) {
            .profile-picture-container {
                width: 150px;
                height: 150px;
            }
            
            .profile-picture {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <?php if (!$isAdminView): ?>
        <?php include 'components/nav_modern.php'; ?>
    <?php else: ?>
        <nav class="navbar navbar-expand-lg navbar-light" style="background: rgba(255, 255, 255, 0.95);">
            <div class="container">
                <a class="navbar-brand fw-bold" href="main_menu_admin.php">
                    <i class="bi bi-mortarboard text-primary"></i>
                    Alumni Portal Admin
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="main_menu_admin.php"><i class="bi bi-house-door"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_accounts.php"><i class="bi bi-people"></i> Accounts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <div class="profile-container">
        <div class="container">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="<?php echo ($isAdminView) ? 'main_menu_admin.php' : 'main_menu.php'; ?>">
                            <i class="bi bi-house-door me-1"></i>Home
                        </a>
                    </li>
                    <?php if (isset($alumnusToViewEmail) && isset($_SESSION['logged_account']['email']) && $alumnusToViewEmail != $_SESSION['logged_account']['email']): ?>
                        <li class="breadcrumb-item">
                            <a href="view_alumni.php">Alumni Directory</a>
                        </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active">
                        <?php echo isset($alumnusToView) ? htmlspecialchars($alumnusToViewName) : 'Profile'; ?>
                    </li>
                </ol>
            </nav>

            <?php if (isset($alumnusToView)): ?>
                <!-- Profile Header Card -->
                <div class="profile-header">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <div class="profile-picture-container">
                                <?php
                                    $profileImage = 'profile_images/default-male-user-profile-icon.jpg';
                                    if (isset($alumnusToViewProfilePicture) && $alumnusToViewProfilePicture != "") {
                                        $profileImage = 'profile_images/'.$alumnusToViewProfilePicture;
                                    } elseif ($alumnusToViewGender == "Female") {
                                        $profileImage = 'profile_images/default-female-user-profile-icon.jpg';
                                    }
                                ?>
                                <img src="<?php echo $profileImage; ?>" class="profile-picture" alt="Profile Picture">
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h1 class="display-4 fw-bold mb-3">
                                <?php echo isset($alumnusToViewName) ? htmlspecialchars($alumnusToViewName) : 'Alumni'; ?>
                            </h1>
                            
                            <?php if (isset($alumnusToViewJobPosition) || isset($alumnusToViewCompany)): ?>
                                <h5 class="mb-3 opacity-90">
                                    <i class="bi bi-briefcase me-2"></i>
                                    <?php 
                                        echo isset($alumnusToViewJobPosition) ? htmlspecialchars($alumnusToViewJobPosition) : '';
                                        if (isset($alumnusToViewJobPosition) && isset($alumnusToViewCompany)) echo ' at ';
                                        echo isset($alumnusToViewCompany) ? htmlspecialchars($alumnusToViewCompany) : '';
                                    ?>
                                </h5>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewCurrentLocation)): ?>
                                <p class="mb-3 opacity-90">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?php echo htmlspecialchars($alumnusToViewCurrentLocation); ?>
                                </p>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewEmail)): ?>
                                <p class="mb-4 opacity-90">
                                    <i class="bi bi-envelope me-2"></i>
                                    <a href="mailto:<?php echo htmlspecialchars($alumnusToViewEmail); ?>" class="text-white text-decoration-none">
                                        <?php echo htmlspecialchars($alumnusToViewEmail); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex flex-wrap gap-2">
                                <?php if (isset($alumnusToViewResume) && $alumnusToViewResume != ''): ?>
                                    <form action="view_resume.php" method="GET" target="_blank" class="d-inline">
                                        <input type="hidden" name="resume" value="<?php echo htmlspecialchars($alumnusToViewResume); ?>">
                                        <button type="submit" class="btn btn-light">
                                            <i class="bi bi-file-earmark-text me-2"></i>View Resume
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['logged_account']['email']) && $alumnusToViewEmail == $_SESSION['logged_account']['email']): ?>
                                    <a href="update_profile.php?email=<?php echo htmlspecialchars($_SESSION['logged_account']['email']); ?>" class="btn btn-light">
                                        <i class="bi bi-pencil me-2"></i>Edit Profile
                                    </a>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['logged_account']['type']) && $_SESSION['logged_account']['type'] == 'admin' && $alumnusToViewEmail != $_SESSION['logged_account']['email']): ?>
                                    <form action="initiate_call.php" method="POST" class="d-inline">
                                        <input type="hidden" name="callee_email" value="<?php echo htmlspecialchars($alumnusToViewEmail); ?>">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-camera-video me-2"></i>Video Call
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (isset($_SESSION['logged_account']['email']) && isset($_SESSION['logged_account']['type']) && $alumnusToViewEmail !== $_SESSION['logged_account']['email'] && $_SESSION['logged_account']['type'] !== 'admin'): ?>
                                    <button class="btn btn-light" onclick="startChat('<?php echo $alumnusToViewEmail; ?>', '<?php echo $alumnusToViewName; ?>')">
                                        <i class="bi bi-chat-dots me-2"></i>Send Message
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
       
         <!-- Profile Information Cards -->
                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-md-6 mb-4">
                        <div class="glass-card">
                            <h4 class="fw-bold text-primary mb-4">
                                <i class="bi bi-person me-2"></i>Personal Information
                            </h4>
                            
                            <?php if (isset($alumnusToViewHometown)): ?>
                                <div class="info-card">
                                    <div class="info-label">Hometown</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewHometown); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewGender)): ?>
                                <div class="info-card">
                                    <div class="info-label">Gender</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewGender); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewDOB)): ?>
                                <div class="info-card">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo date('F j, Y', strtotime($alumnusToViewDOB)); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewContactNumber)): ?>
                                <div class="info-card">
                                    <div class="info-label">Contact Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewContactNumber); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Education & Career -->
                    <div class="col-md-6 mb-4">
                        <div class="glass-card">
                            <h4 class="fw-bold text-primary mb-4">
                                <i class="bi bi-mortarboard me-2"></i>Education & Career
                            </h4>
                            
                            <?php if (isset($alumnusToViewCampus)): ?>
                                <div class="info-card">
                                    <div class="info-label">Campus</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewCampus); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewDegree)): ?>
                                <div class="info-card">
                                    <div class="info-label">Degree</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewDegree); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewDegreeYearGraduated) && $alumnusToViewDegreeYearGraduated != ''): ?>
                                <div class="info-card">
                                    <div class="info-label">Year Graduated</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewDegreeYearGraduated); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($alumnusToViewQualification)): ?>
                                <div class="info-card">
                                    <div class="info-label">Qualification</div>
                                    <div class="info-value"><?php echo htmlspecialchars($alumnusToViewQualification); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alumni Directory Section -->
                <div class="mt-5">
                    <h3 class="section-title">
                        <i class="bi bi-people me-2"></i>
                        <?php echo (isset($_SESSION['logged_account']['email']) && $alumnusToViewEmail == $_SESSION['logged_account']['email']) ? 'Your Alumni Network' : 'Other Alumni'; ?>
                    </h3>
                    
                    <!-- Search Box -->
                    <div class="search-box">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="GET">
                            <div class="input-group">
                                <input type="text" class="form-control search-input" placeholder="Search alumni by name, location, company..." 
                                       name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($alumnusToViewEmail); ?>">
                                <button class="btn action-button" type="submit">
                                    <i class="bi bi-search me-2"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Alumni Cards -->
                    <div class="row">
                        <?php
                            $loggedAccountEmail = isset($_SESSION['logged_account']['email']) ? $_SESSION['logged_account']['email'] : '';
                            $searchQuery = "";
                            
                            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
                                $searchQuery = trim(strtolower($_GET['search']));
                                
                                if ($searchQuery != "") {
                                    $allUsers = $conn->query("SELECT user_table.*, account_table.type FROM user_table 
                                        JOIN account_table ON user_table.email = account_table.email 
                                        WHERE user_table.email != '$loggedAccountEmail' 
                                        AND user_table.email != '$alumnusToViewEmail' 
                                        AND account_table.type != 'Admin' 
                                        AND account_table.status = 'Approved'
                                        AND (LOWER(user_table.email) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.first_name) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.last_name) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.hometown) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.current_location) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.job_position) LIKE '%$searchQuery%' 
                                        OR LOWER(user_table.company) LIKE '%$searchQuery%')");
                                } else {
                                    $allUsers = $conn->query("SELECT user_table.*, account_table.type FROM user_table 
                                        JOIN account_table ON user_table.email = account_table.email 
                                        WHERE user_table.email != '$loggedAccountEmail' 
                                        AND user_table.email != '$alumnusToViewEmail' 
                                        AND account_table.type != 'Admin'
                                        AND account_table.status = 'Approved'");
                                }
                            } else {
                                $allUsers = $conn->query("SELECT user_table.*, account_table.type FROM user_table 
                                    JOIN account_table ON user_table.email = account_table.email 
                                    WHERE user_table.email != '$loggedAccountEmail' 
                                    AND user_table.email != '$alumnusToViewEmail' 
                                    AND account_table.type != 'Admin'
                                    AND account_table.status = 'Approved'");
                            }
                            
                            if ($allUsers && $allUsers->num_rows > 0) {
                                while ($user = $allUsers->fetch_assoc()) {
                                    $listProfilePicture = "profile_images/default-male-user-profile-icon.jpg";
                                    if ($user['gender'] == "Female") {
                                        $listProfilePicture = "profile_images/default-female-user-profile-icon.jpg";
                                    }
                                    if ($user['profile_image'] != null) {
                                        $listProfilePicture = "profile_images/".$user['profile_image'];
                                    }
                                    
                                    echo '
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <a href="profile_detail.php?email='.$user['email'].'" class="text-decoration-none">
                                            <div class="alumni-card">
                                                <div class="d-flex align-items-center">
                                                    <img src="'.$listProfilePicture.'" class="alumni-avatar me-3" alt="Profile">
                                                    <div class="flex-grow-1">
                                                        <h5 class="fw-bold mb-1 text-dark">'.$user['first_name'].' '.$user['last_name'].'</h5>
                                                        <p class="text-muted mb-1 small">
                                                            <i class="bi bi-geo-alt me-1"></i>'.(isset($user['hometown']) ? htmlspecialchars($user['hometown']) : 'N/A').'
                                                        </p>
                                                        '.(isset($user['company']) && $user['company'] != '' ? '<p class="text-muted mb-0 small"><i class="bi bi-briefcase me-1"></i>'.htmlspecialchars($user['company']).'</p>' : '').'
                                                    </div>
                                                    <i class="bi bi-chevron-right text-primary"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>';
                                }
                            } elseif ($allUsers && $allUsers->num_rows == 0 && $searchQuery != "") {
                                echo '
                                <div class="col-12">
                                    <div class="glass-card text-center py-5">
                                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3 text-muted">No results found for: "'.htmlspecialchars($_GET['search']).'"</h5>
                                        <p class="text-muted">Try searching with different keywords</p>
                                    </div>
                                </div>';
                            } else {
                                echo '
                                <div class="col-12">
                                    <div class="glass-card text-center py-5">
                                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                        <h5 class="mt-3 text-muted">No other alumni found</h5>
                                    </div>
                                </div>';
                            }
                        ?>
                    </div>
                </div>

            <?php else: ?>
                <div class="glass-card text-center py-5">
                    <i class="bi bi-person-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3 text-muted">User not found</h4>
                    <p class="text-muted">The profile you're looking for doesn't exist.</p>
                    <a href="view_alumni.php" class="btn action-button mt-3">
                        <i class="bi bi-arrow-left me-2"></i>Back to Alumni Directory
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function startChat(userEmail, userName) {
            fetch('start_conversation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ other_user_email: userEmail })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    window.location.href = 'messages.php';
                } else {
                    alert('Failed to start conversation: ' + (result.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start conversation');
            });
        }
    </script>
</body>
</html>