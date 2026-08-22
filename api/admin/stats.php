<?php

require_once __DIR__ . '/../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(['success' => false, 'message' => 'Method not allowed'], 405);
}

require_admin_auth();

try {
    $db = getDatabase();
    
    // Get total users count
    $usersCount = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    
    // Get total trusts count
    $trustsCount = $db->query('SELECT COUNT(*) FROM user_trusts')->fetchColumn();
    
    // Get total revenue (sum of all successful transactions)
    $revenueQuery = $db->query('SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE status = "completed"');
    $totalRevenue = (float) $revenueQuery->fetchColumn();
    
    // Get active trust services count
    $activeServices = $db->query('SELECT COUNT(*) FROM trust_services WHERE is_active = 1')->fetchColumn();
    
    // Get recent registrations (last 5)
    $recentUsers = $db->query(
        'SELECT id, full_name, email, created_at 
         FROM users 
         ORDER BY created_at DESC 
         LIMIT 5'
    )->fetchAll();
    
    // Format recent users
    foreach ($recentUsers as &$user) {
        $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created_at']));
    }
    
    // Get recent trusts created (last 5)
    $recentTrusts = $db->query(
        'SELECT ut.id, ut.created_at, u.full_name as user_name, ts.service_name,
                ut.payment_method_id, pm.method_name AS payment_method_name, pm.method_type AS payment_method_type
         FROM user_trusts ut
         JOIN users u ON ut.user_id = u.id
         JOIN trust_services ts ON ut.trust_service_id = ts.id
         LEFT JOIN payment_methods pm ON pm.id = ut.payment_method_id
         ORDER BY ut.created_at DESC
         LIMIT 5'
    )->fetchAll();
    
    // Format recent trusts
    foreach ($recentTrusts as &$trust) {
        $trust['created_at'] = date('Y-m-d H:i:s', strtotime($trust['created_at']));
    }
    
    // Get users verified count
    $verifiedUsers = $db->query('SELECT COUNT(*) FROM users WHERE email_verified = 1')->fetchColumn();
    
    // Get pending verifications count
    $pendingVerifications = $db->query('SELECT COUNT(*) FROM users WHERE email_verified = 0')->fetchColumn();
    
    // Get total payment methods count
    $paymentMethodsCount = $db->query('SELECT COUNT(*) FROM payment_methods WHERE is_active = 1')->fetchColumn();
    
    send_json([
        'success' => true,
        'stats' => [
            'total_users' => (int) $usersCount,
            'verified_users' => (int) $verifiedUsers,
            'pending_verifications' => (int) $pendingVerifications,
            'total_trusts' => (int) $trustsCount,
            'total_revenue' => $totalRevenue,
            'active_services' => (int) $activeServices,
            'active_payment_methods' => (int) $paymentMethodsCount,
            'recent_users' => $recentUsers,
            'recent_trusts' => $recentTrusts,
        ]
    ]);
    
} catch (Exception $e) {
    error_log('Stats API error: ' . $e->getMessage());
    send_json(['success' => false, 'message' => 'Failed to fetch statistics'], 500);
}
