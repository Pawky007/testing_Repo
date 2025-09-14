<?php
require __DIR__.'/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add') {
      $veh_no     = trim($_POST['vehicle_no'] ?? '');
      $owner_type = trim($_POST['owner_type'] ?? '');
      $owner_name = trim($_POST['owner_name'] ?? '');
      $truck_type = trim($_POST['truck_type'] ?? '');
      $status     = trim($_POST['status'] ?? 'Available');
      $driver_id  = trim($_POST['driver_id'] ?? '');
      $contact    = trim($_POST['contact'] ?? '');
      $address    = trim($_POST['address'] ?? '');
      $capacity   = (float)($_POST['capacity'] ?? 0);
      $notes      = trim($_POST['notes'] ?? '');

      if ($veh_no === '' || $owner_type === '' || $truck_type === '') throw new Exception('Vehicle no, owner type and truck type are required.');
      if ($owner_type === 'Private' && ($owner_name === '' || $contact === '')) throw new Exception('Owner name and contact are required for private lorries.');

      $stm = $mysqli->prepare("INSERT INTO lorry_owners (vehicle_no, owner_type, owner_name, truck_type, status, driver_id, contact, address, capacity, notes) VALUES (?,?,?,?,?,?,?,?,?,?)");
      $stm->bind_param('ssssssssds', $veh_no, $owner_type, $owner_name, $truck_type, $status, $driver_id, $contact, $address, $capacity, $notes);
      $stm->execute();

      header("Location: Lorry_owner.php");
      exit;

    } elseif ($action === 'update') {
      $id         = (int)($_POST['id'] ?? 0);
      $veh_no     = trim($_POST['vehicle_no'] ?? '');
      $owner_type = trim($_POST['owner_type'] ?? '');
      $owner_name = trim($_POST['owner_name'] ?? '');
      $truck_type = trim($_POST['truck_type'] ?? '');
      $status     = trim($_POST['status'] ?? 'Available');
      $driver_id  = trim($_POST['driver_id'] ?? '');
      $contact    = trim($_POST['contact'] ?? '');
      $address    = trim($_POST['address'] ?? '');
      $capacity   = (float)($_POST['capacity'] ?? 0);
      $notes      = trim($_POST['notes'] ?? '');

      if ($id<=0) throw new Exception('Invalid ID.');
      if ($veh_no === '' || $owner_type === '' || $truck_type === '') throw new Exception('Vehicle no, owner type and truck type are required.');
      if ($owner_type === 'Private' && ($owner_name === '' || $contact === '')) throw new Exception('Owner name and contact are required for private lorries.');

      $stm = $mysqli->prepare("UPDATE lorry_owners SET vehicle_no=?, owner_type=?, owner_name=?, truck_type=?, status=?, driver_id=?, contact=?, address=?, capacity=?, notes=? WHERE id=?");
      $stm->bind_param('ssssssssdis', $veh_no, $owner_type, $owner_name, $truck_type, $status, $driver_id, $contact, $address, $capacity, $notes, $id);
      $stm->execute();

      header("Location: Lorry_owner.php");
      exit;

    } elseif ($action === 'delete') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id<=0) throw new Exception('Invalid ID.');
      $stm = $mysqli->prepare("DELETE FROM lorry_owners WHERE id=?");
      $stm->bind_param('i', $id);
      $stm->execute();
      $msg = 'Lorry owner deleted.';
    }
  } catch(Throwable $e) {
    $msg = 'Error: '.$e->getMessage();
  }
}

$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$edit_owner = null;
if ($edit_id) {
  $st = $mysqli->prepare("SELECT * FROM lorry_owners WHERE id=?");
  $st->bind_param('i', $edit_id);
  $st->execute();
  $edit_owner = $st->get_result()->fetch_assoc();
}

