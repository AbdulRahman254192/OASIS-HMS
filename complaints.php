<?php
// --- DATABASE CONNECTION & LOGIC ---
include 'db.php';

$message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    
    $customer_id = $_POST['customer_id'];
    $room_id = $_POST['room_id'];
    $description = $_POST['description'];
    $status = 'Pending'; // Default status for new complaints

    $insertSql = "INSERT INTO COMPLAINT (CustomerID, RoomID, Description, Status) VALUES (?, ?, ?, ?)";
    $params = array($customer_id, $room_id, $description, $status);
    
    $insertStmt = sqlsrv_query($conn, $insertSql, $params);

    if ($insertStmt === false) {
        $message = "<div style='color:#ef4444; text-align:center; margin-bottom:20px; font-weight:bold;'>Error logging complaint! Check IDs.</div>";
    } else {
        $message = "<div style='color:#10b981; text-align:center; margin-bottom:20px; font-weight:bold;'>Complaint submitted successfully! Sent to maintenance.</div>";
    }
}

// Bring in the master navigation
include 'header.php'; 
?>

<style>
/* MATCHING THEME STYLES */
.page-header {
    padding: 120px 20px 60px;
    text-align: center;
    background: #0f172a;
    border-bottom: 1px solid rgba(245,158,11,0.2);
}

.page-header h1 {
    font-size: 50px;
    color: #f59e0b;
    margin-bottom: 15px;
}

.page-header p {
    color: #94a3b8;
    font-size: 18px;
}

.container-main {
    padding: 60px;
    max-width: 1200px;
    margin: 0 auto;
}

.card { 
    background:#1e293b; 
    padding:30px; 
    border-radius:12px; 
    border:1px solid rgba(245,158,11,0.2); 
    margin-bottom: 40px;
}

.card h3 { color:#f59e0b; margin-bottom:20px; font-size:24px; }

.form-group { display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
.form-control { 
    flex: 1; 
    padding: 15px; 
    border: 1px solid rgba(245,158,11,0.2); 
    border-radius: 6px; 
    background: #0b1120; 
    color: white; 
    outline: none; 
    font-size: 15px; 
    min-width: 200px; 
}
.form-control:focus { border-color: #f59e0b; }

.btn-primary { 
    background:#f59e0b; 
    color:black; 
    padding:15px 35px;
    border-radius:6px;
    font-weight:600;
    transition:0.3s;
    border: none;
    cursor: pointer;
    font-size: 16px;
    width: 100%;
}
.btn-primary:hover { background:#ffbe3b; }

/* TABLE STYLES */
.table-container { overflow-x:auto; }
table { width:100%; border-collapse:collapse; background:#1e293b; border-radius:10px; overflow:hidden; }
table th { background:#f59e0b; color:black; padding:18px; text-align: left; }
table td { padding:18px; border-bottom:1px solid rgba(255,255,255,0.08); color:#cbd5e1; }
table tr:hover { background:#334155; }
.status-pending { color: #ef4444; font-weight: bold; }
.status-resolved { color: #10b981; font-weight: bold; }
</style>

<div class="page-header">
    <h1>Maintenance & Complaints</h1>
    <p>Log guest issues and track maintenance resolution tickets</p>
</div>

<div class="container-main">
    <?php echo $message; ?>

    <!-- COMPLAINT FORM -->
    <div class="card">
        <h3>Log a New Issue</h3>
        <form method="POST" action="complaints.php">
            <div class="form-group">
                <input type="number" name="customer_id" class="form-control" placeholder="Customer ID" required>
                <input type="number" name="room_id" class="form-control" placeholder="Room ID (e.g., 101)" required>
            </div>
            <div class="form-group">
                <input type="text" name="description" class="form-control" placeholder="Describe the issue (e.g., AC not working, Leaking sink)" required>
            </div>
            <button type="submit" name="submit_complaint" class="btn-primary">Generate Maintenance Ticket</button>
        </form>
    </div>

    <!-- COMPLAINTS TABLE -->
    <div class="table-container">
        <h3>Active Maintenance Tickets</h3>
        <table>
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Customer ID</th>
                    <th>Room ID</th>
                    <th>Issue Description</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($conn) && $conn) {
                    $sql = "SELECT * FROM COMPLAINT ORDER BY ComplaintID DESC";
                    $stmt = sqlsrv_query($conn, $sql);

                    if ($stmt !== false) {
                        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['ComplaintID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['CustomerID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['RoomID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                            
                            // Color code the status
                            $status = htmlspecialchars($row['Status']);
                            $statusClass = ($status == 'Pending') ? 'status-pending' : 'status-resolved';
                            echo "<td class='$statusClass'>" . $status . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No complaints found.</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>