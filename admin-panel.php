<?php
/*
Template Name: Spacexinfo Admin Panel
Template Post Type: page
*/

session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    die("<div style='color:red; text-align:center; padding:50px;'>ACCESS DENIED: Admins only. <a href='/log-in/'>Login</a></div>");
}

$servername = "localhost";
$dbusername = "spacenet_spacexinfo"; 
$dbpassword = "@#passNet"; 
$dbname = "spacenet_spacexinfo_userdb";

$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$message = "";

// 2. HANDLE ACTIONS
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // A. MANAGE BALANCE (Add/Subtract manually)
    if (isset($_POST['manage_balance'])) {
        $u_id = intval($_POST['user_id']);
        $amt = floatval($_POST['amount']);
        $type = $_POST['type']; // 'credit' or 'debit'

        if ($type === 'debit') {
            $conn->query("UPDATE users SET balance = balance - $amt WHERE id = $u_id");
            $tx_type = "withdrawal"; 
        } else {
            $conn->query("UPDATE users SET balance = balance + $amt WHERE id = $u_id");
            $tx_type = "deposit";
        }

        // Log to transaction history
        $conn->query("INSERT INTO transactions (user_id, type, amount, status) VALUES ($u_id, '$tx_type', $amt, 'completed')");
        $message = "<div class='alert success'>Successfully updated balance for User #$u_id</div>";
    }

    // B. SUSPEND/ACTIVATE USER
    if (isset($_POST['toggle_status'])) {
        $u_id = intval($_POST['user_id']);
        $new_stat = $_POST['new_status'];
        $conn->query("UPDATE users SET status = '$new_stat' WHERE id = $u_id");
        $message = "<div class='alert success'>User #$u_id is now $new_stat</div>";
    }

    // C. APPROVE DEPOSIT (Amount is taken from the editable input)
    if (isset($_POST['approve_deposit'])) {
        $req_id = intval($_POST['req_id']);
        $u_id = intval($_POST['user_id']);
        
        // This takes the value from the editable input in the table row
        $amt = floatval($_POST['amount']); 

        if ($amt > 0) {
            $conn->query("UPDATE deposit_requests SET status = 'approved', amount = $amt WHERE id = $req_id");
            $conn->query("UPDATE users SET balance = balance + $amt WHERE id = $u_id");
            $conn->query("INSERT INTO transactions (user_id, type, amount, status) VALUES ($u_id, 'deposit', $amt, 'completed')");
            
            $message = "<div class='alert success'>Deposit #$req_id Approved ($$amt added)</div>";
        } else {
            $message = "<div class='alert error'>Cannot approve $0.00. Please enter the valid amount from the receipt/details.</div>";
        }
    }

    // D. REJECT DEPOSIT
    if (isset($_POST['reject_deposit'])) {
        $req_id = intval($_POST['req_id']);
        $conn->query("UPDATE deposit_requests SET status = 'rejected' WHERE id = $req_id");
        $message = "<div class='alert error'>Deposit #$req_id Rejected</div>";
    }

    // E. PROCESS WITHDRAWAL
    if (isset($_POST['process_withdrawal'])) {
        $req_id = intval($_POST['req_id']);
        $conn->query("UPDATE withdrawal_requests SET status = 'approved' WHERE id = $req_id");
        // Update the specific pending transaction logic if you track it there
        $conn->query("UPDATE transactions SET status = 'completed' WHERE user_id = " . intval($_POST['user_id']) . " AND type='withdrawal' AND status='pending' LIMIT 1");
        $message = "<div class='alert success'>Withdrawal #$req_id Marked as Sent</div>";
    }
}

