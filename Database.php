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
    private $dbname = 'your_database_name';
    private $username = 'your_username';
    private $password = 'your_password';
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