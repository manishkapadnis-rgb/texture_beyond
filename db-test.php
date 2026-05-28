<?php
/**
 * Quick connection sanity check.
 * Visit /texture_beyond/db-test.php after importing the SQL.
 * DELETE THIS FILE in production.
 */
require_once 'includes/config.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Texture & Beyond — DB Connection Test ===\n\n";
echo "DB Host    : " . DB_HOST . "\n";
echo "DB Name    : " . DB_NAME . "\n";
echo "DB User    : " . DB_USER . "\n";
echo "MySQL Ver  : " . $conn->server_info . "\n";
echo "Charset    : " . $conn->character_set_name() . "\n\n";

$tables = ['users','categories','products','coupons','orders','order_items','contacts','newsletter','settings'];
foreach ($tables as $t){
    $r = $conn->query("SELECT COUNT(*) c FROM `$t`");
    $row = $r ? $r->fetch_assoc() : ['c'=>'MISSING'];
    printf("  %-15s %s row(s)\n", $t, $row['c']);
}

echo "\n✔ Connection OK. Delete this file before going live.\n";
