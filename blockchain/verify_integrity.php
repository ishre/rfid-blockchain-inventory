<?php
function calculateBlockHash($block) {
  $blockDataWithoutSignature = $block['data'];
  unset($blockDataWithoutSignature['signature']);
  $blockString = json_encode($blockDataWithoutSignature);
  return hash('sha256', $blockString);
}

function verifyBlockchainIntegrity($blockchainData) {
  for ($i = 1; $i < count($blockchainData); $i++) {
    $previousBlock = $blockchainData[$i - 1];
    $currentBlock = $blockchainData[$i];

    // Recalculate the hash for the current block (including all data fields except signature)
    $calculatedHash = calculateBlockHash($currentBlock);

    // Compare the calculated hash with the stored hash
    if ($currentBlock['hash'] !== $calculatedHash) {
      return false; // Tampering detected
    }

    // Check if the previous_hash field is correct
    if ($currentBlock['previous_hash'] !== $previousBlock['hash']) {
      return false; // Previous hash tampered
    }

    // Verify the signature (optional if signature is included in the data)
    $signature = $currentBlock['data']['signature'];
    $blockDataWithoutSignature = $currentBlock['data'];
    unset($blockDataWithoutSignature['signature']);
    $blockString = json_encode($blockDataWithoutSignature);
    $isValidSignature = verifySignature($blockString, $signature);
    if (!$isValidSignature) {
      return false; // Invalid signature
    }
  }

  return true; // Blockchain is intact
}

function verifySignature($message, $signature) {
  // Replace this function with the actual code to verify the signature using a cryptographic library
  // Example: return true if the signature is valid, false otherwise
  return true;
}

// Load blockchain data from JSON file
$file_path = "blockchain.json";
$blockchain_data = [];

if (file_exists($file_path)) {
  $blockchain_data = json_decode(file_get_contents($file_path), true);
}

// Verify the integrity of the blockchain
$is_integrity_verified = verifyBlockchainIntegrity($blockchain_data);

// Return the result as JSON
header('Content-Type: application/json');
echo json_encode(['integrity_verified' => $is_integrity_verified]);
?>
