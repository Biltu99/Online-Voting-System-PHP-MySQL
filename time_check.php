<?php
require_once 'config/db.php';

echo "<pre style='background:#111;color:#0f0;padding:15px;font-family:monospace'>";
echo "PHP timezone:  " . date_default_timezone_get() . "\n";
echo "PHP now:       " . date('Y-m-d H:i:s') . "\n";

$mysqlNow = $pdo->query("SELECT NOW()")->fetchColumn();
echo "MySQL now:     " . $mysqlNow . "\n";

$phpTs   = time();
$mysqlTs = strtotime($mysqlNow);
echo "Difference:    " . abs($phpTs - $mysqlTs) . " seconds\n";
echo "</pre>";