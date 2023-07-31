<?php
// Step 2: Connect to the MySQL Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfidattendance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 3: Retrieve New Data from the MySQL Table
$sql = "SELECT * FROM users_logs WHERE blockchain_add IS NULL"; // Assuming blockchain_add is NULL for new data
$result = $conn->query($sql);

// Step 4: Load Existing Blockchain Data
$file_path = "blockchain.json";
$blockchain_data = [];

if (file_exists($file_path)) {
    $blockchain_data = json_decode(file_get_contents($file_path), true);
}

// Step 5: Calculate and Add New Blocks to the Blockchain
if ($result->num_rows > 0) {
    // Get the last block's hash
    $previous_hash = count($blockchain_data) > 0 ? $blockchain_data[count($blockchain_data) - 1]['hash'] : 'genesis_block';

    // Loop through the new data and add it to the blockchain
    while ($row = $result->fetch_assoc()) {
        $data = [
            "id" => $row["id"],
            "username" => $row["username"],
            "serialnumber" => $row["serialnumber"],
            "card_uid" => $row["card_uid"],
            "device_uid" => $row["device_uid"],
            "device_dep" => $row["device_dep"],
            "checkindate" => $row["checkindate"],
            "timein" => $row["timein"],
            "timeout" => $row["timeout"],
            "card_out" => $row["card_out"]
        ];

        // Create a new block
        $block = [
            "index" => count($blockchain_data),
            "timestamp" => time(),
            "data" => $data,
            "previous_hash" => $previous_hash,
            "hash" => '', // We'll calculate this later
        ];

        // Calculate the hash for the new block
        $block['hash'] = hash('sha256', json_encode($block));

        // Add the block to the blockchain
        $blockchain_data[] = $block;

        // Set the current block's hash as the previous_hash for the next block
        $previous_hash = $block['hash'];

        // Update the blockchain_add column to 1 (meaning data added to the blockchain)
        $update_sql = "UPDATE users_logs SET blockchain_add = 1 WHERE id = " . $row["id"];
        $conn->query($update_sql);
    }

    // Save the updated blockchain data to the file
    file_put_contents($file_path, json_encode($blockchain_data, JSON_PRETTY_PRINT));
}

$conn->close();
?>
