<?php
$host = '127.0.0.1';
$dbname = 'altimacrm_email';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== Inbound Emails ===\n";
    $emails = $pdo->query('SELECT * FROM inbound_emails ORDER BY created_at DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($emails) . "\n\n";
    
    foreach($emails as $email) {
        echo "ID: " . $email['id'] . "\n";
        echo "Subject: " . $email['subject'] . "\n";
        echo "From: " . $email['from_email'] . "\n";
        echo "Status: " . $email['status'] . "\n";
        echo "Received: " . $email['received_at'] . "\n";
        echo "---\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
