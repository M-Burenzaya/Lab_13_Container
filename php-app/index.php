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
    img_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    img_data LONGBLOB,
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

    if (isset($_POST['submit_img'])) {
        if (isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] === UPLOAD_ERR_OK) {

            $user_id = $_POST['user_id'];
            $imgTmpName = $_FILES['imageUpload']['tmp_name'];
            $imgData = file_get_contents($imgTmpName);

            $stmt = $mysqli->prepare("INSERT INTO user_img (user_id, img_data) VALUES (?, ?)");
            $stmt->bind_param("is", $user_id, $imgData);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "Зураг амжилттай нэмэгдлээ";
            } else {
                echo "Алдаа_1" . $mysqli->error;
            }
        } else {
            echo "Алдаа_2";
        }
    }

    if (isset($_POST['clear_data'])) {

        if (($mysqli->query($resetAutoIncrementQuery = "ALTER TABLE orders AUTO_INCREMENT = 1") &&
            $mysqli->query($deleteUsersQuery = "DELETE FROM orders") &&

            $mysqli->query($resetAutoIncrementQuery = "ALTER TABLE user_img AUTO_INCREMENT = 1") &&
            $mysqli->query($deleteUsersQuery = "DELETE FROM user_img") &&

            $mysqli->query($resetAutoIncrementQuery = "ALTER TABLE users AUTO_INCREMENT = 1") &&
            $mysqli->query($deleteUsersQuery = "DELETE FROM users"))  === 1) {
            echo "Done";
        }
    }

    if (isset($_POST['show_data'])) {

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
        
        $result = $mysqli->query("SELECT img_id, user_id FROM user_img");

        $result = $mysqli->query("SELECT img_data FROM user_img");

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<img src="data:image/jpeg;base64,' . base64_encode($row['img_data']) . '" />';
            }
        } else {
            echo 'No images found';
        }
    }
}
$mysqli->close();
