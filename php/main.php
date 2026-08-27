<?php
    include 'init_db.php';

    session_start();
    $tempFlash = null;
    if (isset($_SESSION['flash_mode']) && $_SESSION['flash_mode'] == "alert-success"){
        $tempFlash = $_SESSION['flash_mode'];
        unset($_SESSION['flash_mode']);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Portal | Connect, Engage, Thrive</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 2rem 0;
        }

        .hero-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
            border-radius: 30px 30px 0 0;
        }

        .hero-title {
            font-family: 'Poppins', sans-serif;
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: #64748b;
            margin-bottom: 2rem;
            line-height: 1.6;
            font-weight: 400;
        }

        .hero-description {
            font-size: 1.1rem;
            color: #475569;
            margin-bottom: 3rem;
            line-height: 1.7;
        }

        .btn-hero {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 180px;
            justify-content: center;
        }

        .btn-primary-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-primary-hero:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-outline-hero {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-outline-hero:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .hero-logo {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 500px;
        }

        .alumni-logo-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            max-width: 500px;
            width: 100%;
        }

        .alumni-logo-container:hover {
            transform: translateY(-10px);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.2);
        }

        .alumni-logo {
            width: 300px;
            height: 300px;
            margin-bottom: 2rem;
            filter: drop-shadow(0 10px 20px rgba(102, 126, 234, 0.2));
            animation: logoFloat 6s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }

        .logo-text-overlay {
            text-align: center;
            width: 100%;
        }

        .logo-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .logo-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .logo-stats {
            display: flex;
            justify-content: space-around;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            flex: 1;
            transition: all 0.3s ease;
        }

        .stat-item:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Features Section */
        .features {
            margin-top: 4rem;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .feature-description {
            color: #64748b;
            line-height: 1.6;
        }

        /* Floating Elements */
        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 20%;
            right: 10%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 15%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Alert Styles */
        .alert-modern {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 1000;
            min-width: 300px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .hero-content {
                padding: 2rem;
                margin: 1rem;
            }

            .btn-hero {
                padding: 0.8rem 2rem;
                font-size: 1rem;
                width: 100%;
                margin-bottom: 1rem;
            }

            .hero-logo {
                margin-top: 2rem;
                min-height: 400px;
            }

            .alumni-logo-container {
                padding: 2rem;
                max-width: 100%;
            }

            .alumni-logo {
                width: 250px;
                height: 250px;
            }

            .logo-title {
                font-size: 1.5rem;
            }

            .logo-stats {
                flex-direction: column;
                gap: 0.5rem;
            }

            .stat-item {
                padding: 0.75rem;
            }

            .features {
                margin-top: 2rem;
            }

            .feature-card {
                margin-bottom: 1rem;
            }
        }

        /* Animation Classes */
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }

        .fade-in-left {
            animation: fadeInLeft 0.8s ease-out;
        }

        .fade-in-right {
            animation: fadeInRight 0.8s ease-out;
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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-element">
        <i class="bi bi-mortarboard" style="font-size: 4rem; color: rgba(255,255,255,0.1);"></i>
    </div>
    <div class="floating-element">
        <i class="bi bi-people" style="font-size: 3rem; color: rgba(255,255,255,0.1);"></i>
    </div>
    <div class="floating-element">
        <i class="bi bi-chat-dots" style="font-size: 3.5rem; color: rgba(255,255,255,0.1);"></i>
    </div>

    <!-- Flash Message -->
    <?php if (isset($_SESSION['flash_mode']) || isset($tempFlash)): ?>
        <div class="alert alert-modern <?php echo (isset($_SESSION['flash_mode'])) ? $_SESSION['flash_mode'] : '' . (isset($tempFlash) ? $tempFlash : ''); ?> fade-in-up" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi <?php echo (isset($tempFlash) && $tempFlash == "alert-success" ? "bi-check-circle" : ((isset($_SESSION['flash_mode']) && $_SESSION['flash_mode'] == "alert-primary" ? "bi-info-circle" : ""))) ?> me-2"></i>
                <div><?php echo isset($_SESSION['flash']) ? $_SESSION['flash'] : '' ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Content Column -->
                <div class="col-lg-6 col-md-12">
                    <div class="hero-content fade-in-left">
                        <h1 class="hero-title">
                            Alumni Engaging Portal
                        </h1>
                        <p class="hero-subtitle">
                            Connect • Network • Thrive
                        </p>
                        <p class="hero-description">
                            Stay connected with your alma mater and fellow graduates. Build meaningful professional networks, discover career opportunities, and propel your journey to the next stage of success.
                        </p>
                        
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="login.php" class="btn btn-primary-hero btn-hero">
                                <i class="bi bi-box-arrow-in-right"></i>
                                Login to Portal
                            </a>
                            <a href="registration.php" class="btn btn-outline-hero btn-hero">
                                <i class="bi bi-person-plus"></i>
                                Join Community
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Logo Column -->
                <div class="col-lg-6 col-md-12">
                    <div class="hero-logo fade-in-right">
                        <div class="alumni-logo-container">
                            <svg class="alumni-logo" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
                                <!-- Background Circle -->
                                <defs>
                                    <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                    </linearGradient>
                                    <linearGradient id="accentGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" style="stop-color:#f093fb;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#f5576c;stop-opacity:1" />
                                    </linearGradient>
                                    <filter id="glow">
                                        <feGaussianBlur stdDeviation="3" result="coloredBlur"/>
                                        <feMerge> 
                                            <feMergeNode in="coloredBlur"/>
                                            <feMergeNode in="SourceGraphic"/>
                                        </feMerge>
                                    </filter>
                                </defs>
                                
                                <!-- Main Circle Background -->
                                <circle cx="200" cy="200" r="180" fill="url(#logoGradient)" opacity="0.1"/>
                                <circle cx="200" cy="200" r="160" fill="url(#logoGradient)" opacity="0.2"/>
                                <circle cx="200" cy="200" r="140" fill="url(#logoGradient)" opacity="0.3"/>
                                
                                <!-- Graduation Cap -->
                                <g transform="translate(200,150)">
                                    <!-- Cap Base -->
                                    <rect x="-50" y="-10" width="100" height="8" fill="url(#logoGradient)" rx="4"/>
                                    <!-- Cap Top -->
                                    <rect x="-60" y="-18" width="120" height="4" fill="url(#logoGradient)" rx="2"/>
                                    <!-- Tassel -->
                                    <line x1="45" y1="-14" x2="65" y2="10" stroke="url(#accentGradient)" stroke-width="3" stroke-linecap="round"/>
                                    <circle cx="65" cy="10" r="4" fill="url(#accentGradient)"/>
                                </g>
                                
                                <!-- Book/Knowledge Symbol -->
                                <g transform="translate(200,220)">
                                    <rect x="-30" y="-15" width="60" height="30" fill="url(#logoGradient)" rx="3" opacity="0.8"/>
                                    <rect x="-25" y="-10" width="50" height="20" fill="white" rx="2" opacity="0.9"/>
                                    <line x1="-15" y1="-5" x2="15" y2="-5" stroke="url(#logoGradient)" stroke-width="2"/>
                                    <line x1="-15" y1="0" x2="15" y2="0" stroke="url(#logoGradient)" stroke-width="2"/>
                                    <line x1="-15" y1="5" x2="10" y2="5" stroke="url(#logoGradient)" stroke-width="2"/>
                                </g>
                                
                                <!-- Network Connections -->
                                <g opacity="0.6">
                                    <circle cx="120" cy="120" r="8" fill="url(#accentGradient)"/>
                                    <circle cx="280" cy="120" r="8" fill="url(#accentGradient)"/>
                                    <circle cx="120" cy="280" r="8" fill="url(#accentGradient)"/>
                                    <circle cx="280" cy="280" r="8" fill="url(#accentGradient)"/>
                                    
                                    <line x1="120" y1="120" x2="200" y2="150" stroke="url(#accentGradient)" stroke-width="2" opacity="0.5"/>
                                    <line x1="280" y1="120" x2="200" y2="150" stroke="url(#accentGradient)" stroke-width="2" opacity="0.5"/>
                                    <line x1="120" y1="280" x2="200" y2="220" stroke="url(#accentGradient)" stroke-width="2" opacity="0.5"/>
                                    <line x1="280" y1="280" x2="200" y2="220" stroke="url(#accentGradient)" stroke-width="2" opacity="0.5"/>
                                </g>
                                
                                <!-- Alumni Text -->
                                <text x="200" y="320" text-anchor="middle" fill="url(#logoGradient)" font-family="Poppins, sans-serif" font-size="24" font-weight="700">ALUMNI</text>
                                <text x="200" y="345" text-anchor="middle" fill="url(#logoGradient)" font-family="Inter, sans-serif" font-size="14" font-weight="500" opacity="0.8">NETWORK</text>
                            </svg>
                            
                            <!-- Logo Text Overlay -->
                            <div class="logo-text-overlay">
                                <h2 class="logo-title">Alumni Network</h2>
                                <p class="logo-subtitle">Connecting Graduates Worldwide</p>
                                <div class="logo-stats">
                                    <div class="stat-item">
                                        <span class="stat-number">10K+</span>
                                        <span class="stat-label">Alumni</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">50+</span>
                                        <span class="stat-label">Countries</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">100+</span>
                                        <span class="stat-label">Events</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="features fade-in-up">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h3 class="feature-title">Connect & Network</h3>
                            <p class="feature-description">
                                Build lasting relationships with fellow alumni and expand your professional network across industries and locations.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <h3 class="feature-title">Events & Opportunities</h3>
                            <p class="feature-description">
                                Stay updated with exclusive alumni events, career fairs, and professional development opportunities.
                            </p>
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-6">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <h3 class="feature-title">Share & Collaborate</h3>
                            <p class="feature-description">
                                Exchange ideas, share experiences, and collaborate on projects with your alumni community.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Parallax effect for floating elements
            const floatingElements = document.querySelectorAll('.floating-element');
            
            window.addEventListener('mousemove', (e) => {
                const mouseX = e.clientX / window.innerWidth;
                const mouseY = e.clientY / window.innerHeight;
                
                floatingElements.forEach((element, index) => {
                    const speed = (index + 1) * 0.5;
                    const x = (mouseX - 0.5) * speed * 50;
                    const y = (mouseY - 0.5) * speed * 50;
                    
                    element.style.transform = `translate(${x}px, ${y}px)`;
                });
            });

            // Auto-hide flash messages
            const flashAlert = document.querySelector('.alert-modern');
            if (flashAlert) {
                setTimeout(() => {
                    flashAlert.style.opacity = '0';
                    flashAlert.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        flashAlert.remove();
                    }, 300);
                }, 5000);
            }

            // Smooth scroll for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states to buttons
            document.querySelectorAll('.btn-hero').forEach(button => {
                button.addEventListener('click', function(e) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-arrow-clockwise me-2"></i>Loading...';
                    this.style.pointerEvents = 'none';
                    
                    // Reset after navigation (in case of back button)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.style.pointerEvents = 'auto';
                    }, 2000);
                });
            });

            // Add intersection observer for animations
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

            // Observe feature cards for staggered animation
            document.querySelectorAll('.feature-card').forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `all 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });

        // Add some dynamic background effects
        function createParticle() {
            const particle = document.createElement('div');
            particle.style.position = 'fixed';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.background = 'rgba(255, 255, 255, 0.3)';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '1';
            
            const startX = Math.random() * window.innerWidth;
            const startY = window.innerHeight + 10;
            const endY = -10;
            const duration = Math.random() * 3000 + 2000;
            
            particle.style.left = startX + 'px';
            particle.style.top = startY + 'px';
            
            document.body.appendChild(particle);
            
            particle.animate([
                { transform: 'translateY(0px)', opacity: 0 },
                { transform: 'translateY(-20px)', opacity: 1 },
                { transform: `translateY(${endY - startY}px)`, opacity: 0 }
            ], {
                duration: duration,
                easing: 'linear'
            }).onfinish = () => {
                particle.remove();
            };
        }

        // Create particles periodically
        setInterval(createParticle, 2000);
    </script>
</body>
</html>