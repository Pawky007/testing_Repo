<?php
/****************************************************
 * HaulPro — Admin » Client Payments (LIVE DB)
 * - Users from `users`
 * - Monthly payments from `payments` joined to `invoices`
 *   (prefers invoices.user_id; falls back to payments.user_id if present)
 * - Endpoints:
 *     ?ajax=clients
 *     ?ajax=history&user_id=ID
 ****************************************************/

// ---------- DB CONFIG ----------
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "webtech_project";

// ---------- CONNECT ----------
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
  http_response_code(500);
  die("DB connection failed: ".$mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");

// ---------- Helpers ----------
function jout($x){ header("Content-Type: application/json"); echo json_encode($x); exit; }
function fail($m,$c=400){ http_response_code($c); jout(["error"=>$m]); }
function has_table(mysqli $db, string $t): bool {
  $t = $db->real_escape_string($t);
  $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$t}'");
  return $res && $res->num_rows > 0;
}
function has_col(mysqli $db, string $t, string $c): bool {
  $t = $db->real_escape_string($t);
  $c = $db->real_escape_string($c);
  $res = $db->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{$t}' AND COLUMN_NAME='{$c}'");
  return $res && $res->num_rows > 0;
}
function ym_months_back(int $n=12): array {
  $out=[]; $d=new DateTime('first day of this month 00:00:00');
  for($i=0;$i<$n;$i++){
    $cl=clone $d; $cl->modify("-$i month");
    $out[]=[
      'ym'=>$cl->format('Y-m'),
      'year'=>$cl->format('Y'),
      'month'=>$cl->format('M'),
      'start'=>$cl->format('Y-m-01'),
      'end'  =>$cl->format('Y-m-t')
    ];
  }
  return array_reverse($out);
}

// ---------- AJAX: clients list ----------
if (isset($_GET['ajax']) && $_GET['ajax']==='clients') {
  if (!has_table($mysqli,'users')) jout([]);
  $res = $mysqli->query("SELECT id, COALESCE(full_name,'') AS name, COALESCE(email,'') AS email, DATE(created_at) AS created_at FROM users ORDER BY created_at DESC, id DESC");
  $out=[]; while($r=$res->fetch_assoc()) $out[]=$r;
  jout($out);
}

