<?php

include('main.html');                                       //HTML хуудас

$host = 'db';
$db = 'mydatabase';
$user = 'db_user';
$pass = 'db_password';
$charset = 'utf8mb4';

$mysqli = new mysqli($host, $user, $pass, $db);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Create tables if not exist
$mysqli->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100)
)");

$mysqli->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_name VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

$mysqli->query("CREATE TABLE IF NOT EXISTS user_img (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    img_data VARBINARY(MAX),
    FOREIGN KEY (user_id) REFERENCES users(id)
)");

//Inserting data
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['submit_user'])) {
        $name = $_POST['name'];
        $email = $_POST['email'];

        $userInsertQuery = "INSERT INTO users (name, email) VALUES ('$name', '$email')";
        
        if ($mysqli->query($userInsertQuery) === TRUE) {
            echo "<br>Хэрэглэгчийн мэдээллийг амжилттай нэмлээ";
        } else {
            echo "Алдаа: " . $userInsertQuery . "<br>" . $mysqli->error;
        }
    }

    if (isset($_POST['submit_order'])) {
        $userId = $_POST['user_id'];
        $productName = $_POST['product_name'];

        $orderInsertQuery = "INSERT INTO orders (user_id, product_name) VALUES ('$userId', '$productName')";
        
        if ($mysqli->query($orderInsertQuery) === TRUE) {
            echo "<br>Захиалгын мэдээллийг амжилттай нэмлээ";
        } else {
            echo "Алдаа: " . $orderInsertQuery . "<br>" . $mysqli->error;
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_data'])) {
    
        $deleteUsersQuery = "DELETE FROM orders";
        if ($mysqli->query($deleteUsersQuery) === TRUE) {
            echo "<br>Захиалгын мэдээллийг амжилттай устгалаа";
        } else {
            echo "Алдаа " . $mysqli->error;
        }

        $deleteUsersQuery = "DELETE FROM users";
        if ($mysqli->query($deleteUsersQuery) === TRUE) {
            echo "<br>Хэрэглэгчийн мэдээллийг амжилттай устгалаа";
        } else {
            echo "Алдаа " . $mysqli->error;
        }
    
        // You can perform similar delete operations for other tables or data as needed
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['show_data'])) {
    
        echo "<h1>Users</h1>";
        $resultUsers = $mysqli->query("SELECT * FROM users");
        while ($row = $resultUsers->fetch_assoc()) {
            echo "<p>Хэрэглэгчийн ID: {$row['id']}, Нэр: {$row['name']}, И-майл: {$row['email']}</p>";
        }
        
        echo "<h1>Orders</h1>";
        $resultOrders = $mysqli->query("SELECT * FROM orders");
        while ($row = $resultOrders->fetch_assoc()) {
            echo "<p>Захиалгын ID: {$row['id']}, Хэрэглэгчийн ID: {$row['user_id']}, Барааны нэр: {$row['product_name']}</p>";
        }
    }

}
$mysqli->close();
?>
