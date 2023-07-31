<!DOCTYPE html>
<html>
<head>
  <title>Blockchain Viewer</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.0.0/crypto-js.min.js"></script>
  <style>
    table {
      border-collapse: collapse;
      width: 100%;
    }

    th, td {
      border: 1px solid black;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    tr.tampered {
      background-color: #ffdddd;
    }
  </style>
</head>
<body>
  <h1>Blockchain Viewer</h1>
  <table>
    <tr>
      <th>Index</th>
      <th>Timestamp</th>
      <th>Data</th>
      <th>Previous Hash</th>
      <th>Hash</th>
    </tr>
  </table>

  <script>
    // Function to calculate the SHA-256 hash of a block
    function calculateBlockHash(block) {
      const blockString = JSON.stringify(block);
      return CryptoJS.SHA256(blockString).toString();
    }

    // Function to display blockchain data in the table
    function displayBlockchainData(data) {
      const table = document.querySelector('table');
      let tableHTML = `
        <tr>
          <th>Index</th>
          <th>Timestamp</th>
          <th>Data</th>
          <th>Previous Hash</th>
          <th>Hash</th>
        </tr>
      `;

      let previousHash = 'VIT PROJECT'; // Initial hash for the first block
      data.forEach((block, index) => {
        const { timestamp, data: blockData, previous_hash, hash } = block;
        const dataHTML = Object.entries(blockData)
          .map(([key, value]) => `<strong>${key}:</strong> ${value}`)
          .join('<br>');

        tableHTML += `
          <tr class="${previousHash !== previous_hash ? 'tampered' : ''}">
            <td>${index}</td>
            <td>${timestamp}</td>
            <td>${dataHTML}</td>
            <td>${previous_hash}</td>
            <td>${hash}</td>
          </tr>
        `;

        previousHash = hash;
      });

      table.innerHTML = tableHTML;
    }

    // Function to fetch blockchain data from JSON file
    async function fetchBlockchainData() {
      try {
        const response = await fetch('blockchain.json'); // Replace with the correct path to your JSON file
        const data = await response.json();
        return data;
      } catch (error) {
        console.error('Error fetching blockchain data:', error);
        return [];
      }
    }

    // Fetch and display the blockchain data
    fetchBlockchainData().then(blockchainData => {
      displayBlockchainData(blockchainData);
    });
  </script>
</body>
</html>
