<?php
require __DIR__.'/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add') {
      $reg  = trim($_POST['vehicle_no'] ?? '');
      $type = trim($_POST['truck_type'] ?? '');
      $status = trim($_POST['status'] ?? 'Available');
      if ($reg === '' || $type === '') throw new Exception('Vehicle number and truck type are required.');

      $stm = $mysqli->prepare("INSERT INTO lorry_owners (vehicle_no, truck_type, status) VALUES (?,?,?)");
      $stm->bind_param('sss', $reg, $type, $status);
      $stm->execute();
      $msg = 'Lorry added.';
    } elseif ($action === 'update') {
      $id   = (int)($_POST['id'] ?? 0);
      $reg  = trim($_POST['vehicle_no'] ?? '');
      $type = trim($_POST['truck_type'] ?? '');
      $status = trim($_POST['status'] ?? 'Available');
      if ($id<=0) throw new Exception('Invalid lorry id.');
      if ($reg === '' || $type === '') throw new Exception('Vehicle number and truck type are required.');

      $stm = $mysqli->prepare("UPDATE lorry_owners SET vehicle_no=?, truck_type=?, status=? WHERE id=?");
      $stm->bind_param('sssi', $reg, $type, $status, $id);
      $stm->execute();
      $msg = 'Lorry updated.';
    } elseif ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id<=0) throw new Exception('Invalid lorry id.');
      $stm = $mysqli->prepare("DELETE FROM lorry_owners WHERE id=?");
      $stm->bind_param('i', $id);
      $stm->execute();
      $msg = 'Lorry deleted.';
    }
  } catch(Throwable $e) {
    $msg = 'Error: '.$e->getMessage();
  }
}

$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$edit_truck = null;
if ($edit_id) {
  $st = $mysqli->prepare("SELECT id, vehicle_no, truck_type, status FROM lorry_owners WHERE id=?");
  $st->bind_param('i', $edit_id);
  $st->execute();
  $edit_truck = $st->get_result()->fetch_assoc();
}

