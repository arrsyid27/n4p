<?php
/**
 * N4P (Not4Posers) POS System
 * Home/Landing Page
 */

require_once './includes/config.php';
require_once './includes/functions.php';

// If logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: ' . APP_URL . '/pages/dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>N4P - Not4Posers POS System</title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css">
    <style>
        /* video hero style */
        .hero {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }

        .hero video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            transform: translate(-50%, -50%);
            z-index: -1;
        }

        .hero-content {
            z-index: 1;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 900;
            letter-spacing: -2px;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-top: 1rem;
            opacity: 0.8;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-white {
            background: white;
            color: #667eea;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .features {
            padding: 4rem 2rem;
            background: #f8f9fa;
        }

        .features-content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .features-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .feature-description {
            color: #6b7280;
            line-height: 1.6;
        }

        @media (max-width: 768px) {
            .hero {
                padding: 4rem 1.5rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }

            .features-title {
                font-size: 1.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section (video background) -->
    <section class="hero">
        <video autoplay muted loop playsinline>
            <source src="<?php echo APP_URL; ?>/assets/video/background.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        <div class="hero-content">
            <h1 class="hero-title">Not4Posers</h1>
            <p class="hero-subtitle">POS System</p>
            <div class="hero-buttons">
                <a href="<?php echo APP_URL; ?>/pages/login.php" class="btn-white">Sign In</a>
            </div>
        </div>
    </section>



                <div class="feature-card">
                    <div class="feature-icon">📦</div>
                    <div class="feature-title">Inventory Management</div>
                    <div class="feature-description">
                        Track stock levels, low stock alerts, and automatic inventory adjustments
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <div class="feature-title">Sales Analytics</div>
                    <div class="feature-description">
                        Comprehensive reports on sales trends, best sellers, and daily statistics
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">💳</div>
                    <div class="feature-title">Flexible Payment</div>
                    <div class="feature-description">
                        Support multiple payment methods including cash, card, and transfer
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <div class="feature-title">Multi-User</div>
                    <div class="feature-description">
                        Manage multiple cashiers with different access levels and permissions
                    </div>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📱</div>
                    <div class="feature-title">Responsive Design</div>
                    <div class="feature-description">
                        Works perfectly on desktop, tablet, and mobile devices
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background: #1f2937; color: white; padding: 2rem; text-align: center;">
        <p>&copy; 2024 N4P - Not4Posers POS System. All rights reserved.</p>
    </footer>
</body>
</html>
