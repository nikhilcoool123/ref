<?php

// Full stack development for "Refer & Earn" platform
// Classy and branded for educational purposes

// Enhancements and features:
// 1. User-friendly interface with CSS for a classy look.
// 2. Personalized dashboard with analytics for referrals and earnings.
// 3. Email notifications for successful referrals and purchases.
// 4. Dynamic referral tracking with QR codes.
// 5. Gamification: Achievements and badges for milestones.
// 6. Admin panel for managing users, courses, and referrals.

// Directory structure suggestion:
// assets/css/ - for styles
// assets/js/ - for interactivity
// assets/images/ - for branding images
// views/ - for HTML templates
// controllers/ - for PHP logic
// models/ - for database interactions
// index.php - Entry point

// Database connection setup
namespace Models;

use PDO;
use PDOException;

class Database {
    private $host = 'localhost';
    private $dbname = 'test';
    private $username = 'root';
    private $password = 'root';
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}

// Initializing core development
require_once 'models/Database.php';
require_once 'controllers/UserController.php';
require_once 'controllers/ReferralController.php';
require_once 'controllers/CourseController.php';
require_once 'views/Header.php';
require_once 'views/Footer.php';

// Entry point for the platform
class ReferAndEarnPlatform {
    public function __construct() {
        $this->route();
    }

    private function route() {
        $page = $_GET['page'] ?? 'home';

        switch ($page) {
            case 'register':
                (new UserController())->register();
                break;
            case 'dashboard':
                (new UserController())->dashboard();
                break;
            case 'course':
                (new CourseController())->viewCourses();
                break;
            case 'purchase':
                (new ReferralController())->handlePurchase();
                break;
            default:
                $this->home();
        }
    }

    private function home() {
        include 'views/Home.php';
    }
}

// User registration logic
namespace Controllers;

use Models\Database;

class UserController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $email = $_POST['email'];
            $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $referral_code = bin2hex(random_bytes(4));

            $query = "INSERT INTO fusers (username, email, password, referral_code) VALUES (:username, :email, :password, :referral_code)";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':referral_code', $referral_code);

            if ($stmt->execute()) {
                header("Location: index.php?page=dashboard&success=1");
            } else {
                echo "Error: Unable to register.";
            }
        } else {
            include 'views/Register.php';
        }
    }

    public function dashboard() {
        $query = "SELECT COUNT(*) AS total_referrals, SUM(earnings) AS total_earnings FROM referrals WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $referral_data_query = "SELECT monthname(created_at) as month, COUNT(*) as referrals FROM referrals WHERE user_id = :user_id GROUP BY month";
        $data_stmt = $this->db->prepare($referral_data_query);
        $data_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $data_stmt->execute();
        $referral_data = $data_stmt->fetchAll(PDO::FETCH_ASSOC);

        include 'views/Dashboard.php';
    }
}

// Course handling logic
class CourseController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function viewCourses() {
        $query = "SELECT * FROM courses";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include 'views/Courses.php';
    }
}

// View: Registration Form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="registration-container">
        <h1>Register</h1>
        <form method="POST" action="?page=register">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>


<!-- Dashboard View -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome to Your Dashboard</h1>
        </header>
        <section class="analytics">
            <h2>Your Referrals</h2>
            <p>Total Referrals: <span id="total-referrals"><?= $stats['total_referrals'] ?? 0 ?></span></p>
            <p>Total Earnings: <span id="total-earnings">$<?= number_format($stats['total_earnings'] ?? 0, 2) ?></span></p>
        </section>
        <section class="performance">
            <h2>Performance Overview</h2>
            <canvas id="performance-chart" width="400" height="200"></canvas>
        </section>
        <section class="actions">
            <h2>Share and Earn</h2>
            <p>Your Referral Link:</p>
            <input type="text" value="https://yourplatform.com/referral/yourcode" readonly>
            <button onclick="copyReferralLink()">Copy Link</button>
        </section>
    </div>

    <script>
        function copyReferralLink() {
            const linkInput = document.querySelector('input[type="text"]');
            linkInput.select();
            document.execCommand('copy');
            alert('Referral link copied to clipboard!');
        }

        // Dynamic data for the chart
        const ctx = document.getElementById('performance-chart').getContext('2d');
        const performanceData = <?= json_encode(array_map(function($data) {
            return ['label' => $data['month'], 'data' => $data['referrals']];
        }, $referral_data)) ?>;

        const labels = performanceData.map(d => d.label);
        const data = performanceData.map(d => d.data);

        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Referrals',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
