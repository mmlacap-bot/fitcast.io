<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guide - WebSys Project</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Reset default margin/padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fb;
            color: #333;
            line-height: 1.6;
        }

        /* Navigation bar */
        nav {
            background-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 12px 0;
        }

        nav ul li {
            margin: 0 18px;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            transition: 0.3s ease;
            font-size: 16px;
        }

        nav ul li a:hover,
        nav ul li a.active {
            color: #ffe680;
        }

        /* Main content */
        main {
            max-width: 900px;
            background: #fff;
            margin: 40px auto;
            padding: 40px 50px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.7s ease-in-out;
        }

        main h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            font-size: 2em;
            font-weight: 700;
        }

        section {
            margin-bottom: 35px;
        }

        section h2 {
            color: #0056b3;
            margin-bottom: 12px;
            border-left: 5px solid #007bff;
            padding-left: 10px;
            font-size: 1.3em;
        }

        section p,
        section ol,
        section ul {
            margin-left: 15px;
            color: #444;
        }

        ol li, ul li {
            margin: 8px 0;
        }

        strong {
            color: #007bff;
        }

        a {
            color: #0056b3;
            text-decoration: none;
            font-weight: 600;
        }

        a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 15px;
            color: #777;
            font-size: 14px;
            margin-top: 20px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav>
        <ul>
            <li><a href="homepage.php">Home</a></li>
            <li><a href="aboutus.php">About US</a></li>
            <li><a href="guide.php">Guide</a></li>
        </ul>
    </nav>

    <main>
        <h1>User Guide</h1>
        <section>
            <h2>Welcome!</h2>
            <p>
                This guide will help you understand how to use the application effectively.
            </p>
        </section>
        <section>
            <h2>Getting Started</h2>
            <ol>
                <li>Register for an account or log in if you already have one.</li>
                <li>Navigate through the menu to access different features.</li>
                <li>Use the <strong>Features</strong> page to explore what you can do.</li>
            </ol>
        </section>
        <section>
            <h2>Common Tasks</h2>
            <ul>
                <li><strong>Adding Data:</strong> Go to the relevant section and click "Add". Fill out the form and submit.</li>
                <li><strong>Editing Data:</strong> Find the item you want to edit and click the "Edit" button.</li>
                <li><strong>Deleting Data:</strong> Click the "Delete" button next to the item you wish to remove.</li>
                <li><strong>Contact Support:</strong> Use the <a href="contact.php">Contact</a> page for help.</li>
            </ul>
        </section>
        <section>
            <h2>Navigation</h2>
            <p>
                Use the navigation bar at the top of every page to move between Home, About, Features, Contact, and this Guide.
            </p>
        </section>
        <section>
            <h2>Need More Help?</h2>
            <p>
                If you have any questions, please visit the <a href="contact.php">Contact</a> page.
            </p>
        </section>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> WebSys Project | All rights reserved.
    </footer>
</body>
</html>
