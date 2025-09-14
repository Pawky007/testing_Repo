<?php
require __DIR__.'/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  try {
    if ($action === 'add') {
      $veh_no = trim($_POST['vehicle_no'] ?? '');
      $owner_type = trim($_POST['owner_type'] ?? '');
      $owner_name = trim($_POST['owner_name'] ?? '');
      $contact = trim($_POST['contact'] ?? '');
      $address = trim($_POST['address'] ?? '');
      $capacity = (float)($_POST['capacity'] ?? 0);

      if ($veh_no === '' || $owner_type === '') throw new Exception('Vehicle number and owner type are required.');
      if ($owner_type === 'Private' && $owner_name === '') throw new Exception('Owner name is required for private lorries.');

      $stm = $mysqli->prepare("INSERT INTO lorry_owners (vehicle_no, owner_type, owner_name, contact, address, capacity) VALUES (?,?,?,?,?,?)");
      $stm->bind_param('sssssd', $veh_no, $owner_type, $owner_name, $contact, $address, $capacity);
      $stm->execute();

      // Redirect after save
      header("Location: Lorry_owner.php");
      exit;

    } elseif ($action === 'update') {
      $id = (int)($_POST['id'] ?? 0);
      $veh_no = trim($_POST['vehicle_no'] ?? '');
      $owner_type = trim($_POST['owner_type'] ?? '');
      $owner_name = trim($_POST['owner_name'] ?? '');
      $contact = trim($_POST['contact'] ?? '');
      $address = trim($_POST['address'] ?? '');
      $capacity = (float)($_POST['capacity'] ?? 0);

      if ($id<=0) throw new Exception('Invalid ID.');
      if ($veh_no === '' || $owner_type === '') throw new Exception('Vehicle number and owner type are required.');
      if ($owner_type === 'Private' && $owner_name === '') throw new Exception('Owner name is required for private lorries.');

      $stm = $mysqli->prepare("UPDATE lorry_owners SET vehicle_no=?, owner_type=?, owner_name=?, contact=?, address=?, capacity=? WHERE id=?");
      $stm->bind_param('ssssssi', $veh_no, $owner_type, $owner_name, $contact, $address, $capacity, $id);
      $stm->execute();

      // Redirect after save
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
  --radius: 8px; --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.05);
}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:var(--bg);margin:0;color:var(--text);}
.shell{max-width:1500px;margin:32px auto;padding:0 16px;}
.topbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}
h2{font-size:1.5rem;font-weight:600;text-align:center;flex-grow:1;}
.btn{display:inline-block;padding:6px 14px;border-radius:var(--radius);border:none;font-size:14px;font-weight:500;cursor:pointer;text-decoration:none;transition:background .2s,transform .1s;}
.btn:hover{transform:translateY(-1px);}
.btn.link{background:transparent;border:1px solid var(--border);color:var(--text);}
.btn.primary{background:var(--primary);color:#fff;}
.btn.primary:hover{background:var(--primary-hover);}
.btn.danger{background:var(--danger);color:#fff;}
.btn.danger:hover{background:var(--danger-hover);}
.btn.secondary{background:#e0e7ff;color:var(--primary);}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;margin-bottom:20px;}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.grid input,.grid select{width:95%;}
label{font-size:15px;font-weight:bold;color:var(--muted);margin-bottom:6px;display:block;}
input,select{padding:10px;border:1px solid var(--border);border-radius:var(--radius);font-size:14px;outline:none;transition:border-color .2s;}
input:focus,select:focus{border-color:var(--primary);box-shadow:0 0 0 2px rgba(37,99,235,0.2);}
.form-actions{margin-top:16px;display:flex;gap:10px;}
.msg{margin:12px 0;padding:10px 14px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:var(--radius);font-size:14px;}
table{width:100%;border-collapse:collapse;font-size:14px;}
th,td{padding:14px 16px;border-bottom:1px solid var(--border);}
th{background:var(--secondary);font-weight:600;text-align:left;}
tr:hover td{background:#f9fafb;}
.actions{display:flex;gap:8px;}
</style>
<script>
function confirmDel(id){
  if(confirm('Delete this lorry owner info?')){
    document.getElementById('del-'+id).submit();
  }
}
function toggleOwnerName(){
  const typeSel=document.querySelector('select[name="owner_type"]');
  const ownerDiv=document.getElementById('ownerNameDiv');
  const ownerInput=document.querySelector('input[name="owner_name"]');
  if(typeSel.value==='Private'){
    ownerDiv.style.display='block';
    ownerInput.required=true;
  } else {
    ownerDiv.style.display='none';
    ownerInput.required=false;
    ownerInput.value='';
  }
}
document.addEventListener('DOMContentLoaded',()=>{
  const typeSel=document.querySelector('select[name="owner_type"]');
  if(typeSel){ toggleOwnerName(); typeSel.addEventListener('change',toggleOwnerName); }
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
        <div>
          <label>Vehicle Number</label>
          <input type="text" name="vehicle_no" required value="<?= htmlspecialchars($edit_owner['vehicle_no'] ?? '') ?>">
        </div>
        <div>
          <label>Owner Type</label>
          <select name="owner_type" required>
            <?php
              $types=['Company','Private'];
              $cur=$edit_owner['owner_type'] ?? '';
              foreach($types as $t){
                $sel=($t===$cur)?'selected':'';
                echo "<option $sel>".htmlspecialchars($t)."</option>";
              }
            ?>
          </select>
        </div>
        <div id="ownerNameDiv">
          <label>Owner Name</label>
          <input type="text" name="owner_name" value="<?= htmlspecialchars($edit_owner['owner_name'] ?? '') ?>">
        </div>
        <div>
          <label>Contact Number</label>
          <input type="text" name="contact" value="<?= htmlspecialchars($edit_owner['contact'] ?? '') ?>">
        </div>
        <div>
          <label>Address</label>
          <input type="text" name="address" value="<?= htmlspecialchars($edit_owner['address'] ?? '') ?>">
        </div>
        <div>
          <label>Capacity (tons)</label>
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
          <td><?= htmlspecialchars($row['contact']) ?></td>
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
        <tr><td colspan="5">No lorry owners yet.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
