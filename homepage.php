<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitCast</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Reset and global styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f5f7fa;
            line-height: 1.6;
        }

        /* Navbar */
        nav {
            background: linear-gradient(90deg, #007bff, #00a2ff);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .logo {
            font-size: 1.8em;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 500;
            transition: 0.3s;
        }

        nav a:hover {
            color: #d8ebff;
        }

        /* Hero Section */
        .hero {
            height: 85vh;
            background: url('fitness-bg.jpg') center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            position: relative;
        }

        .hero::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 50, 100, 0.55);
            z-index: 1;
        }

        .hero h1 {
            font-size: 3em;
            z-index: 2;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hero p {
            font-size: 1.2em;
            z-index: 2;
            margin: 15px 0 25px;
            max-width: 600px;
        }

        .hero .btn {
            background: #007bff;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            border-radius: 30px;
            text-decoration: none;
            z-index: 2;
            transition: background 0.3s ease;
        }

        .hero .btn:hover {
            background: #005ecb;
        }

        /* Features Section */
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding: 60px 20px;
            background: #fff;
        }

        .feature {
            background: #eaf3ff;
            padding: 30px;
            border-radius: 12px;
            width: 300px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .feature h3 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .feature p {
            font-size: 0.95em;
            color: #555;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 15px 0;
            background: linear-gradient(90deg, #007bff, #00a2ff);
            color: white;
            font-size: 14px;
            margin-top: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2em;
            }

            .hero p {
                font-size: 1em;
                width: 90%;
            }

            nav {
                flex-direction: column;
            }

            nav a {
                margin: 10px 0;
            }

            .features {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="logo">FitCast</div>
        <div class="nav-links">
            <a href="aboutus.php">About Us</a>
            <a href="guide.php">Guide</a>
            <a href="login.php">Login</a>
            <a href="register.php">Signup</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Track. Train. Triumph.</h1>
        <p>Join FitCast — your all-in-one fitness tracker, community, and performance dashboard. Run smarter, ride stronger, live fitter.</p>
        <a href="register.php" class="btn">Get Started</a>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature">
            <h3>Activity Tracking</h3>
            <p>Monitor your runs, rides, and workouts with real-time stats and detailed insights.</p>
        </div>
        <div class="feature">
            <h3>Community Challenges</h3>
            <p>Compete with friends, join global events, and push your limits together.</p>
        </div>
        <div class="feature">
            <h3>Personal Progress</h3>
            <p>View your achievements, track goals, and celebrate milestones along the way.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        &copy; 2025 GROUP 3 PROJECT — FitCast | All Rights Reserved
    </footer>

</body>
</html>