// ---------- AJAX: 12-month history for a user ----------
if (isset($_GET['ajax']) && $_GET['ajax']==='history') {
  $uid = (int)($_GET['user_id'] ?? 0);
  if ($uid<=0) fail("Invalid user_id");

  $monthlyFee = 1200.00;

  $can_join_invoices = has_table($mysqli,'payments') && has_table($mysqli,'invoices') && has_col($mysqli,'payments','invoice_id') && has_col($mysqli,'invoices','id');
  $has_invoice_user  = $can_join_invoices && has_col($mysqli,'invoices','user_id');
  $has_pay_user      = has_table($mysqli,'payments') && has_col($mysqli,'payments','user_id');

  $cli = null;
  if (has_table($mysqli,'users')) {
    $c = $mysqli->prepare("SELECT id, COALESCE(full_name,'') AS name, COALESCE(email,'') AS email, DATE(created_at) AS created_at FROM users WHERE id=?");
    $c->bind_param("i",$uid); $c->execute(); $cli = $c->get_result()->fetch_assoc();
  }
  if (!$cli) $cli = ["id"=>$uid,"name"=>"","email"=>"","created_at"=>null];

  $months = ym_months_back(12);
  $hist = [];

  foreach($months as $m){
    $sum = 0.0;
    if (has_table($mysqli,'payments')) {
      if ($has_invoice_user) {
        $stmt = $mysqli->prepare("
          SELECT COALESCE(SUM(p.amount_bdt),0) AS total
          FROM payments p
          JOIN invoices i ON i.id = p.invoice_id
          WHERE i.user_id = ?
            AND p.paid_date >= ? AND p.paid_date <= ?
        ");
        $stmt->bind_param("iss", $uid, $m['start'], $m['end']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $sum = (float)($row['total'] ?? 0.0);
      } elseif ($has_pay_user) {
        $stmt = $mysqli->prepare("
          SELECT COALESCE(SUM(amount_bdt),0) AS total
          FROM payments
          WHERE user_id = ?
            AND paid_date >= ? AND paid_date <= ?
        ");
        $stmt->bind_param("iss", $uid, $m['start'], $m['end']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $sum = (float)($row['total'] ?? 0.0);
      }
    }

    $hist[] = [
      "year"   => $m["year"],
      "month"  => $m["month"],
      "ym"     => $m["ym"],
      "status" => ($sum > 0 ? "PAID" : "UNPAID"),
      "paid_on"=> null,
      "fee"    => $monthlyFee,
      "amount" => round($sum,2),
      "txn"    => null
    ];
  }

  jout(["client"=>$cli, "history"=>$hist, "monthly_fee"=>$monthlyFee]);
}

// ---------- Standard page render ----------
$current = basename($_SERVER['PHP_SELF']);
function navActive($f){ global $current; return $current===$f?'active':''; }

$today = new DateTime('today'); $sevenAgo = new DateTime('-7 days');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Client Payments — HaulPro</title>
<link rel="stylesheet" href="dashboad_style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{
  --primary:#2563eb; --primary-hover:#1d4ed8;
  --bg:#f6f8fc; --surface:#ffffff; --border:#e6e8ef; --text:#0f172a;
  --muted:#64748b; --subtle:#334155;
  --radius:14px; --shadow:0 10px 26px rgba(15,23,42,.06);
  --ok:#16a34a; --warn:#f59e0b; --bad:#ef4444;
}
/* Base */
*{box-sizing:border-box}
body{margin:0;background:var(--bg);color:var(--text);font-family:Inter,"Segoe UI",system-ui,-apple-system,sans-serif}
.layout{display:flex;min-height:100vh}

/* ===== Sidebar (neutral — no blue glow) ===== */
.sidebar{
  width:260px;background:var(--surface);border-right:1px solid var(--border);
  padding:18px;display:flex;flex-direction:column;gap:10px;position:sticky;top:0;height:100vh
}
.sidebar img[alt="HaulPro Logo"]{width:160px;height:auto;margin-top:6px}
.sidebar h3{margin:6px 0 10px;font-size:20px;color:#3c4b64;font-weight:800}
.sidebar .menu{list-style:none;margin:8px 0 0;padding:0;display:flex;flex-direction:column;gap:6px}
.sidebar .menu a{
  display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;text-decoration:none;
  color:#0f172a;font-weight:600;border:1px solid transparent
}
.sidebar .menu a img{width:40px;object-fit:contain}
.sidebar .menu a:hover{background:#f3f4f6;border-color:var(--border)}
/* removed blue active background/shadow */
.sidebar .menu a.active{
  background:#e5e7eb; color:#0f172a; border-color:#e5e7eb; box-shadow:none
}
.help-card{
  margin-top:auto;padding:14px;border:1px dashed var(--border);border-radius:12px;background:#fafcff;text-align:center;color:#334155
}
.help-card img{width:60px;height:60px;margin-bottom:8px}
.help-card p{margin:0 0 8px;font-weight:700}
.help-card button{background:#ffc107;border:none;padding:8px 14px;font-weight:800;color:#000;border-radius:8px;cursor:pointer}

/* Main */
.content{flex:1; padding:28px; max-width:1600px; margin:0 auto}
/* page title no blue */
h4{margin:0 0 14px; font-size:24px; font-weight:800; color:#3c4b64}
.card{background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow)}
.table thead th{ background:#f8fafc; color:#334155; font-weight:800; border-bottom:1px solid var(--border) }
.table tbody td{ border-bottom:1px solid var(--border) }
/* neutral row hover (no blue tint) */
.table-hover tbody tr:hover{ background:#f3f4f6 }

/* Buttons (kept as-is; still blue primary) */
.btn-theme{ background:var(--primary); color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:700 }
.btn-theme:hover{ background:var(--primary-hover) }
.btn-outline-theme{ border:1px solid var(--border); color:#334155; background:#f1f5f9; border-radius:10px; font-weight:700 }
.btn-outline-theme:hover{ background:#e2e8f0; color:#0f172a }

/* Badges */
.badge-paid{ background:#e7fff2; color:#0f7a43; border:1px solid #bff0d1; font-weight:700 }
.badge-unpaid{ background:#fff3f0; color:#c3422f; border:1px solid #ffd7cf; font-weight:700 }

/* Modal / receipt */
.modal-content{ border-radius:14px; border:1px solid var(--border); box-shadow:0 20px 50px rgba(2,6,23,.18) }
.modal-header{ border-bottom:1px solid var(--border) }
.modal-footer{ border-top:1px solid var(--border) }
.receipt{ background:#fff; border:1px solid var(--border); border-radius:12px; padding:16px }

/* Print */
@media print{
  .sidebar,.no-print,.modal-header .btn-close{ display:none !important }
  .modal{ position:static }
  .modal-dialog{ max-width:100%; margin:0 }
  .modal-content{ border:none; box-shadow:none }
}
</style>
</head>
<body>
<div class="layout">

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <img src="Image/Logo.png" alt="HaulPro Logo" />
    <h3>HaulPro</h3>
    <ul class="menu">
      <li><a class="<?= navActive('adminDashboard.php') ?>" href="adminDashboard.php"><img src="Image/dashboard.png" alt=""/>Dashboard</a></li>
      <li><a class="<?= navActive('adminShowClients.php') ?>" href="adminShowClients.php"><img src="Image/magnifying-glass.png" alt=""/>Show All Clients</a></li>
      <li><a class="<?= navActive('adminManageVehicles.php') ?>" href="adminManageVehicles.php"><img src="Image/car1.png" alt=""/>Manage Vehicles</a></li>
      <li><a class="<?= navActive('adminPayment.php') ?>" href="adminPayment.php"><img src="Image/wallet.png" alt=""/>Payments</a></li>
      <li><a class="<?= navActive('adminSettings.php') ?>" href="adminSettings.php"><img src="Image/settings.png" alt=""/>Settings</a></li>
    </ul>
    <div class="help-card">
      <img src="https://cdn-icons-png.flaticon.com/512/4712/4712002.png" alt="Help"/>
      <p>Need Help?</p>
      <button>Contact Now</button>
    </div>
  </aside>

  <!-- Main -->
  <main class="content">
    <h4>Client Payments</h4>

    <div class="card p-3">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="clientsTable">
          <thead>
            <tr>
              <th style="min-width:240px">Client</th>
              <th>Email</th>
              <th>Joined</th>
              <th class="text-end" style="min-width:120px">Actions</th>
            </tr>
          </thead>
          <tbody><!-- filled by JS --></tbody>
        </table>
      </div>
      <div class="small text-secondary">Click a row or the History button to view the last 12 months.</div>
    </div>
  </main>
</div>

<!-- History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title fw-bold">Payment History</h5>
          <div class="text-secondary small" id="modalClientInfo"></div>
        </div>
        <div class="no-print d-flex gap-2">
          <button type="button" class="btn btn-outline-theme btn-sm" onclick="window.print()">Print</button>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
      </div>

      <div class="modal-body">
        <div class="receipt">
          <div class="d-flex justify-content-between mb-3">
            <div class="fw-bold">HaulPro — Payment Receipt Summary</div>
            <div class="text-secondary small">Generated: <?= date('d M Y, h:i A') ?></div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle" id="historyTable">
              <thead class="table-light">
                <tr>
                  <th>Month</th><th>Status</th><th>Monthly Fee</th><th>Amount Paid</th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <th colspan="3" class="text-end">Total Paid (12 mo):</th>
                  <th id="totalPaidCell">0 ৳</th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-2 small text-secondary">Live from DB. “PAID” means any payment recorded in that month for the client.</div>
        </div>
      </div>

      <div class="modal-footer no-print">
        <button class="btn btn-outline-theme" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-theme" onclick="window.print()">Print Receipt</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const moneyBDT = n => new Intl.NumberFormat('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}).format(n) + ' ৳';

async function loadClients(){
  const r = await fetch('?ajax=clients');
  const list = await r.json();
  const tb = document.querySelector('#clientsTable tbody');
  tb.innerHTML = '';

  if(!Array.isArray(list) || list.length===0){
    tb.innerHTML = `<tr><td colspan="4" class="text-center text-secondary py-3">No users found.</td></tr>`;
    return;
  }

  list.forEach(u=>{
    const tr = document.createElement('tr');
    tr.dataset.userId = u.id;
    tr.innerHTML = `
      <td><strong>${escapeHtml(u.name||'(No name)')}</strong></td>
      <td>${escapeHtml(u.email||'')}</td>
      <td>${escapeHtml(u.created_at||'')}</td>
      <td class="text-end">
        <button class="btn btn-outline-theme btn-sm history-btn">History</button>
      </td>`;
    tb.appendChild(tr);
  });
}

function escapeHtml(s){
  return String(s==null?'':s)
    .replaceAll('&','&amp;').replaceAll('<','&lt;')
    .replaceAll('>','&gt;').replaceAll('"','&quot;')
    .replaceAll("'","&#39;");
}

const modal = new bootstrap.Modal(document.getElementById('historyModal'));
async function openHistory(userId){
  const r = await fetch(`?ajax=history&user_id=${encodeURIComponent(userId)}`);
  const j = await r.json();
  const client = j.client || {};
  const hist   = j.history || {};
  const fee    = Number(j.monthly_fee || 0);

  document.getElementById('modalClientInfo').textContent =
    `${client.name||'(No name)'} • ${client.email||''} • joined ${client.created_at||''}`;

  const tb = document.querySelector('#historyTable tbody');
  tb.innerHTML = '';
  let total = 0;

  (j.history||[]).forEach(row=>{
    total += Number(row.amount||0);
    const paid = Number(row.amount||0) > 0;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${row.month} ${row.year}</td>
      <td>${paid
        ? '<span class="badge rounded-pill px-3 badge-paid">PAID</span>'
        : '<span class="badge rounded-pill px-3 badge-unpaid">UNPAID</span>'}</td>
      <td>${moneyBDT(fee)}</td>
      <td>${moneyBDT(row.amount||0)}</td>`;
    tb.appendChild(tr);
  });

  document.getElementById('totalPaidCell').textContent = moneyBDT(total);
  modal.show();
}

// Click handlers
document.addEventListener('click', e=>{
  const btn = e.target.closest('.history-btn');
  if (btn){
    const uid = btn.closest('tr')?.dataset.userId;
    if (uid) openHistory(uid);
    return;
  }
  const row = e.target.closest('#clientsTable tbody tr');
  if (row && row.dataset.userId){
    openHistory(row.dataset.userId);
  }
});

// Boot
loadClients();
</script>
</body>
</html>