// 3. FETCH DATA
$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
$deposits = $conn->query("SELECT * FROM deposit_requests WHERE status = 'pending' ORDER BY created_at DESC");
$withdrawals = $conn->query("SELECT * FROM withdrawal_requests WHERE status = 'pending' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #0a0e27; color: white; padding: 20px; }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #333; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { color: #00ff87; font-size: 1.5rem; font-weight: bold; }
        .logout-btn { background: #ff4757; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-size: 0.9rem; }
        
        .tabs { margin-bottom: 20px; }
        .tab-btn { background: #1a1f3c; color: #aaa; border: 1px solid #333; padding: 10px 20px; cursor: pointer; border-radius: 5px; margin-right: 5px; transition: 0.3s; }
        .tab-btn:hover { background: #2a2f5c; }
        .tab-btn.active { background: #00ff87; color: #000; font-weight: bold; border-color: #00ff87; }
        
        .tab-content { display: none; animation: fadeIn 0.5s; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        
        table { width: 100%; border-collapse: collapse; background: #111; border-radius: 8px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #222; vertical-align: middle; }
        th { background: #1f2442; color: #00ff87; font-weight: 600; }
        tr:hover { background: #1a1f3c; }
        
        /* Action Buttons */
        .btn { padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer; color: white; font-size: 0.85rem; margin-right: 5px; font-weight: 600; }
        .btn-green { background: #00ff87; color: #000; }
        .btn-red { background: #ff4757; }
        .btn-blue { background: #00d4ff; color: #000; text-decoration: none; display: inline-block; }
        
        /* Inputs inside tables */
        .admin-input { background: #0a0e27; border: 1px solid #333; color: #fff; padding: 8px; border-radius: 4px; width: 100px; text-align: center; font-weight: bold; }
        .admin-input:focus { border-color: #00ff87; outline: none; }

        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background: rgba(0,255,135,0.1); border: 1px solid #00ff87; color: #00ff87; }
        .error { background: rgba(255,71,87,0.1); border: 1px solid #ff4757; color: #ff4757; }

        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); align-items: center; justify-content: center; z-index: 999; backdrop-filter: blur(5px); }
        .modal-content { background: #1a1f3c; padding: 30px; width: 400px; border-radius: 15px; border: 1px solid #444; box-shadow: 0 0 20px rgba(0,255,135,0.2); }
        .modal input, .modal select { width: 100%; padding: 12px; margin: 10px 0 20px 0; background: #0a0e27; border: 1px solid #444; color: white; border-radius: 8px; }
        
        /* Badges */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
        .badge-method { background: #2a2f5c; color: #b8bcc8; border: 1px solid #444; }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">Admin Dashboard</div>
    <div>
        <span style="margin-right: 15px; color: #b8bcc8;">Logged as Admin</span>
        <a href="/log-in/" class="logout-btn">Logout</a>
    </div>
</div>

<?php echo $message; ?>

<div class="tabs">
    <button class="tab-btn active" onclick="openTab('deposits')">Deposits</button>
    <button class="tab-btn" onclick="openTab('withdrawals')">Withdrawals</button>
    <button class="tab-btn" onclick="openTab('users')">Users & Funds</button>
</div>

<div id="deposits" class="tab-content active">
    <h3>Pending Deposits</h3>
    <p style="color:#ff4757; margin-bottom:15px; font-weight:bold;">ðŸš¨ Important: If Amount is $0.00, check the receipt proof, enter the correct value in the editable input, and then click Approve.</p>
    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Method / Details</th>
                <th>Amount (Editable)</th>
                <th>Proof</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if($deposits->num_rows > 0): while($row = $deposits->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['user_id']; ?></td>
                <td>
                    <span class="badge badge-method"><?php echo strtoupper($row['method']); ?></span><br>
                    <?php if(!empty($row['details'])): ?>
                        <div style="margin-top:5px; font-size:0.85rem; color:#00d4ff;">
                            Code/Ref: <?php echo htmlspecialchars($row['details']); ?>
                        </div>
                    <?php endif; ?>
                </td>
                
                <form method="POST">
                <td>
                    <span style="color:#00ff87; font-weight:bold;">$</span>
                    <input type="number" step="0.01" name="amount" value="<?php echo $row['amount']; ?>" class="admin-input">
                </td>
                <td>
                    <?php 
                        $img_path = $row['proof_image'];
                        if(!empty($img_path)) {
                            // FIX: Ensure the path is absolute by stripping leading slashes and adding a single one. 
                            // This assumes 'uploads' is in your root web directory.
                            $display_path = '/' . ltrim($img_path, '/');
                            echo '<a href="'.$display_path.'" target="_blank" class="btn btn-blue">View Receipt</a>';
                        } else {
                            echo '<span style="color:#666;">No File</span>';
                        }
                    ?>
                </td>
                <td>
                    <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                    
                    <button type="submit" name="approve_deposit" class="btn btn-green">Approve</button>
                    <button type="submit" name="reject_deposit" class="btn btn-red">Reject</button>
                </td>
                </form>
                </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">No pending deposits.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="withdrawals" class="tab-content">
    <h3>Pending Withdrawals</h3>
    <table>
        <thead><tr><th>User</th><th>Amount</th><th>Details</th><th>Action</th></tr></thead>
        <tbody>
            <?php if($withdrawals->num_rows > 0): while($row = $withdrawals->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['user_id']; ?></td>
                <td style="color:#ff4757; font-weight:bold;">-$<?php echo number_format($row['amount'], 2); ?></td>
                <td>
                    <span class="badge badge-method"><?php echo htmlspecialchars($row['method']); ?></span><br>
                    <code style="color:#aaa; display:block; margin-top:5px;"><?php echo htmlspecialchars($row['account_details']); ?></code>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <button type="submit" name="process_withdrawal" class="btn btn-green">Mark as Sent</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
                <tr><td colspan="4" style="text-align:center; padding:30px; color:#666;">No pending withdrawals.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="users" class="tab-content">
    <h3>User Management</h3>
    <table>
        <thead><tr><th>ID</th><th>Username</th><th>Current Balance</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php while($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td style="color:#00ff87; font-weight:bold;">$<?php echo number_format($user['balance'], 2); ?></td>
                <td>
                    <?php if($user['status']=='active'): ?>
                        <span class="badge" style="background:#00ff87; color:black;">ACTIVE</span>
                    <?php else: ?>
                        <span class="badge" style="background:#ff4757; color:white;">SUSPENDED</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-blue" onclick="openFundModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">Adjust Funds</button>
                    
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <input type="hidden" name="toggle_status" value="1">
                        <?php if($user['status'] == 'active'): ?>
                            <input type="hidden" name="new_status" value="suspended">
                            <button class="btn btn-red">Suspend</button>
                        <?php else: ?>
                            <input type="hidden" name="new_status" value="active">
                            <button class="btn btn-green">Activate</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div id="fundModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom:10px;">Manage Balance</h3>
        <p style="color:#b8bcc8; margin-bottom:15px;">User: <span id="mUser" style="color:#00ff87; font-weight:bold;"></span></p>
        
        <form method="POST">
            <input type="hidden" name="manage_balance" value="1">
            <input type="hidden" name="user_id" id="mUserId">
            
            <label style="font-size:0.9rem; color:#aaa;">Action</label>
            <select name="type">
                <option value="credit">Credit (Add Funds +)</option>
                <option value="debit">Debit (Remove Funds -)</option>
            </select>
            
            <label style="font-size:0.9rem; color:#aaa;">Amount ($)</label>
            <input type="number" name="amount" step="0.01" placeholder="0.00" required>
            
            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn btn-green" style="flex:1; padding:12px;">Confirm Update</button>
                <button type="button" class="btn btn-red" style="flex:1;" onclick="document.getElementById('fundModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTab(id) {
        document.querySelectorAll('.tab-content').forEach(d => d.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        // Find the button that triggered this and add active class
        const buttons = document.getElementsByClassName('tab-btn');
        for (let btn of buttons) {
            if(btn.getAttribute('onclick').includes(id)) {
                btn.classList.add('active');
            }
        }
    }

    function openFundModal(id, name) {
        document.getElementById('mUserId').value = id;
        document.getElementById('mUser').innerText = name;
        document.getElementById('fundModal').style.display = 'flex';
    }

    // Close modal on outside click
    window.onclick = function(event) {
        let modal = document.getElementById('fundModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
<?php wp_footer(); ?> 
</body>
</html>
<?php $conn->close(); ?>

