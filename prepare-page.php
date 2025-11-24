<?php
/*
Template Name: Spacexinfo Prepare Page
Template Post Type: page
*/

session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: /log-in/"); 
    exit;
}

// ===========================================
// 💥 DATABASE CONNECTION
// ===========================================
$servername = "localhost";
$dbusername = "spacenet_spacexinfo";
$dbpassword = "@#passNet";
$dbname = "spacenet_spacexinfo_userdb";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$success_msg = "";
$error_msg = "";

// ===========================================
// 💥 HANDLE DEPOSITS & WITHDRAWALS LOGIC
// ===========================================

// A. Handle Deposit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_deposit'])) {
    $method = $_POST['payment_method'];
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $details = isset($_POST['details']) ? $_POST['details'] : ''; 
    
    $proof_image = "";
    if (isset($_FILES['receipt_file']) && $_FILES['receipt_file']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . $user_id . "_" . basename($_FILES["receipt_file"]["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES["receipt_file"]["tmp_name"], $target_file)) {
            $proof_image = $target_file;
        }
    }

    $stmt = $conn->prepare("INSERT INTO deposit_requests (user_id, method, amount, details, proof_image, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    if($stmt) {
        $stmt->bind_param("isdss", $user_id, $method, $amount, $details, $proof_image);
        if ($stmt->execute()) {
            $success_msg = "DEPOSIT_OK";
        } else {
            $error_msg = "Error submitting deposit.";
        }
        $stmt->close();
    } else {
        $error_msg = "Database error: Table setup required.";
    }
}

// B. Handle Withdrawal
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_withdrawal'])) {
    $amount = floatval($_POST['amount']);
    $method = $_POST['payment_type']; 
    $account_info = $_POST['account_info'];

    $check = $conn->query("SELECT balance FROM users WHERE id = $user_id")->fetch_assoc();
    if ($check && $check['balance'] >= $amount) {
        $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $user_id");
        $stmt = $conn->prepare("INSERT INTO withdrawal_requests (user_id, method, amount, account_details, status) VALUES (?, ?, ?, ?, 'pending')");
        if($stmt) {
            $stmt->bind_param("isds", $user_id, $method, $amount, $account_info);
            $stmt->execute();
        }
        $conn->query("INSERT INTO transactions (user_id, type, amount, status) VALUES ($user_id, 'withdrawal', $amount, 'pending')");
        $success_msg = "WITHDRAWAL_OK";
    } else {
        $error_msg = "Insufficient funds for this withdrawal.";
    }
}

// ===========================================
// 💥 FETCH USER DATA
// ===========================================
$bal_res = $conn->query("SELECT balance FROM users WHERE id = $user_id");
$current_balance = ($bal_res && $bal_res->num_rows > 0) ? $bal_res->fetch_assoc()['balance'] : 0.00;

