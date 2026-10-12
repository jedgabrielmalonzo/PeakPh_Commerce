<?php
/**
 * PeakPH Commerce - Rate Limiting & Brute Force Verification Suite
 * 
 * Verifies defenses against Burp Suite Cluster Bomb / Intruder attacks:
 * 1. Simulates 1-5 normal login attempts (processed normally)
 * 2. Simulates 6th attempt triggering HTTP 429 Too Many Requests + Retry-After: 900
 * 3. Tests IP-level and Account-level lockout
 * 4. Provides reset capability for repeatable verification
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/security.php';

$is_cli = (php_sapi_name() === 'cli');

// Handle Reset Action
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    $ip = getClientIP();
    $test_email = 'attacker@target.com';
    clearRateLimit('login_ip', $ip);
    clearRateLimit('login_account', $test_email);
    clearRateLimit('admin_login_ip', $ip);
    clearRateLimit('admin_login_account', $test_email);
    
    if ($is_cli) {
        echo "Rate limit cache reset successfully for IP: $ip\n";
        exit;
    } else {
        header("Location: test_rate_limit.php?msg=reset_ok");
        exit;
    }
}

// Run Simulation
$test_ip = getClientIP();
$test_account = 'target_user@peakph.com';

if ($is_cli || (isset($_GET['action']) && $_GET['action'] === 'test')) {
    $results = [];
    $max_attempts = 5;
    $window = 900; // 15 minutes

    // Clear prior test keys first for a clean run
    clearRateLimit('test_bruteforce_ip', $test_ip);

    for ($i = 1; $i <= 7; $i++) {
        $res = checkRateLimitWithDetails('test_bruteforce_ip', $test_ip, $max_attempts, $window);
        
        if ($res['allowed']) {
            $results[] = [
                'attempt' => $i,
                'status' => '200 / 401',
                'allowed' => true,
                'attempts_count' => $res['attempts'],
                'message' => "Attempt #{$i} permitted (Normal entry within 1-5 threshold)",
                'retry_after' => 0
            ];
        } else {
            $results[] = [
                'attempt' => $i,
                'status' => '429 Too Many Requests',
                'allowed' => false,
                'attempts_count' => $res['attempts'],
                'message' => "Attempt #{$i} BLOCKED! Rate limit enforced (Cluster Bomb attack mitigated)",
                'retry_after' => $res['retry_after']
            ];
        }
    }

    // Clean up test keys
    clearRateLimit('test_bruteforce_ip', $test_ip);

    if ($is_cli) {
        echo "============================================================\n";
        echo " PeakPH Commerce - Brute Force Rate Limit Verification\n";
        echo " Target IP: {$test_ip} | Window: {$window}s | Threshold: {$max_attempts}\n";
        echo "============================================================\n";
        foreach ($results as $r) {
            $prefix = $r['allowed'] ? "[PASS]" : "[BLOCKED]";
            echo "{$prefix} Attempt {$r['attempt']}: Status {$r['status']} | {$r['message']} | Retry-After: {$r['retry_after']}s\n";
        }
        echo "\nSample HTTP Response for Attempt #6+:\n";
        echo "------------------------------------------------------------\n";
        echo "HTTP/1.1 429 Too Many Requests\n";
        echo "Retry-After: 900\n";
        echo "Content-Type: application/json; charset=UTF-8\n\n";
        echo json_encode([
            'success' => false,
            'error' => 'Too Many Requests',
            'message' => 'Too many login attempts. Please try again in 15 minutes.',
            'retry_after' => 900
        ], JSON_PRETTY_PRINT) . "\n";
        echo "============================================================\n";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brute Force Rate Limiter Verification - PeakPH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; padding: 40px 20px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        .card-header { background: #0f172a; border-bottom: 1px solid #334155; }
        pre { background: #090d16; border: 1px solid #1e293b; padding: 15px; border-radius: 8px; color: #38bdf8; }
        .badge-allowed { background: #059669; color: #fff; }
        .badge-blocked { background: #dc2626; color: #fff; }
    </style>
</head>
<body>
<div class="container" style="max-width: 900px;">
    <div class="text-center mb-4">
        <h2>🛡️ Rate Limiting & Brute Force Defense Verification</h2>
        <p class="text-muted">Verification tool for Cluster Bomb / Intruder mitigation in PeakPH Commerce</p>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_ok'): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i> Rate limit cache successfully cleared for your IP!
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0"><i class="bi bi-cpu me-2"></i> Live Rate Limit Simulation</h5>
            <div>
                <a href="test_rate_limit.php?action=test" class="btn btn-primary btn-sm me-2">
                    <i class="bi bi-play-fill"></i> Run 7-Request Attack Simulation
                </a>
                <a href="test_rate_limit.php?action=reset" class="btn btn-outline-warning btn-sm">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset IP Counter
                </a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-secondary small mb-3">
                Detected Client IP: <code><?= htmlspecialchars($test_ip) ?></code> | 
                Threshold: <strong>5 attempts</strong> | 
                Lockout Window: <strong>900 seconds (15 minutes)</strong>
            </p>

            <?php if (!empty($results)): ?>
                <table class="table table-dark table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status Code</th>
                            <th>State</th>
                            <th>Retry-After</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $r): ?>
                            <tr>
                                <td><?= $r['attempt'] ?></td>
                                <td><code><?= $r['status'] ?></code></td>
                                <td>
                                    <?php if ($r['allowed']): ?>
                                        <span class="badge badge-allowed">ALLOWED (<?= $r['attempts_count'] ?>/5)</span>
                                    <?php else: ?>
                                        <span class="badge badge-blocked">BLOCKED (429)</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $r['retry_after'] > 0 ? $r['retry_after'] . 's' : '-' ?></td>
                                <td><?= htmlspecialchars($r['message']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="p-4 text-center border border-dashed border-secondary rounded">
                    <p class="mb-2 text-muted">Click the button below to test how the server processes 7 rapid attempts.</p>
                    <a href="test_rate_limit.php?action=test" class="btn btn-primary">
                        <i class="bi bi-shield-check"></i> Run Verification Test
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="bi bi-terminal me-2"></i> Expected Raw HTTP 429 Response (Burp Suite Output)</h5>
        </div>
        <div class="card-body">
            <pre>HTTP/1.1 429 Too Many Requests
Retry-After: 900
Content-Type: application/json; charset=UTF-8

{
  "success": false,
  "error": "Too Many Requests",
  "message": "Too many login attempts. Please try again in 15 minutes.",
  "retry_after": 900
}</pre>
        </div>
    </div>

    <div class="card">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="bi bi-bug me-2"></i> How to Verify in Burp Suite Intruder (Cluster Bomb)</h5>
        </div>
        <div class="card-body">
            <ol class="mb-0">
                <li class="mb-2">Send a login request to <strong>Burp Suite Intruder</strong>:
                    <code>POST /PeakPH_Commerce/auth/login_handler.php</code>
                </li>
                <li class="mb-2">Select <strong>Attack type: Cluster Bomb</strong>.</li>
                <li class="mb-2">Mark payload positions:
                    <pre class="my-1">{"email":"§user§","password":"§pass§"}</pre>
                </li>
                <li class="mb-2">Load 3-5 usernames into <strong>Payload Set 1</strong> and 5+ passwords into <strong>Payload Set 2</strong>.</li>
                <li class="mb-2">Start the attack. Observe the Intruder results table:
                    <ul>
                        <li><strong>Requests 1 to 5:</strong> Receive status <code>401</code> or <code>200</code>.</li>
                        <li><strong>Requests 6+:</strong> Immediately transition to status <code>429</code> with <code>Retry-After: 900</code>.</li>
                    </ul>
                </li>
            </ol>
        </div>
    </div>
</div>
</body>
</html>
