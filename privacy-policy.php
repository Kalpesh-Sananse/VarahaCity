<?php
require_once './includes/db_connection.php';

// Fetch active privacy policy
$query = "SELECT * FROM privacy_policy WHERE status = 'active' ORDER BY id DESC LIMIT 1";
$result = $conn->query($query);
$policy = $result->fetch_assoc();

// Check if policy exists
if (!$policy) {
    exit('Privacy Policy is currently unavailable.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Varaha City</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            color: #1B1B1B;
            line-height: 1.8;
        }

        /* Hero Section */
        .hero-section {
            background-color: #0A142F;
            padding: 80px 0;
            position: relative;
            margin-bottom: 60px;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(10, 20, 47, 0.9), rgba(10, 20, 47, 0.7));
        }

        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #ffffff;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-subtitle {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Main Content */
        .privacy-content {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            padding: 40px;
            margin-bottom: 60px;
        }

        .privacy-content h1 {
            color: #0A142F;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 30px;
        }

        .privacy-text {
            color: #1B1B1B;
            font-size: 16px;
            line-height: 1.8;
        }

        .privacy-text h1,
        .privacy-text h2,
        .privacy-text h3 {
            color: #0A142F;
            margin-top: 40px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .privacy-text h2 {
            font-size: 28px;
        }

        .privacy-text h3 {
            font-size: 24px;
        }

        .privacy-text p {
            margin-bottom: 20px;
        }

        .privacy-text ul,
        .privacy-text ol {
            margin-bottom: 20px;
            padding-left: 20px;
        }

        .privacy-text li {
            margin-bottom: 10px;
        }

        .last-updated {
            font-size: 14px;
            color: #666;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        /* Table Styles */
        .privacy-text table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .privacy-text table th,
        .privacy-text table td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .privacy-text table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        /* Responsive Styles */
        @media (max-width: 991px) {
            .hero-section {
                padding: 60px 0;
            }

            .hero-title {
                font-size: 40px;
            }

            .privacy-content {
                padding: 30px;
            }
        }

        @media (max-width: 767px) {
            .hero-section {
                padding: 40px 0;
            }

            .hero-title {
                font-size: 32px;
            }

            .privacy-content {
                padding: 20px;
            }

            .privacy-text h2 {
                font-size: 24px;
            }

            .privacy-text h3 {
                font-size: 20px;
            }
        }

        @media print {
            .hero-section {
                padding: 20px 0;
                background: #fff !important;
            }

            .hero-title,
            .hero-subtitle {
                color: #000;
            }

            .privacy-content {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Privacy Policy</h1>
                <p class="hero-subtitle">Learn how we protect and handle your information</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="privacy-content">
                    <h1><?php echo htmlspecialchars($policy['title']); ?></h1>
                    
                    <div class="privacy-text">
                        <?php echo $policy['description']; ?>
                    </div>

                    <div class="last-updated">
                        Last Updated: <?php echo date('F d, Y', strtotime($policy['updated_at'])); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>