<?php
/****************************************************
 * HaulPro — Payment Center (single file)
 * Dynamic with MariaDB/MySQL. No SQL views required.
 * Endpoints: summary, methods, addMethod, renameMethod,
 * setDefault, delMethod, savePrefs, dues, tx, pay, seedDemo, addTrip
 ****************************************************/

// ---------- CONFIG ----------
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "webtech_project";

// Always use customer #1 on this page
$customer_id = 1;

// ---------- DB CONNECT ----------
$mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) { http_response_code(500); die("DB connection failed: ".$mysqli->connect_error); }
$mysqli->set_charset("utf8mb4");

// ---------- ENSURE TABLES EXIST ----------
$mysqli->query("
CREATE TABLE IF NOT EXISTS customers (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(140) NOT NULL,
  contact VARCHAR(80), phone VARCHAR(30), address VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  customer_id BIGINT NOT NULL,
  invoice_no VARCHAR(40) NOT NULL,
  invoice_date DATE NOT NULL,
  due_date DATE NULL,
  status ENUM('Open','Paid','Cancelled') DEFAULT 'Open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (customer_id), INDEX (invoice_date), INDEX (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS invoice_items (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  invoice_id BIGINT NOT NULL,
  description VARCHAR(255) NOT NULL,
  qty DECIMAL(10,2) DEFAULT 1,
  amount_bdt DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS payments (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  invoice_id BIGINT NOT NULL,
  paid_date DATE NOT NULL,
  method VARCHAR(120),
  amount_bdt DECIMAL(12,2) NOT NULL,
  reference VARCHAR(80),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (invoice_id), INDEX (paid_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS payment_methods (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  customer_id BIGINT NOT NULL,
  type ENUM('card','wallet','bank') NOT NULL,
  label VARCHAR(120),
  provider VARCHAR(80),
  brand VARCHAR(40),
  last4 VARCHAR(8),
  exp   VARCHAR(7),
  bank  VARCHAR(120),
  is_default TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS payment_prefs (
  customer_id BIGINT PRIMARY KEY,
  currency VARCHAR(8) DEFAULT 'BDT',
  auto ENUM('No','Yes') DEFAULT 'No',
  email VARCHAR(160) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// ---------- HELPERS ----------
function jout($x){ header("Content-Type: application/json"); echo json_encode($x); exit; }
function first_row($res){ return $res ? $res->fetch_assoc() : null; }
function fail($msg,$code=400){ http_response_code($code); jout(["error"=>$msg]); }

// ---------- DEMO SEEDER ----------
function seed_demo_dues(mysqli $db, int $cid){
  // ensure a customer row
  $st = $db->prepare("INSERT INTO customers (id,name,contact,phone,address)
                      VALUES (?,?,?,?,?)
                      ON DUPLICATE KEY UPDATE name=VALUES(name), contact=VALUES(contact), phone=VALUES(phone), address=VALUES(address)");
  $nm="Demo Customer"; $ct="Billing"; $ph="0123456789"; $ad="Dhaka, Bangladesh";
  $st->bind_param("issss",$cid,$nm,$ct,$ph,$ad);
  if(!$st->execute()) return ["ok"=>0,"error"=>"customers insert: ".$db->error];

  $db->begin_transaction();
  try{
    // invoices
    $sqlInv = "INSERT INTO invoices (customer_id, invoice_no, invoice_date, due_date, status)
               VALUES (?,?,?,?, 'Open')";
    $insInv = $db->prepare($sqlInv);

    $today = new DateTime();

    // three open invoices (one overdue)
    $no1 = "HP-".mt_rand(1000,9999);
    $d1  = (clone $today)->modify("-8 day")->format("Y-m-d");
    $due1= (clone $today)->modify("-1 day")->format("Y-m-d");
    $insInv->bind_param("isss",$cid,$no1,$d1,$due1); $insInv->execute(); $id1=$db->insert_id;

    $no2 = "HP-".mt_rand(1000,9999);
    $d2  = (clone $today)->modify("-5 day")->format("Y-m-d");
    $due2= (clone $today)->modify("+4 day")->format("Y-m-d");
    $insInv->bind_param("isss",$cid,$no2,$d2,$due2); $insInv->execute(); $id2=$db->insert_id;

    $no3 = "HP-".mt_rand(1000,9999);
    $d3  = (clone $today)->modify("-2 day")->format("Y-m-d");
    $due3= (clone $today)->modify("+9 day")->format("Y-m-d");
    $insInv->bind_param("isss",$cid,$no3,$d3,$due3); $insInv->execute(); $id3=$db->insert_id;

    // items (bind with variables, correct type string)
    $sqlItem = "INSERT INTO invoice_items (invoice_id, description, qty, amount_bdt) VALUES (?,?,?,?)";
    $insIt = $db->prepare($sqlItem);
    $rows = [
      [$id1,'Line haul — Dhaka ➜ Chittagong', 1, 18000],
      [$id2,'Backhaul — Chittagong ➜ Dhaka', 1, 15000],
      [$id2,'Fuel charge',                    1,  2000],
      [$id3,'City distribution — 3 drops',    3,  7500],
      [$id3,'Toll & parking',                 1,   800],
    ];
    foreach($rows as $r){
      $iv=(int)$r[0]; $desc=(string)$r[1]; $qty=(float)$r[2]; $amt=(float)$r[3];
      $insIt->bind_param("isdd",$iv,$desc,$qty,$amt);
      if(!$insIt->execute()){ throw new Exception("insert invoice_items failed: ".$db->error); }
    }

    // Ensure a default payment method exists so Pay Now works
    $db->query("INSERT INTO payment_methods (customer_id, type, label, provider, brand, last4, exp, bank, is_default)
                SELECT $cid, 'bank', 'DBBL Current', NULL, NULL, '4321', NULL, 'DBBL', 1
                WHERE NOT EXISTS (SELECT 1 FROM payment_methods WHERE customer_id=$cid AND is_default=1)");

    $db->commit();
    return ["ok"=>1, "invoice_ids"=>[$id1,$id2,$id3]];
  }catch(Throwable $e){
    $db->rollback();
    return ["ok"=>0,"error"=>$e->getMessage()];
  }
}

// ---------- JSON ENDPOINTS ----------
if (isset($_GET['ajax'])) {
  $ajax = $_GET['ajax'];

  // Summary cards (compute dues without views)
  if ($ajax === 'summary') {
    $sum = first_row($mysqli->query("
      SELECT
        COALESCE(SUM(
          GREATEST(
            (
              (SELECT COALESCE(SUM(ii.amount_bdt),0) FROM invoice_items ii WHERE ii.invoice_id=i.id)
              - (SELECT COALESCE(SUM(p.amount_bdt),0)  FROM payments      p  WHERE p.invoice_id = i.id)
            ), 0)
        ),0) AS total_due_bdt,
        COALESCE(SUM(
          CASE
            WHEN ( (SELECT COALESCE(SUM(ii.amount_bdt),0) FROM invoice_items ii WHERE ii.invoice_id=i.id)
                   - (SELECT COALESCE(SUM(p.amount_bdt),0)  FROM payments      p  WHERE p.invoice_id = i.id)
                 ) > 0
             AND (i.due_date IS NOT NULL AND i.due_date < CURDATE())
            THEN 1 ELSE 0
          END
        ),0) AS overdue_count,
        MIN(
          CASE
            WHEN ( (SELECT COALESCE(SUM(ii.amount_bdt),0) FROM invoice_items ii WHERE ii.invoice_id=i.id)
                   - (SELECT COALESCE(SUM(p.amount_bdt),0)  FROM payments      p  WHERE p.invoice_id = i.id)
                 ) > 0
            THEN i.due_date
          END
        ) AS next_due
      FROM invoices i
      WHERE i.customer_id = {$customer_id} AND i.status <> 'Cancelled'
    ")) ?: ["total_due_bdt"=>0,"overdue_count"=>0,"next_due"=>null];

    // count invoices and methods for bootstrapping UI
    $inv_count = (int) (first_row($mysqli->query("SELECT COUNT(*) c FROM invoices WHERE customer_id={$customer_id}"))['c'] ?? 0);
    $pm_count  = (int) (first_row($mysqli->query("SELECT COUNT(*) c FROM payment_methods WHERE customer_id={$customer_id}"))['c'] ?? 0);

    $def = first_row($mysqli->query("
      SELECT id,type,label,provider,brand,last4,exp,bank,(is_default+0) AS is_default
      FROM payment_methods
      WHERE customer_id={$customer_id} AND is_default=1
      ORDER BY id DESC LIMIT 1
    "));
    $prefs = first_row($mysqli->query("SELECT currency,auto,email FROM payment_prefs WHERE customer_id={$customer_id}"))
          ?: ["currency"=>"BDT","auto"=>"No","email"=>null];

    $rc = first_row($mysqli->query("
      SELECT COUNT(*) AS receipts
      FROM payments p
      JOIN invoices i ON i.id = p.invoice_id
      WHERE i.customer_id = {$customer_id}
    ")) ?: ["receipts"=>0];

    jout([
      "total_due_bdt" => (float)$sum["total_due_bdt"],
      "overdue_count" => (int)$sum["overdue_count"],
      "next_due"      => $sum["next_due"],
      "default_method"=> $def,
      "prefs"         => $prefs,
      "receipts"      => (int)$rc["receipts"],
      "inv_count"     => $inv_count,
      "pm_count"      => $pm_count
    ]);
  }

  // Methods list
  if ($ajax === 'methods') {
    $rows = $mysqli->query("SELECT id,type,label,provider,brand,last4,exp,bank,(is_default+0) AS is_default,created_at
                            FROM payment_methods
                            WHERE customer_id={$customer_id}
                            ORDER BY is_default DESC, id DESC");
    $out=[]; while($r=$rows->fetch_assoc()) $out[]=$r; jout($out);
  }

  // Add method
  if ($ajax === 'addMethod' && $_SERVER['REQUEST_METHOD']==='POST') {
    $type = $_POST['type'] ?? '';
    if (!in_array($type,['card','wallet','bank'])) fail("Invalid type");
    $label = trim($_POST['label'] ?? '');
    $provider = trim($_POST['provider'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $last4 = trim($_POST['last4'] ?? '');
    $exp   = trim($_POST['exp'] ?? '');
    $bank  = trim($_POST['bank'] ?? '');
    $hasAny = first_row($mysqli->query("SELECT id FROM payment_methods WHERE customer_id={$customer_id} LIMIT 1"));
    $is_def = $hasAny ? 0 : 1;
    $q = $mysqli->prepare("INSERT INTO payment_methods (customer_id,type,label,provider,brand,last4,exp,bank,is_default)
                           VALUES (?,?,?,?,?,?,?,?,?)");
    $q->bind_param("isssssssi",$customer_id,$type,$label,$provider,$brand,$last4,$exp,$bank,$is_def);
    $q->execute();
    jout(["ok"=>1,"id"=>$q->insert_id,"is_default"=>$is_def]);
  }

  // Rename method
  if ($ajax === 'renameMethod' && $_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['id'] ?? 0);
    $label = trim($_POST['label'] ?? '');
    if ($id<=0) fail("Invalid id");
    $q=$mysqli->prepare("UPDATE payment_methods SET label=? WHERE id=? AND customer_id=?");
    $q->bind_param("sii",$label,$id,$customer_id);
    $q->execute(); jout(["ok"=>1]);
  }

  // Set default
  if ($ajax === 'setDefault' && $_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id<=0) fail("Invalid id");
    $mysqli->query("UPDATE payment_methods SET is_default=0 WHERE customer_id={$customer_id}");
    $mysqli->query("UPDATE payment_methods SET is_default=1 WHERE id={$id} AND customer_id={$customer_id}");
    jout(["ok"=>1]);
  }

  // Delete method
  if ($ajax === 'delMethod' && $_SERVER['REQUEST_METHOD']==='POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id<=0) fail("Invalid id");
    $mysqli->query("DELETE FROM payment_methods WHERE id={$id} AND customer_id={$customer_id} LIMIT 1");
    $hasDef = first_row($mysqli->query("SELECT id FROM payment_methods WHERE customer_id={$customer_id} AND is_default=1 LIMIT 1"));
    if (!$hasDef) {
      $last = first_row($mysqli->query("SELECT id FROM payment_methods WHERE customer_id={$customer_id} ORDER BY id DESC LIMIT 1"));
      if ($last) $mysqli->query("UPDATE payment_methods SET is_default=1 WHERE id={$last['id']}");
    }
    jout(["ok"=>1]);
  }

  // Save preferences
  if ($ajax === 'savePrefs' && $_SERVER['REQUEST_METHOD']==='POST') {
    $cur  = in_array($_POST['currency'] ?? 'BDT',['BDT','USD']) ? $_POST['currency'] : 'BDT';
    $auto = ($_POST['auto'] ?? 'No') === 'Yes' ? 'Yes' : 'No';
    $email= $_POST['email'] ?? null;
    $row = first_row($mysqli->query("SELECT customer_id FROM payment_prefs WHERE customer_id={$customer_id}"));
    if ($row) { $q=$mysqli->prepare("UPDATE payment_prefs SET currency=?,auto=?,email=? WHERE customer_id=?"); $q->bind_param("sssi",$cur,$auto,$email,$customer_id); }
    else { $q=$mysqli->prepare("INSERT INTO payment_prefs (currency,auto,email,customer_id) VALUES (?,?,?,?)"); $q->bind_param("sssi",$cur,$auto,$email,$customer_id); }
    $q->execute(); jout(["ok"=>1]);
  }

  // Dues (outstanding invoices)
  if ($ajax === 'dues') {
    $rows = $mysqli->query("
      SELECT
        i.id AS invoice_id, i.invoice_no, i.invoice_date, i.due_date, i.status,
        GREATEST(
          (
            (SELECT COALESCE(SUM(ii.amount_bdt),0) FROM invoice_items ii WHERE ii.invoice_id=i.id)
            - (SELECT COALESCE(SUM(p.amount_bdt),0)  FROM payments      p  WHERE p.invoice_id = i.id)
          ), 0) AS due_bdt
      FROM invoices i
      WHERE i.customer_id={$customer_id} AND i.status <> 'Cancelled'
      HAVING due_bdt > 0
      ORDER BY (CASE WHEN i.due_date IS NULL THEN 1 ELSE 0 END), i.due_date, i.invoice_date
    ");
    $out=[]; while($r=$rows->fetch_assoc()) $out[]=$r; jout($out);
  }

  // Transactions
  if ($ajax === 'tx') {
    $rows = $mysqli->query("
      SELECT p.id, p.paid_date, p.method, p.amount_bdt, p.reference, i.invoice_no
      FROM payments p
      JOIN invoices i ON i.id = p.invoice_id
      WHERE i.customer_id={$customer_id}
      ORDER BY p.paid_date DESC, p.id DESC
    ");
    $out=[]; while($r=$rows->fetch_assoc()){
      $out[]=[
        "t"=>$r["paid_date"],
        "act"=>"invoice.pay #".$r["invoice_no"],
        "amt"=>number_format((float)$r["amount_bdt"],2,'.',''),
        "status"=>"ok",
        "method"=>$r["method"],
        "reference"=>$r["reference"],
      ];
    }
    jout($out);
  }

  // Apply payment (oldest invoices first)
  if ($ajax === 'pay' && $_SERVER['REQUEST_METHOD']==='POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $method_id = (int)($_POST['method_id'] ?? 0);
    if ($amount<=0) fail("Invalid amount");

    $pm = first_row($mysqli->query("SELECT * FROM payment_methods WHERE id={$method_id} AND customer_id={$customer_id}"));
    if (!$pm) $pm = first_row($mysqli->query("SELECT * FROM payment_methods WHERE customer_id={$customer_id} AND is_default=1 LIMIT 1"));
    if (!$pm) fail("No payment method found. Add or set a default method first.");

    $invq = $mysqli->query("
      SELECT
        i.id AS invoice_id,
        GREATEST(
          (
            (SELECT COALESCE(SUM(ii.amount_bdt),0) FROM invoice_items ii WHERE ii.invoice_id=i.id)
            - (SELECT COALESCE(SUM(p.amount_bdt),0)  FROM payments      p  WHERE p.invoice_id = i.id)
          ), 0) AS due_bdt
      FROM invoices i
      WHERE i.customer_id={$customer_id} AND i.status <> 'Cancelled'
      HAVING due_bdt > 0
      ORDER BY (CASE WHEN i.due_date IS NULL THEN 1 ELSE 0 END), i.due_date, i.invoice_date
      FOR UPDATE
    ");

    $mysqli->begin_transaction();
    try{
      $remain = $amount;

      $methodLabel = $pm["type"]==='card' ? (($pm["brand"]?:'Card').' ****'.($pm["last4"]?:'----'))
                    : ($pm["type"]==='wallet' ? (($pm["provider"]?:'Wallet').' ****'.($pm["last4"]?:'----'))
                    : (($pm["bank"]?:'Bank').' ****'.($pm["last4"]?:'----')));

      while($row = $invq->fetch_assoc()){
        if ($remain<=0) break;
        $due = (float)$row["due_bdt"];
        if ($due<=0) continue;
        $pay = min($remain, $due);

        $stmt = $mysqli->prepare("INSERT INTO payments (invoice_id, paid_date, method, amount_bdt, reference) VALUES (?,?,?,?,?)");
        $ref = "HP-".date("YmdHis")."-".$pm["id"];
        $pd  = date("Y-m-d");
        $stmt->bind_param("issds", $row["invoice_id"], $pd, $methodLabel, $pay, $ref);
        $stmt->execute();

        $remain -= $pay;
      }
      $mysqli->commit();
      jout(["ok"=>1,"paid_total"=>$amount-$remain,"unallocated"=>$remain]);
    }catch(Throwable $e){
      $mysqli->rollback(); fail("Payment failed: ".$e->getMessage(),500);
    }
  }

  // Seed demo dues
  if ($ajax === 'seedDemo') {
    $res = seed_demo_dues($mysqli, $customer_id);
    if (!($res["ok"]??0)) fail("Seeder error: ".($res["error"]??"unknown"), 500);
    jout($res);
  }

  // NEW: Create invoice from a trip (fuel/driver/labour/toll/misc + line-haul)
  if ($ajax === 'addTrip' && $_SERVER['REQUEST_METHOD']==='POST') {
    // Inputs (numbers can be empty)
    $route  = trim($_POST['route'] ?? 'Trip');
    $haul   = (float)($_POST['haul']   ?? 0);
    $fuel   = (float)($_POST['fuel']   ?? 0);
    $driver = (float)($_POST['driver'] ?? 0);
    $labour = (float)($_POST['labour'] ?? 0);
    $toll   = (float)($_POST['toll']   ?? 0);
    $misc   = (float)($_POST['misc']   ?? 0);

    if(($haul+$fuel+$driver+$labour+$toll+$misc) <= 0){
      fail("No cost provided");
    }

    $mysqli->begin_transaction();
    try{
      // invoice header
      $no   = "HP-".mt_rand(1000,9999);
      $invD = date("Y-m-d");
      $dueD = date("Y-m-d", strtotime("+7 days"));
      $q = $mysqli->prepare("INSERT INTO invoices (customer_id, invoice_no, invoice_date, due_date, status) VALUES (?,?,?,?, 'Open')");
      $q->bind_param("isss",$customer_id,$no,$invD,$dueD);
      $q->execute(); $inv_id = $mysqli->insert_id;

      // items
      $ins = $mysqli->prepare("INSERT INTO invoice_items (invoice_id, description, qty, amount_bdt) VALUES (?,?,?,?)");
      $iv=$inv_id; $qty=1.0;

      if($haul>0){  $desc="Line haul — ".$route; $amt=$haul;  $ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }
      if($fuel>0){  $desc="Fuel charge";           $amt=$fuel;  $ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }
      if($driver>0){$desc="Driver charge";         $amt=$driver;$ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }
      if($labour>0){$desc="Labour charge";         $amt=$labour;$ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }
      if($toll>0){  $desc="Toll & parking";        $amt=$toll;  $ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }
      if($misc>0){  $desc="Misc charge";           $amt=$misc;  $ins->bind_param("isdd",$iv,$desc,$qty,$amt); $ins->execute(); }

      // make sure there is a default payment method
      $mysqli->query("INSERT INTO payment_methods (customer_id, type, label, provider, brand, last4, exp, bank, is_default)
                      SELECT {$customer_id}, 'bank', 'DBBL Current', NULL, NULL, '4321', NULL, 'DBBL', 1
                      WHERE NOT EXISTS (SELECT 1 FROM payment_methods WHERE customer_id={$customer_id} AND is_default=1)");

      $mysqli->commit();
      jout(["ok"=>1,"invoice_id"=>$inv_id,"invoice_no"=>$no,"due_date"=>$dueD]);
    }catch(Throwable $e){
      $mysqli->rollback(); fail("Trip add failed: ".$e->getMessage(),500);
    }
  }

  fail("Unknown ajax endpoint",404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HaulPro — Payment Center</title>
  <link rel="stylesheet" href="dashboad_style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    :root{
      --primary:#2563eb;--primary-hover:#1d4ed8;--bg:#f6f8fc;--surface:#fff;--border:#e6e8ef;--text:#0f172a;
      --muted:#64748b;--subtle:#334155;--radius:14px;--shadow:0 6px 18px rgba(0,0,0,0.06);
      --ok:#16a34a;--warn:#f59e0b;--bad:#ef4444;--chip:#eef2ff;--chiptext:#3730a3
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,"Segoe UI",system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--text)}
    .container{display:flex;min-height:100vh}
    main{flex:1;padding:24px;max-width:1700px;margin:0 auto}
    .header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px}
    .header h1{margin:0;font-size:26px;color:var(--primary)}
    .btn-row{display:flex;gap:10px;flex-wrap:wrap}

    .tabs{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0}
    .tab-btn{padding:10px 14px;border:1px solid var(--border);background:#fff;border-radius:999px;font-weight:600;cursor:pointer}
    .tab-btn.active{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 6px 16px rgba(37,99,235,.25)}
    .tab{display:none}.tab.active{display:block;animation:fade .25s ease}
    @keyframes fade{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

    .grid{display:grid;gap:14px}
    .cols-2{grid-template-columns:1fr 1fr}
    .cols-3{grid-template-columns:repeat(3,minmax(0,1fr))}
    .cols-4{grid-template-columns:repeat(4,minmax(0,1fr))}
    @media (max-width:1050px){.cols-4{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media (max-width:860px){.cols-3,.cols-2,.cols-4{grid-template-columns:1fr}}

    .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);padding:16px}
    h2{font-size:20px;margin:0 0 10px} h3{font-size:16px;margin:8px 0}
    label{display:block;font-weight:600;margin:6px 0 4px} input,select,textarea{width:100%;padding:12px;border:1px solid var(--border);border-radius:10px;background:#fff;font-size:15px}
    input:disabled, select:disabled{opacity:.6;cursor:not-allowed}
    .help{font-size:12px;color:var(--muted);margin-top:4px}
    .btn{background:var(--primary);color:#fff;padding:10px 16px;border:none;border-radius:10px;font-weight:600;cursor:pointer}
    .btn:hover{background:var(--primary-hover)} .btn.secondary{background:#f1f5f9;color:#0f172a;border:1px solid var(--border)} .btn.ghost{background:transparent;border:1px dashed var(--border);color:#0f172a} .btn.danger{background:var(--bad)}
    .btn[disabled]{opacity:.6;cursor:not-allowed}
    .seg{display:flex;gap:6px;background:#f1f5f9;padding:6px;border-radius:999px;width:max-content;margin-bottom:8px}
    .seg button{background:transparent;border:none;padding:8px 14px;border-radius:999px;cursor:pointer;font-weight:600}
    .seg button.active{background:#fff;box-shadow:var(--shadow);border:1px solid var(--border)}
    .stat{display:flex;align-items:center;justify-content:space-between}
    .stat .big{font-size:28px;font-weight:800}
    .chip{display:inline-block;padding:4px 8px;border-radius:999px;font-size:12px;background:var(--chip);color:var(--chiptext)}

    table{width:100%;border-collapse:separate;border-spacing:0;border:1px solid var(--border);border-radius:12px;overflow:hidden;background:#fff}
    thead th{background:#f8fafc;font-weight:700;color:#334155;position:sticky;top:0;z-index:1}
    th,td{padding:10px 12px;border-bottom:1px solid var(--border);font-size:14px;text-align:left}
    tbody tr:nth-child(odd){background:#fcfdff}
    tbody tr:hover{background:#f9fbff}
    .tag{display:inline-block;padding:4px 8px;border-radius:999px;font-size:12px;background:#eef2ff;color:#3730a3}.tag.green{background:#ecfdf5;color:#065f46}.tag.yellow{background:#fffbeb;color:#92400e}.tag.gray{background:#f3f4f6;color:#374151}

    .toast{position:fixed;right:24px;bottom:24px;padding:12px 16px;background:#111827;color:#fff;border-radius:10px;box-shadow:var(--shadow);opacity:0;transform:translateY(10px);transition:.2s}
    .toast.show{opacity:1;transform:translateY(0)}
    .note{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:10px;border-radius:10px;margin-bottom:12px}

    .empty{padding:18px;border:1px dashed var(--border);border-radius:12px;background:#fff; color:#475569}
  </style>
</head>
<body>
<div class="container">
  <aside class="sidebar" id="sidebar">
    <img src="Image/Logo.png" alt="HaulPro Logo" width="160" />
    <h3>HaulPro</h3>
    <ul class="menu">
      <li><a href="dashboard.php"><img src="Image/dashboard.png" alt="" />Dashboard</a></li>
      <li class="has-submenu">
        <a href="#"><img src="Image/chart.png" alt="" />Analysis</a>
        <ul class="submenu">
          <li><a href="delivery_performance.php"><img src="Image/continuous-improvement.png" alt=""/>Delivery Performance</a></li>
          <li><a href="Revenue_analysis.php"><img src="Image/profit-margin.png" alt=""/>Revenue Analysis</a></li>
          <li><a href="fleet_analysis.php"><img src="Image/delivery-truck.png" alt=""/>Fleet Efficiency</a></li>
        </ul>
      </li>
      <li><a href="#"><img src="Image/plus.png" alt="" style="width:40px" />Add Trips</a></li>
      <li><a href="Payment_customer.php" class="active"><img src="Image/wallet.png" alt="" style="width:40px" />Payment Method</a></li>
      <li><a href="Lorry_owner.php"><img src="Image/businessman.png" alt="" style="width:40px" />Lorry Owner List</a></li>
      <li><a href="lorrylist.php"><img src="Image/truck.png" alt="" style="width:40px" />Lorry List</a></li>
      <li><a href="Admin_settings.php"><img src="Image/settings.png" alt="" style="width:40px" />Settings</a></li>
      <li><a href="faq.html"><img src="Image/faq.png" alt="" style="width:40px" />FAQ</a></li>
    </ul>
    <div class="help-card">
      <img src="https://cdn-icons-png.flaticon.com/512/4712/4712002.png" alt="Help"/>
      <p>Need Help?</p>
      <button>Contact Now</button>
    </div>
  </aside>

  <main>
    <div class="header">
      <h1>💳 Payment Center</h1>
      <div class="btn-row">

        <button class="btn secondary" id="exportTx">⬇️ Export Transactions CSV</button>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="grid cols-4" id="summary">
      <div class="card stat">
        <div>
          <div class="subtle">Total Due</div>
          <div class="big" id="sum_due">—</div>
        </div>
        <div class="btn-row">
          <button class="btn" id="quickPay">Pay Now</button>
          <button class="btn ghost" id="seedDemoBtn">🌱 Add Demo Dues</button>
        </div>
      </div>
      <div class="card stat">
        <div><div class="subtle">Overdue</div><div class="big" id="sum_overdue">—</div></div>
        <span class="chip" id="sum_next">Next due: —</span>
      </div>
      <div class="card stat">
        <div><div class="subtle">Default Method</div><div class="big" id="sum_default">—</div></div>
        <span class="chip" id="sum_auto">Auto-charge: Off</span>
      </div>
      <div class="card stat">
        <div><div class="subtle">Receipts</div><div class="big" id="sum_receipts">0</div></div>
        <button class="btn secondary" id="printLast">🧾 Last Receipt</button>
      </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab-btn active" data-tab="methods">Methods</button>
      <button class="tab-btn" data-tab="dues">Dues & Pay</button>
      <button class="tab-btn" data-tab="activity">Activity & Receipts</button>
    </div>

    <!-- METHODS TAB -->
    <section id="methods" class="tab active">
      <div class="grid cols-2">
        <!-- Left: add -->
        <div class="card">
          <h2>Add New Method</h2>
          <div class="seg" id="paySeg">
            <button data-kind="card" class="active">Card</button>
            <button data-kind="wallet">Mobile Wallet</button>
            <button data-kind="bank">Bank Account</button>
          </div>

          <div id="form_card">
            <div class="grid cols-2">
              <div><label>Name on Card</label><input id="c_name" placeholder="Jane Customer"/></div>
              <div><label>Label (optional)</label><input id="c_label" placeholder="Personal Visa"/></div>
            </div>
            <div class="grid cols-3">
              <div><label>Card Number</label><input id="c_number" placeholder="4242 4242 4242 4242" inputmode="numeric" maxlength="19"/><div class="help">Only last 4 is stored</div></div>
              <div><label>Expiry (MM/YY)</label><input id="c_exp" placeholder="12/27" maxlength="5"/></div>
              <div><label>CVC</label><input id="c_cvc" placeholder="123" inputmode="numeric" maxlength="4"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addCard">➕ Add Card</button></div>
          </div>

          <div id="form_wallet" style="display:none">
            <div class="grid cols-2">
              <div><label>Provider</label>
                <select id="w_provider"><option>bKash</option><option>Nagad</option><option>Rocket</option><option>Upay</option></select>
              </div>
              <div><label>Wallet Number</label><input id="w_number" placeholder="+8801XXXXXXXXX"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addWallet">➕ Link Wallet</button></div>
            <div class="help">We store masked last 4 only.</div>
          </div>

          <div id="form_bank" style="display:none">
            <div class="grid cols-2">
              <div><label>Account Name</label><input id="b_name" placeholder="Your Name or Company"/></div>
              <div><label>Account Number</label><input id="b_ac" placeholder="XXXXXXXXXXXX"/></div>
            </div>
            <div class="grid cols-2">
              <div><label>Bank & Branch</label><input id="b_branch" placeholder="Bank name, branch"/></div>
              <div><label>Routing / SWIFT (optional)</label><input id="b_swift" placeholder="XXXXXX"/></div>
            </div>
            <div class="btn-row"><button class="btn" id="addBank">➕ Add Bank</button></div>
          </div>
        </div>

        <!-- Right: saved -->
        <div class="card">
          <h2>Saved Methods</h2>
          <table id="pmTable">
            <thead><tr><th>Type</th><th>Label / Provider</th><th>Details</th><th>Default</th><th style="width:240px">Actions</th></tr></thead>
            <tbody></tbody>
          </table>
          <div class="help">Set one default method. Rename or remove anytime.</div>
        </div>
      </div>

      <div class="card" style="margin-top:14px">
        <h2>Billing Preferences</h2>
        <div id="noDueMsg" class="note" style="display:none">
          No outstanding invoices found. You can <b>add demo dues</b> from the Total Due card to see the flow.
        </div>
        <div class="grid cols-3">
          <div><label>Default Currency</label><select id="p_currency"><option>BDT</option><option>USD</option></select></div>
          <div><label>Auto-Charge on Due Date</label><select id="p_auto"><option>No</option><option>Yes</option></select><div class="help">Auto-charge the default method on invoice due date</div></div>
          <div><label>Receipt Email</label><input id="p_email" type="email" placeholder="billing@company.com"/></div>
        </div>
        <div class="btn-row" style="margin-top:8px"><button class="btn" id="savePrefs">💾 Save Preferences</button></div>
      </div>
    </section>

    <!-- DUES & PAY TAB -->
    <section id="dues" class="tab">
      <div class="grid cols-3">
        <div class="card stat" style="grid-column:1/-1">
          <div>
            <div class="subtle">Your Current Due</div>
            <div class="big" id="due_total">—</div>
            <div class="help">Includes outstanding invoices</div>
          </div>
          <div class="btn-row">
            <button class="btn" id="payFull">Pay Full Due</button>
            <button class="btn secondary" id="payPartialBtn">Pay Partial</button>
          </div>
        </div>

        <div class="card" style="grid-column: span 2">
          <h2>Open Items</h2>
          <div id="dueEmpty" class="empty" style="display:none">No open invoices yet.</div>
          <table id="dueTable">
            <thead><tr><th>Date</th><th>Invoice</th><th>Amount</th><th>Status</th><th style="width:160px">Action</th></tr></thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="card">
          <h2>Pay Due</h2>
          <label>Amount</label>
          <input id="pay_amount" placeholder="e.g., 5000" inputmode="decimal"/>
          <label>Pay with</label>
          <select id="pay_method"></select>
          <div class="help">Default method is preselected when available</div>
          <div class="btn-row" style="margin-top:8px">
            <button class="btn" id="btnPay" disabled>💸 Pay Now</button>
          </div>
        </div>
      </div>
    </section>

    <!-- ACTIVITY TAB -->
    <section id="activity" class="tab">
      <div class="grid cols-3">
        <div class="card" style="grid-column:1/-1">
          <h2>Transactions</h2>
          <div class="grid cols-3" style="align-items:end">
            <div><label>Filter Type</label>
              <select id="f_type"><option value="">All</option><option value="invoice.pay">Payments</option></select>
            </div>
            <div><label>Date Contains</label><input id="f_date" placeholder="e.g., 2025-09"/></div>
            <div class="btn-row"><button class="btn secondary" id="applyTxFilter">Apply</button><button class="btn" id="clearTxFilter">Clear</button></div>
          </div>
          <table id="txTable"><thead><tr><th>Date</th><th>Action</th><th>Amount</th><th>Status</th><th style="width:160px">Receipt</th></tr></thead><tbody></tbody></table>
        </div>
      </div>
    </section>

    <div class="toast" id="toast">Saved.</div>
  </main>
</div>

<script>
'use strict';

const CURRENCY = { BDT: '৳', USD: '$' };
const money = (n,cur) => (CURRENCY[cur]||'') + Number(n||0).toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
const toast=(m='Saved.')=>{const t=document.getElementById('toast'); t.textContent=m; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),1600)};

// Tabs
(function setupTabs(){
  const tabBtns=document.querySelectorAll('.tab-btn'); const tabs=document.querySelectorAll('.tab');
  tabBtns.forEach(btn=>btn.addEventListener('click',()=>{
    tabBtns.forEach(b=>b.classList.remove('active')); tabs.forEach(t=>t.classList.remove('active'));
    btn.classList.add('active'); document.getElementById(btn.dataset.tab).classList.add('active');
  }));
})();

// Summary
async function loadSummary(){
  const r = await fetch('?ajax=summary&customer_id=1'); const j = await r.json();
  const cur = j.prefs?.currency || 'BDT';
  document.getElementById('sum_due').textContent      = money(j.total_due_bdt, cur);
  document.getElementById('sum_overdue').textContent  = j.overdue_count ?? 0;
  document.getElementById('sum_next').textContent     = 'Next due: ' + (j.next_due || '—');
  document.getElementById('sum_default').textContent  = j.default_method ? (j.default_method.label || j.default_method.type) : '—';
  document.getElementById('sum_receipts').textContent = j.receipts || 0;
  document.getElementById('p_currency').value = cur;
  document.getElementById('p_auto').value     = (j.prefs?.auto || 'No');
  document.getElementById('p_email').value    = (j.prefs?.email || '');
  const noDue = Number(j.total_due_bdt||0)===0;
  document.getElementById('noDueMsg').style.display = noDue ? 'block' : 'none';
  refreshPayButtonState();
}

// Methods
async function loadMethods(){
  const r = await fetch('?ajax=methods&customer_id=1'); const list = await r.json();
  const tb=document.querySelector('#pmTable tbody'); tb.innerHTML='';

  if (!list.length){
    const tr=document.createElement('tr'); tr.innerHTML = `<td colspan="5"><div class="empty">No saved methods yet. Add a card, wallet, or bank.</div></td>`;
    tb.appendChild(tr);
  }

  list.forEach(m=>{
    const isDef = Number(m.is_default) === 1;
    const details = m.type==='card'   ? `${m.brand||'Card'} •••• ${m.last4||'----'} ${m.exp?'('+m.exp+')':''}`
                  : m.type==='wallet' ? `${m.provider||'Wallet'} •••• ${m.last4||'----'}`
                  :                    `${m.bank||'Bank'} •••• ${m.last4||'----'}`;
    const tr=document.createElement('tr');
    tr.innerHTML = `
      <td>${m.type[0].toUpperCase()+m.type.slice(1)}</td>
      <td><input class="pmLabel" data-id="${m.id}" value="${m.label||''}" style="width:220px"/></td>
      <td>${details}</td>
      <td>${isDef?'✅':'—'}</td>
      <td>
        ${isDef?'':`<button type="button" class="btn secondary setDef" data-id="${m.id}">Set Default</button>`}
        <button type="button" class="btn danger del" data-id="${m.id}">Remove</button>
      </td>`;
    tb.appendChild(tr);
  });

  tb.querySelectorAll('.setDef').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      await fetch('?ajax=setDefault&customer_id=1', {method:'POST', body:new URLSearchParams({id:btn.dataset.id})});
      await loadMethods(); await loadSummary(); toast('Default method set');
    });
  });

  tb.querySelectorAll('.del').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      if(!confirm('Remove this payment method?')) return;
      await fetch('?ajax=delMethod&customer_id=1', {method:'POST', body:new URLSearchParams({id:btn.dataset.id})});
      await loadMethods(); await loadSummary(); toast('Method removed');
    });
  });

  tb.querySelectorAll('.pmLabel').forEach(inp=>{
    inp.addEventListener('change', async ()=>{
      await fetch('?ajax=renameMethod&customer_id=1', {method:'POST', body:new URLSearchParams({id:inp.dataset.id,label:inp.value})});
      toast('Label updated');
    });
  });

  // pay dropdown
  const sel=document.getElementById('pay_method'); sel.innerHTML='';
  list.forEach(m=>{
    const opt=document.createElement('option'); opt.value=m.id;
    opt.textContent=(m.label||m.type)+' — '+(m.type==='card'?(m.brand+' •••• '+(m.last4||'----')):m.type==='wallet'?(m.provider+' •••• '+(m.last4||'----')):((m.bank||'Bank')+' •••• '+(m.last4||'----')));
    sel.appendChild(opt);
  });
  const def = list.find(x=>Number(x.is_default)===1); if(def) sel.value = def.id;

  refreshPayButtonState();
}

// helpers
function last4(s){ return String(s||'').replace(/\D/g,'').slice(-4).padStart(4,'0'); }
function brandOf(n){ return /^4/.test(n)?'Visa' : /^5[1-5]/.test(n)?'Mastercard' : 'Card'; }
function refreshPayButtonState(){
  const amt = parseFloat((document.getElementById('pay_amount').value||'').replace(/,/g,''))||0;
  const hasMethod = !!document.getElementById('pay_method').options.length;
  document.getElementById('btnPay').disabled = !(amt>0 && hasMethod);
}

// Method forms
(function setupMethodForms(){
  const seg=document.getElementById('paySeg');
  const forms={ card:document.getElementById('form_card'), wallet:document.getElementById('form_wallet'), bank:document.getElementById('form_bank') };
  seg.addEventListener('click',(e)=>{
    if(e.target.tagName!=='BUTTON') return;
    seg.querySelectorAll('button').forEach(b=>b.classList.remove('active'));
    e.target.classList.add('active'); const k=e.target.dataset.kind;
    Object.keys(forms).forEach(x=> forms[x].style.display = (x===k?'block':'none'));
  });

  const c_number=document.getElementById('c_number'), c_exp=document.getElementById('c_exp'), c_label=document.getElementById('c_label'), c_cvc=document.getElementById('c_cvc');
  const w_provider=document.getElementById('w_provider'), w_number=document.getElementById('w_number');
  const b_name=document.getElementById('b_name'), b_ac=document.getElementById('b_ac'), b_branch=document.getElementById('b_branch'), b_swift=document.getElementById('b_swift');

  c_exp && c_exp.addEventListener('input',()=> c_exp.value = c_exp.value.replace(/\s+/g,'').replace(/^(\d{2})(\d{0,2}).*/, (m,a,b)=> b? a+'/'+b : a));
  c_number && c_number.addEventListener('input',()=>{ let v=c_number.value.replace(/\D/g,'').slice(0,16); c_number.value=v.replace(/(\d{4})(?=\d)/g,'$1 ').trim(); });

  document.getElementById('addCard').addEventListener('click', async ()=>{
    const num=(c_number.value||'').replace(/\s+/g,''); const exp=(c_exp.value||'').trim(); const label=(c_label.value||'').trim();
    if(num.length<12 || !exp){ alert('Enter valid card number & expiry'); return; }
    const fd=new URLSearchParams({type:'card',label,brand:brandOf(num),last4:last4(num),exp});
    await fetch('?ajax=addMethod&customer_id=1',{method:'POST',body:fd});
    c_number.value=c_exp.value=c_cvc.value=c_label.value=''; await loadMethods(); await loadSummary(); toast('Card added');
  });

  document.getElementById('addWallet').addEventListener('click', async ()=>{
    const prov=w_provider.value; const num=w_number.value; if(!num){ alert('Enter wallet number'); return; }
    const fd=new URLSearchParams({type:'wallet',label:prov,provider:prov,last4:last4(num)});
    await fetch('?ajax=addMethod&customer_id=1',{method:'POST',body:fd});
    w_number.value=''; await loadMethods(); await loadSummary(); toast('Wallet linked');
  });

  document.getElementById('addBank').addEventListener('click', async ()=>{
    const nm=(b_name.value||'').trim(); const ac=(b_ac.value||'').trim(); const bank=(b_branch.value||'').trim();
    if(!nm || !ac){ alert('Enter account name & number'); return; }
    const fd=new URLSearchParams({type:'bank',label:(nm+(bank?' — '+bank:'')),bank:(bank||'Bank'),last4:last4(ac)});
    await fetch('?ajax=addMethod&customer_id=1',{method:'POST',body:fd});
    b_name.value=b_ac.value=b_branch.value=b_swift.value=''; await loadMethods(); await loadSummary(); toast('Bank added');
  });
})();

// Dues
async function loadDues(){
  const r = await fetch('?ajax=dues&customer_id=1'); const list = await r.json();
  const s = await (await fetch('?ajax=summary&customer_id=1')).json();
  const cur = s.prefs?.currency || 'BDT';

  document.getElementById('due_total').textContent = money(s.total_due_bdt, cur);

  const tb=document.querySelector('#dueTable tbody'); tb.innerHTML='';
  document.getElementById('dueEmpty').style.display = list.length ? 'none' : 'block';

  list.forEach(it=>{
    const tr=document.createElement('tr');
    tr.innerHTML = `
      <td>${it.invoice_date || ''}</td>
      <td>#${it.invoice_no}</td>
      <td>${money(it.due_bdt, cur)}</td>
      <td><span class="tag yellow">Open</span></td>
      <td><button type="button" class="btn secondary payItem" data-id="${it.invoice_id}" data-amt="${it.due_bdt}">Pay Item</button></td>`;
    tb.appendChild(tr);
  });

  tb.querySelectorAll('.payItem').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      document.getElementById('pay_amount').value = Number(btn.dataset.amt).toFixed(2);
      refreshPayButtonState();
      toast('Amount set from invoice '+btn.dataset.id);
    });
  });
}

// Quick actions
document.getElementById('quickPay').addEventListener('click', async ()=>{
  document.querySelector('.tab-btn[data-tab="dues"]').click();
  const s = await (await fetch('?ajax=summary&customer_id=1')).json();
  if((s.total_due_bdt||0)<=0){ toast('No outstanding due'); return; }
  document.getElementById('pay_amount').value = Number(s.total_due_bdt).toFixed(2);
  refreshPayButtonState();
});
document.getElementById('payFull').addEventListener('click', async ()=>{
  const s = await (await fetch('?ajax=summary&customer_id=1')).json();
  if((s.total_due_bdt||0)<=0){ toast('No outstanding due'); return; }
  document.getElementById('pay_amount').value = Number(s.total_due_bdt).toFixed(2);
  refreshPayButtonState();
});
document.getElementById('payPartialBtn').addEventListener('click', async ()=>{
  const s = await (await fetch('?ajax=summary&customer_id=1')).json();
  const half=Math.max(0,(s.total_due_bdt||0)/2); document.getElementById('pay_amount').value=half.toFixed(2); toast('Partial amount filled');
  refreshPayButtonState();
});

// Enable/disable pay button on amount change
document.getElementById('pay_amount').addEventListener('input', refreshPayButtonState);
document.getElementById('pay_method').addEventListener('change', refreshPayButtonState);

// Pay now
document.getElementById('btnPay').addEventListener('click', async ()=>{
  const amount=parseFloat((document.getElementById('pay_amount').value||'0').replace(/,/g,'')); const method_id=document.getElementById('pay_method').value;
  if(!(amount>0)){ alert('Enter a valid amount'); return; }
  const fd=new URLSearchParams({amount,method_id}); const r=await fetch('?ajax=pay&customer_id=1',{method:'POST',body:fd});
  const j=await r.json(); if(j.error){ alert(j.error); return; }
  await loadSummary(); await loadDues(); await loadTx(); document.getElementById('pay_amount').value=''; refreshPayButtonState(); toast('Payment successful');
});

// Activity
function rcptHTML(entry,email){ return `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Receipt</title>
<style>body{font-family:Inter,Segoe UI,sans-serif;padding:24px;color:#0f172a}.box{border:1px solid #e5e7eb;border-radius:12px;padding:16px;max-width:720px}
h1{margin:0 0 8px;font-size:22px;color:#2563eb}.row{display:flex;gap:20px;flex-wrap:wrap}.row>div{flex:1 1 240px}.muted{color:#64748b}
table{width:100%;border-collapse:collapse;margin-top:12px}th,td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}th{background:#f8fafc}</style></head><body>
<div class="box"><h1>Payment Receipt</h1><div class="row">
<div><div class="muted">Date</div><div>${entry.t}</div></div>
<div><div class="muted">Amount</div><div>${entry.amt}</div></div>
<div><div class="muted">Method</div><div>${entry.method||'—'}</div></div>
<div><div class="muted">Email</div><div>${email||'—'}</div></div>
</div><table><thead><tr><th>Description</th><th>Status</th></tr></thead><tbody><tr><td>${entry.act}</td><td>${entry.status}</td></tr></tbody></table></div></body></html>`; }

async function loadTx(filter={}){
  const r = await fetch('?ajax=tx&customer_id=1'); let list = await r.json();
  const tb=document.querySelector('#txTable tbody'); tb.innerHTML='';
  if(filter.type) list=list.filter(x=>(x.act||'').startsWith(filter.type));
  if(filter.date) list=list.filter(x=>(x.t||'').includes(filter.date));

  if (!list.length){
    const tr=document.createElement('tr'); tr.innerHTML = `<td colspan="5"><div class="empty">No transactions yet.</div></td>`;
    tb.appendChild(tr);
  }

  list.forEach((row,i)=>{
    const tr=document.createElement('tr');
    tr.innerHTML = `<td>${row.t}</td><td>${row.act}</td><td>${row.amt}</td><td><span class="tag green">ok</span></td><td><button type="button" class="btn secondary rcpt" data-idx="${i}">Download</button></td>`;
    tb.appendChild(tr);
  });
  const prefs = await (await fetch('?ajax=summary&customer_id=1')).json();
  tb.querySelectorAll('.rcpt').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const entry=list[+btn.dataset.idx];
      const blob=new Blob([rcptHTML(entry,prefs?.prefs?.email)],{type:'text/html'});
      const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`receipt_${Date.now()}.html`; a.click();
    });
  });
  document.getElementById('sum_receipts').textContent = list.length;
}

// Export CSV
document.getElementById('exportTx').addEventListener('click', async ()=>{
  const r = await fetch('?ajax=tx&customer_id=1'); const rows = await r.json();
  const csv=['time,action,amount,status,method'].concat(rows.map(x=>`"${x.t}","${(x.act||'').replace(/"/g,'""')}","${x.amt}","ok","${(x.method||'').replace(/"/g,'""')}"`)).join('\n');
  const blob=new Blob([csv],{type:'text/csv'}); const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download='payment_activity.csv'; a.click();
});

// Seeder button — always for CID=1
document.getElementById('seedDemoBtn').addEventListener('click', async ()=>{
  try{
    const url = window.location.pathname + '?ajax=seedDemo&customer_id=1';
    const r   = await fetch(url);
    const j   = await r.json();
    if(j.error){ alert('Seeder error: '+j.error); return; }
    await loadSummary(); await loadDues(); await loadTx({});
    toast('Demo dues added');
  }catch(e){
    console.error(e);
    alert('Failed to seed demo dues. Check PHP error log.');
  }
});

// NEW: Quick add trip button (simple prompts -> addTrip endpoint)
document.getElementById('addTripQuick').addEventListener('click', async ()=>{
  const route  = prompt('Route (e.g., Dhaka ➜ Chittagong):','Dhaka ➜ Chittagong') || 'Trip';
  const haul   = parseFloat(prompt('Line haul amount (BDT):','10000')||'0')||0;
  const fuel   = parseFloat(prompt('Fuel charge (BDT):','2000')||'0')||0;
  const driver = parseFloat(prompt('Driver charge (BDT):','800')||'0')||0;
  const labour = parseFloat(prompt('Labour charge (BDT):','500')||'0')||0;
  const toll   = parseFloat(prompt('Toll & parking (BDT):','300')||'0')||0;
  const misc   = parseFloat(prompt('Misc charge (BDT):','0')||'0')||0;

  const fd = new URLSearchParams({route, haul, fuel, driver, labour, toll, misc});
  const r  = await fetch('?ajax=addTrip&customer_id=1',{method:'POST',body:fd});
  const j  = await r.json();
  if(j.error){ alert(j.error); return; }
  await loadSummary(); await loadDues();
  toast('Trip added as invoice #' + j.invoice_no);
});

// Prefs save
document.getElementById('savePrefs').addEventListener('click', async ()=>{
  const fd=new URLSearchParams({currency:document.getElementById('p_currency').value, auto:document.getElementById('p_auto').value, email:document.getElementById('p_email').value});
  await fetch('?ajax=savePrefs&customer_id=1',{method:'POST',body:fd});
  await loadSummary(); toast('Preferences saved');
});

// Filters
document.getElementById('applyTxFilter').addEventListener('click',()=> loadTx({type:document.getElementById('f_type').value, date:document.getElementById('f_date').value}));
document.getElementById('clearTxFilter').addEventListener('click',()=>{ document.getElementById('f_type').value=''; document.getElementById('f_date').value=''; loadTx({}) });

// Last receipt quick button
document.getElementById('printLast').addEventListener('click', async ()=>{
  const r = await fetch('?ajax=tx&customer_id=1'); const rows = await r.json();
  const last = rows.find(x => (x.act||'').startsWith('invoice.pay'));
  if(!last){ toast('No payment receipt found'); return; }
  const prefs = await (await fetch('?ajax=summary&customer_id=1')).json();
  const blob=new Blob([rcptHTML(last,prefs?.prefs?.email)],{type:'text/html'});
  const a=document.createElement('a'); a.href=URL.createObjectURL(blob); a.download=`receipt_${Date.now()}.html`; a.click();
});

// Init
(async function init(){ await loadSummary(); await loadMethods(); await loadDues(); await loadTx({}); })();
</script>
</body>
</html>
