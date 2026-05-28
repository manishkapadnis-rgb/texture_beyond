<?php
require_once '../includes/functions.php';

$items = cart_items();
$subtotal = cart_subtotal();

$payload = [
  'ok' => true,
  'count' => cart_count(),
  'subtotal' => (float) $subtotal,
  'subtotal_formatted' => money($subtotal),
  'items' => array_map(function ($item) {
    return [
      'id' => (int) $item['id'],
      'slug' => $item['slug'] ?? '',
      'name' => $item['name'] ?? '',
      'image' => product_image($item['image'] ?? ''),
      'qty' => (int) $item['qty'],
      'price' => (float) ($item['effective_price'] ?? 0),
      'price_formatted' => money($item['effective_price'] ?? 0),
      'line_total' => (float) ($item['line_total'] ?? 0),
      'line_total_formatted' => money($item['line_total'] ?? 0),
      'stock' => (int) ($item['stock'] ?? 0),
    ];
  }, $items),
];

header('Content-Type: application/json');
echo json_encode($payload, JSON_UNESCAPED_SLASHES);