$display_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
$user_initial = strtoupper(substr($display_username, 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Spacexinfo - Dashboard</title>
    <style>
        /* --- UI DESIGN CSS --- */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0a0e27; color: #ffffff; overflow-x: hidden; width: 100%; position: relative; }

        /* Sidebar */
        .sidebar { position: fixed; left: 0; top: 0; width: 280px; height: 100vh; background: linear-gradient(180deg, #0d1128, #0a0e27); border-right: 1px solid rgba(255, 255, 255, 0.1); padding: 2rem 0; z-index: 1000; transition: transform 0.3s ease-in-out; }
        .logo { font-size: 1.8rem; font-weight: bold; background: linear-gradient(135deg, #00ff87, #00d4ff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; padding: 0 2rem; margin-bottom: 3rem; }
        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 0.5rem; }
        .nav-link { display: flex; align-items: center; gap: 1rem; padding: 1rem 2rem; color: #b8bcc8; text-decoration: none; transition: all 0.3s; position: relative; cursor: pointer; }
        .nav-link:hover, .nav-link.active { color: #fff; background: rgba(0, 255, 135, 0.1); }
        .nav-link.active::before { content: ''; position: absolute; left: 0; top: 0; height: 100%; width: 3px; background: linear-gradient(180deg, #00ff87, #00d4ff); }
        .nav-icon { font-size: 1.3rem; }
        .sidebar-footer { position: absolute; bottom: 2rem; left: 0; right: 0; padding: 0 2rem; }
        .user-profile { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: rgba(255, 255, 255, 0.05); border-radius: 10px; cursor: pointer; transition: all 0.3s; }
        .user-profile:hover { background: rgba(255, 255, 255, 0.08); }
        .user-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #00ff87, #00d4ff); display: flex; align-items: center; justify-content: center; font-weight: bold; color: #0a0e27; flex-shrink: 0; }
        .user-info { flex: 1; overflow: hidden; }
        .user-name { font-weight: 600; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .user-email { font-size: 0.8rem; color: #b8bcc8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* Main Content */
        .main-content { margin-left: 280px; min-height: 100vh; transition: margin-left 0.3s ease; width: calc(100% - 280px); }
        .topbar { background: rgba(10, 14, 39, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); padding: 1.5rem 3rem; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100; width: 100%; }
        .search-bar { flex: 1; max-width: 400px; position: relative; }
        .search-bar input { width: 100%; padding: 0.8rem 1rem 0.8rem 3rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; color: #fff; font-size: 0.95rem; }
        .search-bar input:focus { outline: none; border-color: #00ff87; }
        .search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #b8bcc8; }
        .topbar-actions { display: flex; gap: 1rem; align-items: center; }
        .icon-btn { width: 45px; height: 45px; border-radius: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.3s; position: relative; color: #fff; }
        .icon-btn:hover { background: rgba(255, 255, 255, 0.1); border-color: #00ff87; }
        .notification-badge { position: absolute; top: -5px; right: -5px; width: 18px; height: 18px; background: #ff4757; border-radius: 50%; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; }

        .content-wrapper { padding: 3rem; max-width: 100%; }
        
        /* VIEW SWITCHING CSS */
        .view-section { display: none; animation: fadeIn 0.4s; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 2rem; margin-bottom: 0.5rem; }
        .page-subtitle { color: #b8bcc8; }

        /* Cards */
        /* UPDATE: Reduced minmax to 240px for better mobile fit */
        .balance-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .balance-card { background: linear-gradient(135deg, rgba(0, 255, 135, 0.1), rgba(0, 212, 255, 0.05)); border: 1px solid rgba(0, 255, 135, 0.2); border-radius: 15px; padding: 2rem; position: relative; overflow: hidden; }
        .balance-card::before { content: ''; position: absolute; top: -50%; right: -50%; width: 200px; height: 200px; background: radial-gradient(circle, rgba(0, 255, 135, 0.2), transparent); border-radius: 50%; }
        .balance-card-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem; }
        .balance-label { color: #b8bcc8; font-size: 0.9rem; }
        .balance-icon { font-size: 1.8rem; }
        .balance-amount { font-size: 2.5rem; font-weight: bold; margin-bottom: 0.5rem; word-break: break-all; }
        .balance-change { font-size: 0.9rem; display: flex; align-items: center; gap: 0.3rem; }
        .card-actions { display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }

        .btn-small { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; font-weight: 600; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; }
        .btn-primary { background: linear-gradient(135deg, #00ff87, #00d4ff); color: #0a0e27; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 255, 135, 0.3); }
        .btn-secondary { background: rgba(255, 255, 255, 0.05); color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); }
        .btn-secondary:hover { background: rgba(255, 255, 255, 0.1); }

        /* Dashboard Grid */
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .chart-card { background: linear-gradient(135deg, rgba(255, 255, 255, 0.05), rgba(255, 255, 255, 0.02)); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; padding: 2rem; overflow: hidden; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
        .card-title { font-size: 1.3rem; font-weight: 600; }

        .time-filter { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .time-btn { padding: 0.5rem 1rem; background: transparent; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #b8bcc8; cursor: pointer; transition: all 0.3s; font-size: 0.85rem; }
        .time-btn:hover, .time-btn.active { background: rgba(0, 255, 135, 0.1); border-color: #00ff87; color: #00ff87; }

        /* Watchlist */
        .transactions-card { grid-column: 1 / -1; }
        .watchlist { display: flex; flex-direction: column; gap: 1rem; }
        .watchlist-item { display: flex; justify-content: space-between; align-items: center; padding: 1rem; background: rgba(255, 255, 255, 0.03); border-radius: 10px; cursor: pointer; transition: all 0.3s; }
        .watchlist-item:hover { background: rgba(255, 255, 255, 0.05); }
        .watchlist-info h4 { font-size: 0.95rem; margin-bottom: 0.2rem; }
        .watchlist-info p { font-size: 0.8rem; color: #b8bcc8; }
        .watchlist-price { text-align: right; }
        .watchlist-amount { font-size: 1rem; font-weight: 600; margin-bottom: 0.2rem; }
        .watchlist-change { font-size: 0.85rem; }
        .positive { color: #00ff87; }

        /* Mobile Toggle */
        .menu-toggle { display: none; position: fixed; bottom: 2rem; right: 2rem; width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #00ff87, #00d4ff); border: none; color: #0a0e27; font-size: 1.5rem; cursor: pointer; box-shadow: 0 10px 30px rgba(0, 255, 135, 0.3); z-index: 1001; }
        
        /* Tables */
        .table-container { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .custom-table { width: 100%; border-collapse: collapse; background: rgba(255, 255, 255, 0.05); border-radius: 10px; overflow: hidden; margin-top: 1rem; min-width: 600px; /* Forces scroll on small screens */ }
        .custom-table th, .custom-table td { padding: 1rem; text-align: left; border-bottom: 1px solid rgba(255, 255, 255, 0.1); white-space: nowrap; }
        .custom-table th { background: rgba(0,0,0,0.3); color: #00ff87; }
        .status-completed, .status-approved { color: #00ff87; }
        .status-pending { color: #ffa502; }
        .status-rejected, .status-failed { color: #ff4757; }

        /* MODALS */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.85); display: none; justify-content: center; align-items: center; z-index: 2000; backdrop-filter: blur(5px); opacity: 0; transition: opacity 0.3s ease-in-out; }
        .modal-overlay.active { display: flex; opacity: 1; }
        .modal-content { background: #0a0e27; border: 2px solid rgba(0, 255, 135, 0.3); border-radius: 15px; width: 90%; max-width: 550px; padding: 2rem; position: relative; box-shadow: 0 10px 40px rgba(0, 255, 135, 0.3); transform: scale(0.9); transition: transform 0.3s ease-in-out; max-height: 90vh; overflow-y: auto; }
        .modal-overlay.active .modal-content { transform: scale(1); }
        .modal-close { position: absolute; top: 1rem; right: 1rem; background: none; border: none; color: #fff; font-size: 1.5rem; cursor: pointer; opacity: 0.7; transition: opacity 0.3s; }
        .modal-close:hover { opacity: 1; color: #00ff87; }
        .modal-title { font-size: 1.8rem; font-weight: 700; margin-bottom: 1.5rem; color: #00ff87; }

        .payment-options { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .payment-option-card { background: rgba(255, 255, 255, 0.05); border: 2px solid transparent; border-radius: 10px; padding: 1rem 0.5rem; text-align: center; cursor: pointer; transition: all 0.3s; }
        .payment-option-card:hover { background: rgba(255, 255, 255, 0.1); }
        .payment-option-card.selected { border-color: #00ff87; background: rgba(0, 255, 135, 0.15); box-shadow: 0 0 10px rgba(0, 255, 135, 0.5); }
        .payment-icon { font-size: 2rem; margin-bottom: 0.5rem; }
        .payment-label { font-size: 0.85rem; font-weight: 600; }

        .method-section { display: none; padding: 1.5rem; border: 1px solid rgba(0, 255, 135, 0.2); border-radius: 10px; margin-bottom: 1.5rem; }
        .method-section.active { display: block; }
        
        input, select { width: 100%; padding: 0.8rem; margin-bottom: 1rem; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; color: #fff; font-size: 0.9rem; }
        input:focus { border-color: #00ff87; outline: none; }
        .account-info { background: rgba(0, 255, 135, 0.1); color: #00ff87; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; border-left: 3px solid #00ff87; }

        /* --- RESPONSIVE MEDIA QUERIES --- */
        @media (max-width: 1200px) { 
            .dashboard-grid { grid-template-columns: 1fr; } 
        }

        @media (max-width: 968px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .main-content { margin-left: 0; width: 100%; }
            .menu-toggle { display: flex; align-items: center; justify-content: center; }
            .topbar { padding: 1rem 1.5rem; }
            .content-wrapper { padding: 1.5rem 1rem; }
            .search-bar { display: none; }
            
            /* Better font sizes for mobile */
            .page-title { font-size: 1.5rem; }
            .balance-amount { font-size: 2rem; }
            
            /* Stack Payment options on very small screens */
            .payment-options { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="logo">Spacexinfo</div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a onclick="switchView('dashboard', this)" class="nav-link active">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a onclick="switchView('portfolio', this)" class="nav-link">
                    <span class="nav-icon">💼</span>
                    <span>Portfolio</span>
                </a>
            </li>
            <li class="nav-item">
                <a onclick="switchView('markets', this)" class="nav-link">
                    <span class="nav-icon">📈</span>
                    <span>Markets</span>
                </a>
            </li>
            <li class="nav-item">
                <a onclick="switchView('transactions', this)" class="nav-link">
                    <span class="nav-icon">🔄</span>
                    <span>Transactions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/log-in/" class="nav-link" style="color:#ff4757;">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar"><?php echo $user_initial; ?></div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($display_username); ?></div>
                    <div class="user-email">Active</div>
                </div>
            </div>
        </div>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="topbar">
            <div class="search-bar">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search stocks, crypto, or news...">
            </div>
            <div class="topbar-actions">
                <button class="icon-btn"><span>🔔</span><span class="notification-badge">3</span></button>
                <button class="icon-btn"><span>⚙️</span></button>
            </div>
        </div>

        <div class="content-wrapper">
            <?php if($success_msg): ?>
                <div style="padding:15px; background:rgba(0,255,135,0.2); border:1px solid #00ff87; color:#00ff87; border-radius:10px; margin-bottom:20px;">
                    <?php echo $success_msg == "DEPOSIT_OK" ? "Deposit Request Submitted Successfully!" : "Withdrawal Request Submitted Successfully!"; ?>
                </div>
            <?php endif; ?>
            <?php if($error_msg): ?>
                <div style="padding:15px; background:rgba(255,71,87,0.2); border:1px solid #ff4757; color:#ff4757; border-radius:10px; margin-bottom:20px;">
                    <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div id="view-dashboard" class="view-section active">
                <div class="page-header">
                    <h1 class="page-title">Welcome back, <?php echo htmlspecialchars($display_username); ?>! 👋</h1>
                    <p class="page-subtitle">Here's what's happening with your trading today</p>
                </div>

                <div class="balance-cards">
                    <div class="balance-card">
                        <div class="balance-card-header">
                            <div><div class="balance-label">Total Balance</div></div>
                            <div class="balance-icon">💰</div>
                        </div>
                        <div class="balance-amount">$<?php echo number_format($current_balance, 2); ?></div>
                        <div class="balance-change" style="color: #b8bcc8;"><span>Available</span></div>
                        <div class="card-actions">
                            <button class="btn-small btn-primary" onclick="openModal('depositModal')">Deposit Funds</button>
                            <button class="btn-small btn-secondary" onclick="openModal('withdrawalModal')">Withdraw</button>
                        </div>
                    </div>

                    <div class="balance-card">
                        <div class="balance-card-header">
                            <div><div class="balance-label">Today's P&L</div></div>
                            <div class="balance-icon">📈</div>
                        </div>
                        <div class="balance-amount" style="color: #b8bcc8;">$0.00</div>
                        <div class="balance-change" style="color: #b8bcc8;"><span>Start trading to see stats</span></div>
                    </div>
                </div>

                <div class="dashboard-grid">
                    <div class="chart-card">
                        <div class="card-header">
                            <h3 class="card-title">Portfolio Performance</h3>
                            <div class="time-filter">
                                <button class="time-btn active">1W</button>
                                <button class="time-btn">1M</button>
                            </div>
                        </div>
                        <div style="text-align: center; padding: 3rem 2rem;">
                            <div style="font-size: 4rem; margin-bottom: 1.5rem; opacity: 0.3;">📊</div>
                            <h3 style="font-size: 1.3rem; margin-bottom: 0.8rem; color: #fff;">Market Overview</h3>
                            <p style="color: #b8bcc8; margin-bottom: 2rem;">Chart data will appear once trading activity begins.</p>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="card-header">
                            <h3 class="card-title">Watchlist</h3>
                        </div>
                        <div class="watchlist">
                            <div class="watchlist-item">
                                <div class="watchlist-info"><h4>BTC</h4><p>Bitcoin</p></div>
                                <div class="watchlist-price">
                                    <div class="watchlist-amount">$95,120</div>
                                    <div class="watchlist-change positive">+2.4%</div>
                                </div>
                            </div>
                            <div class="watchlist-item">
                                <div class="watchlist-info"><h4>ETH</h4><p>Ethereum</p></div>
                                <div class="watchlist-price">
                                    <div class="watchlist-amount">$3,450</div>
                                    <div class="watchlist-change positive">+1.2%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card transactions-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Transactions</h3>
                        </div>
                        <div class="table-container">
                            <table class="custom-table">
                                <thead><tr><th>Type</th><th>Amount</th><th>Status</th></tr></thead>
                                <tbody>
                                    <?php 
                                    if($conn->query("SHOW TABLES LIKE 'transactions'")->num_rows > 0) {
                                        $res = $conn->query("SELECT * FROM transactions WHERE user_id=$user_id ORDER BY date DESC LIMIT 3");
                                        if($res && $res->num_rows > 0): while($row = $res->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo ucfirst($row['type']); ?></td>
                                                <td style="color:<?php echo $row['type']=='deposit'?'#00ff87':'#ff4757'; ?>">
                                                    <?php echo $row['type']=='deposit'?'+':'-'; ?>$<?php echo number_format($row['amount'], 2); ?>
                                                </td>
                                                <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                                            </tr>
                                        <?php endwhile; else: ?>
                                            <tr><td colspan="3" style="text-align:center;">No recent activity</td></tr>
                                        <?php endif; 
                                    } else { ?>
                                         <tr><td colspan="3" style="text-align:center;">Transaction history unavailable</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="view-transactions" class="view-section">
                <div class="page-header">
                    <h1 class="page-title">Transaction History</h1>
                </div>
                <div class="chart-card" style="width:100%;">
                    <div class="table-container">
                        <table class="custom-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if($conn->query("SHOW TABLES LIKE 'transactions'")->num_rows > 0) {
                                    $full_hist = $conn->query("SELECT * FROM transactions WHERE user_id=$user_id ORDER BY date DESC LIMIT 50");
                                    if($full_hist && $full_hist->num_rows > 0): while($row = $full_hist->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                            <td><?php echo ucfirst($row['type']); ?></td>
                                            <td style="color:<?php echo $row['type']=='deposit'?'#00ff87':'#ff4757'; ?>; font-weight:bold;">
                                                <?php echo $row['type']=='deposit'?'+':'-'; ?>$<?php echo number_format($row['amount'], 2); ?>
                                            </td>
                                            <td class="status-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></td>
                                        </tr>
                                    <?php endwhile; else: ?>
                                        <tr><td colspan="4" style="text-align:center; padding:2rem;">No transactions found.</td></tr>
                                    <?php endif; 
                                } else { ?>
                                        <tr><td colspan="4" style="text-align:center; padding:2rem;">History unavailable.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="view-portfolio" class="view-section">
                <div class="page-header"><h1 class="page-title">My Portfolio</h1></div>
                <div class="chart-card">
                    <div style="text-align:center; padding:3rem;">
                        <h2>Coming Soon</h2>
                        <p style="color:#b8bcc8">Detailed asset tracking is under development.</p>
                    </div>
                </div>
            </div>

            <div id="view-markets" class="view-section">
                <div class="page-header"><h1 class="page-title">Live Markets</h1></div>
                <div class="chart-card">
                    <div style="text-align:center; padding:3rem;">
                        <h2>Market Data Unavailable</h2>
                        <p style="color:#b8bcc8">Connecting to live exchange...</p>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('active')">☰</button>

    <div class="modal-overlay" id="depositModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('depositModal')">×</button>
            <h2 class="modal-title">Deposit Funds</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_deposit" value="1">
                <input type="hidden" name="payment_method" id="depMethod" value="bank">

                <div class="payment-options">
                    <div class="payment-option-card selected" onclick="selDep(this, 'bank')">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-label">Bank</div>
                    </div>
                    <div class="payment-option-card" onclick="selDep(this, 'giftcard')">
                        <div class="payment-icon">🎁</div>
                        <div class="payment-label">Gift Card</div>
                    </div>
                    <div class="payment-option-card" onclick="selDep(this, 'crypto')">
                        <div class="payment-icon">₿</div>
                        <div class="payment-label">Crypto</div>
                    </div>
                </div>

                <div id="sec-bank" class="method-section active">
                    <div class="account-info">
                        <strong>Bank:</strong> <i>Unavailable</i><br>
                        <strong>Acc:</strong> <i>Unavailable</i><br>
                        <strong>Ref:</strong> <?php echo htmlspecialchars($display_username); ?>
                    </div>
                    <input type="number" name="amount" placeholder="Amount ($)" step="0.01">
                    <label style="display:block;margin-bottom:5px;color:#ccc">Upload Receipt:</label>
                    <input type="file" name="receipt_file" accept="image/*">
                </div>

                <div id="sec-giftcard" class="method-section">
                    <h4>Gift Card Deposit</h4>
                    <p><i>Unavailable</i></p>
                    <input type="text" name="details" placeholder="Card Code / Value">
                    <label style="display:block;margin-bottom:5px;color:#ccc">Upload Card Image:</label>
                    <input type="file" name="receipt_file" accept="image/*">
                </div>

                <div id="sec-crypto" class="method-section">
                    <div class="account-info">
                        <strong>USDT (TRC20):</strong><br>
                        <code style="word-break:break-all">TFXhJ2u8EwLp9R4kG7zQcYmYxW6hD3sFkZ</code>
                    </div>
                    <input type="number" name="amount" placeholder="Amount Sent ($)" step="0.01">
                    <label style="display:block;margin-bottom:5px;color:#ccc">Upload Screenshot:</label>
                    <input type="file" name="receipt_file" accept="image/*">
                </div>

                <button type="submit" class="btn-small btn-primary" style="width:100%; padding:15px; margin-top:10px;">Confirm Deposit</button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="withdrawalModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal('withdrawalModal')">×</button>
            <h2 class="modal-title" style="color:#ff4757;">Withdraw Funds</h2>
            
            <form method="POST">
                <input type="hidden" name="submit_withdrawal" value="1">
                <label style="color:#b8bcc8;display:block;margin-bottom:5px;">Withdrawal Amount ($)</label>
                <input type="number" name="amount" placeholder="0.00" step="0.01" required>
                <label style="color:#b8bcc8;display:block;margin-bottom:5px;">Payment Method</label>
                <select name="payment_type">
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Crypto (USDT)">Crypto (USDT)</option>
                    <option value="Crypto (BTC)">Crypto (BTC)</option>
                    <option value="PayPal">PayPal</option>
                </select>
                <label style="color:#b8bcc8;display:block;margin-bottom:5px;">Account Details / Address</label>
                <input type="text" name="account_info" placeholder="Enter bank info or wallet address" required>
                <button type="submit" class="btn-small btn-primary" style="width:100%; padding:15px; margin-top:10px;">Submit Request</button>
            </form>
        </div>
    </div>

    <script>
        // ===============================
        // 🚀 NAVIGATION & UI LOGIC
        // ===============================
        
        // Switch between Dashboard, Transactions, etc.
        function switchView(viewName, btn) {
            // 1. Hide all content sections
            document.querySelectorAll('.view-section').forEach(el => {
                el.classList.remove('active');
            });

            // 2. Show the selected section
            const target = document.getElementById('view-' + viewName);
            if(target) target.classList.add('active');

            // 3. Update Sidebar Active State
            if(btn) {
                document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
                btn.classList.add('active');
            }

            // 4. Mobile: Close sidebar if open
            if(window.innerWidth <= 968) {
                document.getElementById('sidebar').classList.remove('active');
            }
        }

        // Modal Logic
        function openModal(id) { document.getElementById(id).classList.add('active'); }
        function closeModal(id) { document.getElementById(id).classList.remove('active'); }

        // Deposit Tabs
        function selDep(btn, method) {
            document.getElementById('depMethod').value = method;
            document.querySelectorAll('.payment-option-card').forEach(el => el.classList.remove('selected'));
            btn.classList.add('selected');
            document.querySelectorAll('.method-section').forEach(el => el.classList.remove('active'));
            document.getElementById('sec-' + method).classList.add('active');
        }

        // Close on outside click
        window.onclick = function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