$res = $mysqli->query("SELECT id, vehicle_no, truck_type, status FROM lorry_owners ORDER BY vehicle_no");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HaulPro — Lorry List</title>
<style>
:root {
  --bg: #f9fafb; --surface: #ffffff; --text: #1f2937; --muted: #6b7280;
  --border: #e5e7eb; --primary: #2563eb; --primary-hover: #1d4ed8;
  --danger: #ef4444; --danger-hover: #dc2626; --secondary: #f3f4f6;
  --radius: 10px; --shadow: 0 2px 6px rgba(0,0,0,0.08);
}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:var(--bg);margin:0;color:var(--text);}
.shell{max-width:1500px;margin:32px auto;padding:0 16px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}
h2{font-size:1.7rem;font-weight:700;text-align:center;flex-grow:1;color:var(--primary);}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 16px;border-radius:50px;border:none;font-size:14px;font-weight:500;cursor:pointer;transition:background 0.2s,transform 0.1s;text-decoration:none;}
.btn:hover{transform:translateY(-1px);}
.btn.link{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn.primary{background:var(--primary);color:#fff;}
.btn.primary:hover{background:var(--primary-hover);}
.btn.danger{background:var(--danger);color:#fff;}
.btn.danger:hover{background:var(--danger-hover);}
.btn.secondary{background:#eef2ff;color:var(--primary);}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:24px;margin-bottom:24px;}
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; } 
.grid input { width: 450px; }
label{font-size:14px;font-weight:600;color:var(--muted);margin-bottom:6px;display:block;}
input,select{width:100%;padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:14px;outline:none;}
input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(37,99,235,0.15);}
.form-actions{margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;}
.msg{margin:16px 0;padding:12px 16px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:var(--radius);font-size:14px;}
table{width:100%;border-collapse:collapse;font-size:16px;border-radius:var(--radius);overflow:hidden;}
th,td{padding:14px 16px;border-bottom:1px solid var(--border);}
th{background:var(--secondary);font-weight:600;text-align:left;}
tr:nth-child(even) td{background:#fdfdfd;}
tr:hover td{background:#f1f5ff;}
.actions{display:flex;gap:8px;}
/* Status Badges */
.status-badge{display:inline-block;padding:6px 12px;border-radius:20px;font-size:13px;font-weight:600;text-align:center;}
.status-available{background:#dcfce7;color:#166534;}
.status-active{background:#dbeafe;color:#1e40af;}
.status-intransit{background:#ffedd5;color:#9a3412;}
.status-delivered{background:#e5e7eb;color:#374151;}
.status-waiting{background:#ede9fe;color:#5b21b6;}
.status-outofservice{background:#fee2e2;color:#991b1b;}
.status-maintenance{background:#fef9c3;color:#854d0e;}
</style>
<script>
function confirmDel(id){
  if(confirm('Delete this lorry? Associated trips will also be removed.')){
    document.getElementById('del-'+id).submit();
  }
}
function goRow(url){ window.location.href = url; }
function stopEvt(e){ e.stopPropagation(); }
</script>
</head>
<body>
<div class="shell">
  <div class="topbar">
    <a class="btn link" href="Dashboard.html">⬅ Back to Dashboard</a>
    <h2>🚛 Lorry List</h2>
    <span></span>
  </div>

  <?php if($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <!--<div class="card" style="margin-bottom:16px">
    <h3><?= $edit_truck ? '✏️ Edit Lorry' : '➕ Add Lorry' ?></h3>
    <form method="post">
      <div class="grid">
        <div>
          <label>Vehicle No</label>
          <input type="text" name="vehicle_no" required value="<?= htmlspecialchars($edit_truck['vehicle_no'] ?? '') ?>">
        </div>
        <div>
          <label>Truck Type</label>
          <select name="truck_type" required>
            <?php
              $opts=['Small Truck','Medium Truck','Large Truck','Covered Van','Open Truck'];
              $cur=$edit_truck['truck_type'] ?? '';
              foreach($opts as $o){ $sel=($o===$cur)?'selected':''; echo "<option $sel>".htmlspecialchars($o)."</option>"; }
            ?>
          </select>
        </div>
        <div>
          <label>Status</label>
          <select name="status">
            <?php
              $sopts=['Available','Active','In Transit','Delivered','Waiting for Load','Out of Service','Maintenance'];
              $scur=$edit_truck['status'] ?? 'Available';
              foreach($sopts as $s){ $sel=($s===$scur)?'selected':''; echo "<option $sel>".htmlspecialchars($s)."</option>"; }
            ?>
          </select>
        </div>
      </div>
      <div class="form-actions">
        <?php if($edit_truck): ?>
          <input type="hidden" name="id" value="<?= (int)$edit_truck['id'] ?>">
          <input type="hidden" name="action" value="update">
          <button class="btn primary" type="submit">💾 Save</button>
          <a class="btn secondary" href="lorrylist.php">Cancel</a>
        <?php else: ?>
          <input type="hidden" name="action" value="add">
          <button class="btn primary" type="submit">✅ Add Lorry</button>
        <?php endif; ?>
      </div>
    </form>
  </div>-->

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Vehicle No</th>
          <th>Truck Type</th>
          <th>Status</th>
          <th style="width:240px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if($res && $res->num_rows): while($row=$res->fetch_assoc()): $id=(int)$row['id']; ?>
        <tr class="row-link" onclick="goRow('calculationInput.php?truck_id=<?= $id ?>')">
          <td><?= htmlspecialchars($row['vehicle_no']) ?></td>
          <td><?= htmlspecialchars($row['truck_type']) ?></td>
          <td>
            <?php
              $status = $row['status'];
              $class = '';
              switch($status) {
                case 'Available': $class='status-available'; break;
                case 'Active': $class='status-active'; break;
                case 'In Transit': $class='status-intransit'; break;
                case 'Delivered': $class='status-delivered'; break;
                case 'Waiting for Load': $class='status-waiting'; break;
                case 'Out of Service': $class='status-outofservice'; break;
                case 'Maintenance': $class='status-maintenance'; break;
              }
              echo "<span class='status-badge $class'>".htmlspecialchars($status)."</span>";
            ?>
          </td>
          <td class="actions" onclick="stopEvt(event)">
            <!--<a class="btn secondary" href="lorrylist.php?edit_id=<?= $id ?>">Edit</a>-->
            <form id="del-<?= $id ?>" method="post" style="display:inline">
              <input type="hidden" name="id" value="<?= $id ?>">
              <input type="hidden" name="action" value="delete">
              <button type="button" class="btn danger" onclick="confirmDel(<?= $id ?>)">Delete</button>
            </form>
            <a class="btn primary" href="calculationShow.php?truck_id=<?= $id ?>">📊 Trips</a>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="4">No lorries yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
