<?php
if (!defined('DB_HOST')) require_once __DIR__ . '/config.php';
$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error){
    if (ENV === 'development') die('DB Connection Failed: ' . $conn->connect_error);
    error_log('DB Error: ' . $conn->connect_error);
    die('Service temporarily unavailable.');
}
$conn->set_charset('utf8mb4');

function db(){ global $conn; return $conn; }
function db_query($sql, array $params = []){
    global $conn;
    if (!$params) return $conn->query($sql);
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;
    $types = '';
    foreach ($params as $p) $types .= is_int($p)?'i':(is_float($p)?'d':'s');
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}
function db_one($s,$p=[]){ $r=db_query($s,$p); return $r?$r->fetch_assoc():null; }
function db_all($s,$p=[]){ $r=db_query($s,$p); return $r?$r->fetch_all(MYSQLI_ASSOC):[]; }
function db_exec($s,$p=[]){ global $conn; $st=$conn->prepare($s); if(!$st) return false; if($p){$t='';foreach($p as $v)$t.=is_int($v)?'i':(is_float($v)?'d':'s'); $st->bind_param($t,...$p);} $st->execute(); return $st; }
