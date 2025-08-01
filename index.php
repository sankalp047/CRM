<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FunAsia CRM</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa; /* Light grey background */
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Ensure the body takes at least the full viewport height */
        }

        /* Navbar */
        .navbar {
            background-color: #343a40; /* Dark navbar */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Hero Section */
        .hero-section {
            background-color: #f8f9fa; /* Light grey background */
            color: #333; /* Dark text for contrast */
            padding: 100px 0;
            text-align: center;
            flex: 1; /* Allow the hero section to grow and push the footer down */
        }

        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 40px;
            color: #666; /* Slightly lighter text for the description */
        }

        /* Button Container */
        .button-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .button-container .btn {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 50px; /* Rounded buttons */
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: #007bff; /* Blue buttons */
            color: white;
            border: none;
        }

        .button-container .btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Footer */
        .footer {
            background-color: #343a40; /* Dark footer */
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: auto; /* Push the footer to the bottom */
        }

        .footer p {
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">FunAsia CRM</a>
            <!-- Empty Navbar (no links) -->
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Welcome to FunAsia CRM</h1>
            <p>Your gateway to seamless sales, production, and administration management.</p>
            <div class="button-container">
                <a href="sales_admin.php" class="btn">Sales Admin</a>
                <a href="production_admin.php" class="btn">Production Admin</a>
                <a href="super_admin.php" class="btn">Super Admin</a>
                <a href="sales_rep.php" class="btn">Sales Rep</a>
                <a href="radio_jockey.php" class="btn">Radio Jockey</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 FunAsia CRM. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>