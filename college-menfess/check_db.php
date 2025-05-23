<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db = new SQLite3('database.sqlite');

// Check tables
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table';");
echo "Tables in database:\n";
while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
    echo "- " . $table['name'] . "\n";
    
    // Show table structure
    $schema = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='" . $table['name'] . "';");
    $schemaRow = $schema->fetchArray(SQLITE3_ASSOC);
    echo "Schema:\n" . $schemaRow['sql'] . "\n\n";
}
?>
