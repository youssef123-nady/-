<?php
$conn = new mysqli("localhost", "root", "", "account_book");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST["name"])) {
    $stmt = $conn->prepare("INSERT INTO persons (name) VALUES (?)");
    $stmt->bind_param("s", $_POST["name"]);
    $stmt->execute();
}

$search = isset($_GET["search"]) ? "%" . $_GET["search"] . "%" : "%";
$stmt = $conn->prepare("SELECT * FROM persons WHERE name LIKE ?");
$stmt->bind_param("s", $search);
$stmt->execute();
$persons = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Book</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 30px;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 25px;
        }
        input[type="text"], button {
            padding: 10px;
            font-size: 16px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
        }
        button:hover {
            background-color: #0056b3;
        }
        ul {
            padding-left: 0;
            list-style: none;
        }
        li {
            margin: 10px 0;
        }
        a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Account Book</h1>

    <h2>Add New Person</h2>
    <form method="POST">
        <input type="text" name="name" placeholder="Enter name..." required>
        <button type="submit">Add</button>
    </form>

    <h2>Search Person</h2>
    <form method="GET">
        <input type="text" name="search" placeholder="Search name...">
        <button type="submit">Search</button>
    </form>

    <h2>Persons</h2>
    <ul>
        <?php while ($row = $persons->fetch_assoc()): ?>
            <li><a href="person.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></li>
        <?php endwhile; ?>
    </ul>
</div>
</body>
</html>