$res = $mysqli->query("SELECT * FROM lorry_owners ORDER BY vehicle_no");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HaulPro — Lorry Owners</title>
<style>
:root {
  --bg: #f9fafb; --surface: #ffffff; --text: #1f2937; --muted: #6b7280;
  --border: #e5e7eb; --primary: #2563eb; --primary-hover: #1d4ed8;
  --danger: #ef4444; --danger-hover: #dc2626; --secondary: #f3f4f6;
  --radius: 10px; --shadow: 0 2px 6px rgba(0,0,0,0.08);
}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:var(--bg);margin:0; color:var(--text);line-height:1.5;}
.shell{max-width:1500px;margin:32px auto;padding:0 16px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}
h2{font-size:1.7rem;font-weight:700;text-align:center;flex-grow:1;color:var(--primary);}
h3{margin-bottom:16px;font-size:1.2rem;color:var(--primary);}
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
.grid input { width: 320px; }
label{font-size:16px;font-weight:600;color:var(--muted);margin-bottom:6px;display:block;}
input,select{padding:12px;border:1px solid var(--border);border-radius:var(--radius);font-size:16px;outline:none;width:100%;transition:border-color 0.2s,box-shadow 0.2s;}
input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(37,99,235,0.15);}
.form-actions{margin-top:20px;display:flex;flex-wrap:wrap;gap:12px;}
.msg{margin:16px 0;padding:12px 16px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:var(--radius);font-size:14px;}
table{width:100%;border-collapse:collapse;font-size:16px;border-radius:var(--radius);overflow:hidden;}
th,td{padding:14px 16px;border-bottom:1px solid var(--border);}
th{background:var(--secondary);font-weight:600;text-align:left;position:sticky;top:0;}
tr:nth-child(even) td{background:#fdfdfd;}
tr:hover td{background:#f1f5ff;}
.actions{display:flex;gap:8px;}
a{text-decoration:none;}

/* Status Badges (lighter pastel colors) */
.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-align: center;
}
.status-available { background: #dcfce7; color: #166534; }
.status-active { background: #dbeafe; color: #1e40af; }
.status-intransit { background: #ffedd5; color: #9a3412; }
.status-delivered { background: #e5e7eb; color: #374151; }
.status-waiting { background: #ede9fe; color: #5b21b6; }
.status-outofservice { background: #fee2e2; color: #991b1b; }
.status-maintenance { background: #fef9c3; color: #854d0e; }
</style>
<script>
function confirmDel(id){
  if(confirm('Delete this lorry owner info?')){
    document.getElementById('del-'+id).submit();
  }
}
function toggleFields(){
  const typeSel=document.querySelector('select[name="owner_type"]');
  const ownerDiv=document.getElementById('ownerNameDiv');
  const ownerInput=document.querySelector('input[name="owner_name"]');
  const contactDiv=document.getElementById('contactDiv');
  const contactInput=document.querySelector('input[name="contact"]');
  if(typeSel.value==='Private'){
    ownerDiv.style.display='block'; ownerInput.required=true;
    contactDiv.style.display='block'; contactInput.required=true;
  } else {
    ownerDiv.style.display='none'; ownerInput.required=false; ownerInput.value='';
    contactDiv.style.display='none'; contactInput.required=false; contactInput.value='';
  }
}
document.addEventListener('DOMContentLoaded',()=>{
  const typeSel=document.querySelector('select[name="owner_type"]');
  if(typeSel){ toggleFields(); typeSel.addEventListener('change',toggleFields); }
});
</script>
</head>
<body>
<div class="shell">
  <div class="topbar">
    <a class="btn link" href="Dashboard.html">⬅ Back to Dashboard</a>
    <h2>🚚 Lorry Owner List</h2>
    <span></span>
  </div>

  <?php if($msg): ?><div class="msg"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

  <div class="card">
    <h3><?= $edit_owner ? '✏️ Edit Lorry Owner' : '➕ Add Lorry Owner' ?></h3>
    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
      <div class="grid">
        <div><label>Vehicle Number</label>
          <input type="text" name="vehicle_no" required value="<?= htmlspecialchars($edit_owner['vehicle_no'] ?? '') ?>">
        </div>
        <div><label>Owner Type</label>
          <select name="owner_type" required>
            <?php
              $types=['Company','Private'];
              $cur=$edit_owner['owner_type'] ?? '';
              foreach($types as $t){ $sel=($t===$cur)?'selected':''; echo "<option $sel>".htmlspecialchars($t)."</option>"; }
            ?>
          </select>
        </div>
        <div id="ownerNameDiv">
          <label>Owner Name</label>
          <input type="text" name="owner_name" value="<?= htmlspecialchars($edit_owner['owner_name'] ?? '') ?>">
        </div>
        <div><label>Truck Type</label>
          <select name="truck_type" required>
            <?php
              $opts=['Small Truck','Medium Truck','Large Truck','Covered Van','Open Truck'];
              $cur=$edit_owner['truck_type'] ?? '';
              foreach($opts as $o){ $sel=($o===$cur)?'selected':''; echo "<option $sel>".htmlspecialchars($o)."</option>"; }
            ?>
          </select>
        </div>
        <div><label>Status</label>
          <select name="status">
            <?php
              $sopts=['Available','Active','In Transit','Delivered','Waiting for Load','Out of Service','Maintenance'];
              $cur=$edit_owner['status'] ?? 'Available';
              foreach($sopts as $s){ $sel=($s===$cur)?'selected':''; echo "<option $sel>".htmlspecialchars($s)."</option>"; }
            ?>
          </select>
        </div>
        <div><label>Driver ID</label>
          <input type="text" name="driver_id" value="<?= htmlspecialchars($edit_owner['driver_id'] ?? '') ?>">
        </div>
        <div id="contactDiv">
          <label>Contact Number</label>
          <input type="text" name="contact" value="<?= htmlspecialchars($edit_owner['contact'] ?? '') ?>">
        </div>
        <div><label>Address</label>
          <input type="text" name="address" value="<?= htmlspecialchars($edit_owner['address'] ?? '') ?>">
        </div>
        <div><label>Capacity (tons)</label>
          <input type="number" step="0.1" name="capacity" value="<?= htmlspecialchars($edit_owner['capacity'] ?? '') ?>">
        </div>
      </div>
      <div class="form-actions">
        <?php if($edit_owner): ?>
          <input type="hidden" name="id" value="<?= (int)$edit_owner['id'] ?>">
          <input type="hidden" name="action" value="update">
          <button class="btn primary" type="submit">💾 Save</button>
          <a class="btn secondary" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">Cancel</a>
        <?php else: ?>
          <input type="hidden" name="action" value="add">
          <button class="btn primary" type="submit">✅ Add Owner</button>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <div class="card">
    <table>
      <thead>
        <tr>
          <th>Vehicle No</th>
          <th>Owner</th>
          <th>Truck Type</th>
          <th>Status</th>
          <th>Driver ID</th>
          <th>Contact</th>
          <th>Capacity</th>
          <th style="width:200px">Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if($res && $res->num_rows): while($row=$res->fetch_assoc()): $id=(int)$row['id']; ?>
        <tr>
          <td><?= htmlspecialchars($row['vehicle_no']) ?></td>
          <td><?= $row['owner_type']==='Company' ? 'Company-Owned' : htmlspecialchars($row['owner_name']) ?></td>
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
          <td><?= htmlspecialchars($row['driver_id']) ?></td>
          <td><?= $row['owner_type']==='Company' ? '—' : htmlspecialchars($row['contact']) ?></td>
          <td><?= htmlspecialchars($row['capacity']) ?> tons</td>
          <td class="actions">
            <a class="btn secondary" href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?edit_id=<?= $id ?>">Edit</a>
            <form id="del-<?= $id ?>" method="post" style="display:inline">
              <input type="hidden" name="id" value="<?= $id ?>">
              <input type="hidden" name="action" value="delete">
              <button type="button" class="btn danger" onclick="confirmDel(<?= $id ?>)">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="8">No lorry owners yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
