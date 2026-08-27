<?php
// Start output buffering to prevent header issues
ob_start();

// Start session and include files BEFORE any HTML output
session_start();
include 'db_controller.php';
$conn->select_db("Alumni");
include 'logged_user.php';

// Search functionality
$search = $_GET['search'] ?? '';
$whereClause = "";

if (!empty($search)) {
    $trimSearch = strtolower(trim($search));
    $whereClause = "WHERE e.type = 'News' AND (
        LOWER(e.title) LIKE '%$trimSearch%' OR 
        LOWER(e.location) LIKE '%$trimSearch%' OR 
        LOWER(e.description) LIKE '%$trimSearch%' OR 
        LOWER(e.event_date) LIKE '%$trimSearch%'
    )";
} else {
    $whereClause = "WHERE e.type = 'News'";
}

// Query for news only
$allNews = $conn->query("SELECT e.*, 
    COALESCE(COUNT(er.participant_email), 0) as participant_count 
    FROM event_table e 
    LEFT JOIN event_registration_table er ON e.id = er.event_id 
    $whereClause
    GROUP BY e.id 
    ORDER BY e.event_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Updates | Alumni Portal</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Modern Design System -->

    <style>
        /* Match Alumni Dashboard Design */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            padding: 2rem 0;
            min-height: calc(100vh - 80px);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .page-description {
            color: #718096;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        
        .search-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .news-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        
        .news-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        
        .news-card:hover .news-image {
            transform: scale(1.05);
        }
        
        .news-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .news-category-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 2;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }
        
        .news-date {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .news-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            line-height: 1.4;
            min-height: 2.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .news-location {
            color: #718096;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            min-height: 1.5rem;
        }
        
        .news-description {
            color: #4a5568;
            font-size: 0.875rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .news-stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid rgba(226, 232, 240, 0.5);
            margin-top: auto;
            flex-shrink: 0;
        }
        
        .reader-count {
            color: #718096;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-read {
            background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%);
            color: white;
        }
        
        .btn-read:hover {
            background: linear-gradient(135deg, #dd6b20 0%, #c05621 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(237, 137, 54, 0.4);
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: white;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.5);
        }
        
        /* Animation for cards */
        .news-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem 0;
            }
            
            .welcome-card {
                padding: 1.5rem;
                margin-bottom: 1.5rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .search-card {
                padding: 1rem;
            }
            
            .news-stats {
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }
            
            .news-stats .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <!-- Navigation -->
    <?php include 'components/nav_modern.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-card">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h1 class="page-title">
                                    <i class="bi bi-newspaper me-3"></i>News & Updates
                                </h1>
                                <p class="page-description">Stay informed with the latest news and updates from our alumni community.</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <?php include 'components/alumni_logo.php'; ?>
                            </div>
                            <div class="col-md-3 text-end">
                                <a href="add_event.php?type=News" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>Post News
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Search -->
        <div class="search-card slide-left">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="GET">
                <div class="row g-3 align-items-center">
                    <div class="col-md-9">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" 
                                   placeholder="Search news and updates..." 
                                   name="search" 
                                   value="<?php echo isset($_GET['search']) ? htmlspecialchars(trim($_GET['search'])) : ''; ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bi bi-search me-2"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- News Grid -->
        <div class="row">
            <?php if ($allNews && $allNews->num_rows > 0): ?>
                <?php while ($row = $allNews->fetch_assoc()): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="news-card">
                            <!-- News Image -->
                            <div class="position-relative">
                                <img src="images/<?php echo htmlspecialchars($row['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($row['title']); ?>" 
                                     class="news-image w-100">
                                <div class="news-category-badge">
                                    News
                                </div>
                            </div>
                            
                            <!-- News Content -->
                            <div class="p-3 d-flex flex-column h-100">
                                <div class="news-date">
                                    <i class="bi bi-calendar me-2"></i>
                                    <?php echo date('M j, Y', strtotime($row['event_date'])); ?>
                                </div>
                                
                                <h5 class="news-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                
                                <div class="news-location">
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <?php echo htmlspecialchars($row['location']); ?>
                                </div>
                                
                                <p class="news-description"><?php echo htmlspecialchars($row['description']); ?></p>
                                
                                <div class="news-stats mt-auto">
                                    <div class="reader-count">
                                        <i class="bi bi-eye me-1"></i>
                                        <?php echo $row['participant_count']; ?> readers
                                    </div>
                                    
                                    <button class="btn btn-read" onclick="readMore(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-book me-1"></i>Read More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <i class="bi bi-newspaper"></i>
                        <h4>No News Found</h4>
                        <p>
                            <?php if (!empty($search)): ?>
                                No news articles match your search for "<?php echo htmlspecialchars($search); ?>".
                                <br><a href="view_news_modern.php" class="text-primary">View all news</a>
                            <?php else: ?>
                                Check back later for the latest news and updates from our alumni community.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function readMore(newsId) {
            // You can implement a detailed view modal or redirect to a detail page
            alert('Read more about news ID: ' + newsId);
        }
    </script>
</body>
</html>
<?php
$conn->close();
ob_end_flush();
?>