<?php
$conn = new mysqli("localhost", "root", "", "account_book");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$person_id = $_GET["id"] ?? 0;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["delete_id"])) {
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $_POST["delete_id"]);
        $stmt->execute();
    } elseif (!empty($_POST["description"])) {
        $stmt = $conn->prepare("INSERT INTO transactions (person_id, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $person_id, $_POST["description"], $_POST["price"]);
        $stmt->execute();
    } elseif (isset($_POST["subtract_amount"]) && isset($_POST["subtract_from_total"])) {
        // This handles the new "Subtract from Total" functionality
        $subtract_amount = $_POST["subtract_amount"];
        $description = "Adjustment (subtracted)"; // You can customize this description
        
        // Insert a new transaction with a negative value to represent subtraction
        $stmt = $conn->prepare("INSERT INTO transactions (person_id, description, price) VALUES (?, ?, ?)");
        $negative_amount = -$subtract_amount; // Store as negative to reflect subtraction
        $stmt->bind_param("isd", $person_id, $description, $negative_amount);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT name FROM persons WHERE id=?");
$stmt->bind_param("i", $person_id);
$stmt->execute();
$person = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM transactions WHERE person_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $person_id);
$stmt->execute();
$transactions = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($person['name']) ?>'s Transactions</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f6f8;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        form {
            margin-bottom: 20px;
            display: inline-block; /* For aligning buttons */
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"], button {
            padding: 8px;
            font-size: 15px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: #dc3545;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .minus-btn {
            background-color: #007bff;
        }
        .minus-btn:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f1f1f1;
        }
        .actions form {
            display: inline-block;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            color: #007bff;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h1><?= htmlspecialchars($person['name']) ?></h1>

    <div class="form-group">
        <form method="POST" style="display: inline-block;">
            <input type="text" name="description" placeholder="Description" required>
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <button type="submit">Add Transaction</button>
        </form>
        <form method="POST" style="display: inline-block;">
            <input type="number" step="0.01" name="subtract_amount" placeholder="Amount to subtract" required>
            <button type="submit" name="subtract_from_total" class="minus-btn">Subtract from Total</button>
        </form>
    </div>

    <table>
        <tr>
            <th>Description</th>
            <th>Price ($)</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php
        $total = 0;
        while ($row = $transactions->fetch_assoc()):
            $total += $row['price'];
        ?>
        <tr>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= number_format($row['price'], 2) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td class="actions">
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this transaction?')" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                    <button type="submit" class="delete-btn">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <th style="text-align:right;">Total:</th>
            <th><?= number_format($total, 2) ?></th>
            <th colspan="2"></th>
        </tr>
    </table>

    <a href="index.php" class="back-link">← Back to List</a>
</div>
</body>
</html>