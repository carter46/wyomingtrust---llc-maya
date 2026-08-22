<?php

require_once __DIR__ . '/../helpers.php';

$method = get_request_method();

switch ($method) {
    case 'GET':
        handleListPricing();
        break;
    case 'POST':
        handleCreatePricing();
        break;
    case 'PUT':
    case 'PATCH':
        handleUpdatePricing();
        break;
    case 'DELETE':
        handleDeletePricing();
        break;
    default:
        send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

function handleListPricing() {
    require_admin_auth();
    $db = getDatabase();
    
    $stmt = $db->query(
        'SELECT id, plan_name, price, features, is_active, created_at, updated_at
         FROM pricing_plans
         ORDER BY price ASC'
    );
    $plans = $stmt->fetchAll();
    
    // Decode JSON features
    foreach ($plans as &$plan) {
        if (!empty($plan['features'])) {
            $plan['features'] = json_decode($plan['features'], true) ?? [];
        } else {
            $plan['features'] = [];
        }
    }
    
    send_json(['success' => true, 'plans' => $plans]);
}

function handleCreatePricing() {
    require_admin_auth();
    $payload = get_json_input();
    $planName = sanitize_text($payload['plan_name'] ?? '');
    $price = isset($payload['price']) ? (float) $payload['price'] : 0.0;
    $features = isset($payload['features']) ? $payload['features'] : [];
    $isActive = isset($payload['is_active']) ? (int) $payload['is_active'] : 1;
    
    if ($planName === '') {
        send_json(['success' => false, 'message' => 'Plan name is required'], 400);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare(
            'INSERT INTO pricing_plans (plan_name, price, features, is_active)
             VALUES (:plan_name, :price, :features, :is_active)'
        );
        $stmt->execute([
            ':plan_name' => $planName,
            ':price' => $price,
            ':features' => json_encode($features),
            ':is_active' => $isActive,
        ]);
        
        $planId = (int) $db->lastInsertId();
        
        send_json([
            'success' => true,
            'message' => 'Pricing plan created successfully',
            'plan' => [
                'id' => $planId,
                'plan_name' => $planName,
                'price' => $price,
            ],
        ]);
    } catch (Exception $e) {
        error_log('Create pricing plan failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to create pricing plan'], 500);
    }
}

function handleUpdatePricing() {
    require_admin_auth();
    $payload = get_json_input();
    $planId = isset($payload['id']) ? (int) $payload['id'] : 0;
    
    if ($planId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid plan ID'], 400);
    }
    
    $db = getDatabase();
    
    $updates = [];
    $params = [':id' => $planId];
    
    if (isset($payload['plan_name'])) {
        $updates[] = 'plan_name = :plan_name';
        $params[':plan_name'] = sanitize_text($payload['plan_name']);
    }
    
    if (isset($payload['price'])) {
        $updates[] = 'price = :price';
        $params[':price'] = (float) $payload['price'];
    }
    
    if (isset($payload['features'])) {
        $updates[] = 'features = :features';
        $params[':features'] = json_encode($payload['features']);
    }
    
    if (isset($payload['is_active'])) {
        $updates[] = 'is_active = :is_active';
        $params[':is_active'] = (int) $payload['is_active'];
    }
    
    if (empty($updates)) {
        send_json(['success' => false, 'message' => 'No valid fields to update'], 400);
    }
    
    try {
        $sql = 'UPDATE pricing_plans SET ' . implode(', ', $updates) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        send_json(['success' => true, 'message' => 'Pricing plan updated successfully']);
    } catch (Exception $e) {
        error_log('Update pricing plan failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to update pricing plan'], 500);
    }
}

function handleDeletePricing() {
    require_admin_auth();
    $planId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    
    if ($planId <= 0) {
        send_json(['success' => false, 'message' => 'Invalid plan ID'], 400);
    }
    
    $db = getDatabase();
    
    try {
        $stmt = $db->prepare('DELETE FROM pricing_plans WHERE id = :id');
        $stmt->execute([':id' => $planId]);
        
        if ($stmt->rowCount() === 0) {
            send_json(['success' => false, 'message' => 'Pricing plan not found'], 404);
        }
        
        send_json(['success' => true, 'message' => 'Pricing plan deleted successfully']);
    } catch (Exception $e) {
        error_log('Delete pricing plan failed: ' . $e->getMessage());
        send_json(['success' => false, 'message' => 'Failed to delete pricing plan'], 500);
    }
}
