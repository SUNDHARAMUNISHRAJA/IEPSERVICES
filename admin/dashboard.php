<?php
session_start();
require_once '../includes/config.php';

if (empty($_SESSION['iep_admin'])) {
    header('Location: login.php');
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = intval($_POST['id'] ?? 0);

    if ($action === 'approve_feedback' && $id) {
        $db->prepare("UPDATE feedback SET is_approved = 1 WHERE id = ?")->execute([$id]);
    } elseif ($action === 'reject_feedback' && $id) {
        $db->prepare("DELETE FROM feedback WHERE id = ?")->execute([$id]);
    } elseif ($action === 'update_enquiry_status' && $id) {
        $status = $_POST['status'] ?? 'new';
        $db->prepare("UPDATE enquiries SET status = ? WHERE id = ?")->execute([$status, $id]);
    } elseif ($action === 'delete_enquiry' && $id) {
        $db->prepare("DELETE FROM enquiries WHERE id = ?")->execute([$id]);
    }
    header('Location: dashboard.php');
    exit;
}

$enquiries    = $db->query("SELECT * FROM enquiries ORDER BY created_at DESC")->fetchAll();
$feedbackAll  = $db->query("SELECT * FROM feedback ORDER BY created_at DESC")->fetchAll();

$totalEnquiries  = count($enquiries);
$newEnquiries    = count(array_filter($enquiries, fn($e) => $e['status'] === 'new'));
$totalFeedback   = count($feedbackAll);
$pendingFeedback = count(array_filter($feedbackAll, fn($f) => !$f['is_approved']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IEP Admin — Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root { --navy:#0a1f5c; --blue:#1a56db; --blue-light:#e8f0ff; --green:#16a34a; --border:#e5e7eb; --bg:#f8fafc; }
    *, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family:'Manrope',sans-serif; background:var(--bg); color:#111827; }
    a { text-decoration:none; color:inherit; }
    .layout { display:flex; min-height:100vh; }

    /* Sidebar */
    .sidebar { width:240px; background:var(--navy); color:#fff; flex-shrink:0; display:flex; flex-direction:column; }
    .sidebar-logo { padding:24px 20px; border-bottom:1px solid rgba(255,255,255,.1); display:flex; align-items:center; gap:10px; }
    .sidebar-logo .icon { width:38px; height:38px; background:var(--blue); border-radius:10px; display:grid; place-items:center; }
    .sidebar-logo span { font-size:0.82rem; font-weight:700; color:rgba(255,255,255,.9); line-height:1.3; }
    .sidebar-nav { padding:16px 0; flex:1; }
    .nav-item { display:flex; align-items:center; gap:10px; padding:11px 20px; font-size:0.875rem; font-weight:600; color:rgba(255,255,255,.65); cursor:pointer; transition:all .2s; border-left:3px solid transparent; }
    .nav-item:hover, .nav-item.active { color:#fff; background:rgba(255,255,255,.07); border-left-color:var(--blue); }
    .nav-item i { width:18px; text-align:center; }
    .sidebar-footer { padding:16px 20px; border-top:1px solid rgba(255,255,255,.1); display:flex; flex-direction:column; gap:10px; }
    .sidebar-footer a { display:flex; align-items:center; gap:8px; font-size:0.8rem; color:rgba(255,255,255,.5); }
    .sidebar-footer a:hover { color:#fff; }

    /* Main */
    .main { flex:1; display:flex; flex-direction:column; overflow:hidden; }
    .topbar { background:#fff; border-bottom:1px solid var(--border); padding:14px 28px; display:flex; align-items:center; justify-content:space-between; }
    .topbar h1 { font-size:1.1rem; font-weight:700; color:var(--navy); }
    .topbar-right { display:flex; align-items:center; gap:10px; font-size:0.85rem; color:#6b7280; }
    .content { flex:1; overflow-y:auto; padding:28px; }

    /* Badges */
    .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:0.72rem; font-weight:700; }
    .badge-new { background:#fff3cd; color:#856404; }
    .badge-pending { background:#fff3cd; color:#856404; }
    .badge-resolved { background:#dbeafe; color:#1e40af; }
    .badge-in_progress { background:#fce7f3; color:#9d174d; }
    .badge-closed { background:#f3f4f6; color:#374151; }

    /* Stats */
    .stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
    .stat-card { background:#fff; border-radius:12px; padding:20px; border:1px solid var(--border); display:flex; align-items:center; gap:14px; }
    .stat-card .icon { width:48px; height:48px; border-radius:12px; display:grid; place-items:center; font-size:1.2rem; flex-shrink:0; }
    .stat-card h3 { font-size:1.6rem; font-weight:800; color:var(--navy); line-height:1; }
    .stat-card p { font-size:0.78rem; color:#6b7280; margin-top:2px; }

    /* Table */
    .table-wrap { background:#fff; border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .table-header { padding:16px 20px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); }
    .table-header h3 { font-size:0.95rem; font-weight:700; color:var(--navy); }
    table { width:100%; border-collapse:collapse; }
    th { padding:11px 16px; text-align:left; font-size:0.75rem; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; background:#f9fafb; border-bottom:1px solid var(--border); white-space:nowrap; }
    td { padding:12px 16px; font-size:0.875rem; color:#374151; border-bottom:1px solid var(--border); vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#f9fafb; }

    /* Requirement single line */
    .req-preview { max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; color:#6b7280; font-size:0.82rem; }

    /* Buttons */
    .action-btn { display:inline-flex; align-items:center; gap:5px; padding:5px 10px; border-radius:6px; font-size:0.78rem; font-weight:700; cursor:pointer; transition:all .2s; border:none; font-family:'Manrope',sans-serif; }
    .btn-view { background:#e8f0ff; color:#1a56db; }
    .btn-view:hover { background:#1a56db; color:#fff; }
    .btn-approve { background:#d1fae5; color:#065f46; }
    .btn-approve:hover { background:#16a34a; color:#fff; }
    .btn-delete { background:#fee2e2; color:#991b1b; }
    .btn-delete:hover { background:#dc2626; color:#fff; }
    .action-group { display:flex; gap:6px; flex-wrap:nowrap; }

    .search-bar { padding:8px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:0.875rem; font-family:'Manrope',sans-serif; width:220px; }
    .search-bar:focus { outline:none; border-color:var(--blue); }
    .stars-sm { color:#f59e0b; font-size:0.85rem; }
    .empty { text-align:center; padding:48px; color:#9ca3af; }
    .empty i { font-size:2.5rem; margin-bottom:12px; display:block; }
    .section { display:none; }
    .section.active { display:block; }
    select.status-select { border:1px solid #e5e7eb; border-radius:6px; padding:4px 8px; font-size:0.78rem; font-family:'Manrope',sans-serif; cursor:pointer; }

    /* ── MODAL ── */
    .modal-overlay {
      position:fixed; inset:0; background:rgba(0,0,0,.5);
      display:none; place-items:center; z-index:9999;
      backdrop-filter:blur(3px);
    }
    .modal-overlay.open { display:grid; }
    .modal-card {
      background:#fff; border-radius:16px; width:100%; max-width:520px;
      box-shadow:0 24px 60px rgba(0,0,0,.25); overflow:hidden;
      animation:slideUp .25s ease;
    }
    @keyframes slideUp { from { transform:translateY(30px); opacity:0; } to { transform:translateY(0); opacity:1; } }
    .modal-header {
      background:var(--navy); color:#fff; padding:20px 24px;
      display:flex; align-items:center; justify-content:space-between;
    }
    .modal-header h3 { font-size:1rem; font-weight:700; }
    .modal-header .modal-badge { font-size:0.75rem; padding:3px 10px; border-radius:20px; background:rgba(255,255,255,.15); }
    .modal-close { background:none; border:none; color:#fff; font-size:1.2rem; cursor:pointer; opacity:.7; }
    .modal-close:hover { opacity:1; }
    .modal-body { padding:24px; display:flex; flex-direction:column; gap:16px; }
    .modal-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .modal-field label { display:block; font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; margin-bottom:4px; }
    .modal-field p { font-size:0.9rem; font-weight:600; color:#111827; }
    .modal-field.full { grid-column:1/-1; }
    .modal-field .req-text { background:#f8fafc; border:1px solid var(--border); border-radius:8px; padding:12px; font-size:0.875rem; color:#374151; line-height:1.6; white-space:pre-wrap; }
    .modal-footer { padding:16px 24px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:10px; }
    .btn-print { background:var(--blue); color:#fff; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; font-family:'Manrope',sans-serif; display:flex; align-items:center; gap:6px; }
    .btn-print:hover { background:#1344b8; }
    .btn-close-modal { background:#f3f4f6; color:#374151; padding:8px 18px; border-radius:8px; font-size:0.85rem; font-weight:700; border:none; cursor:pointer; font-family:'Manrope',sans-serif; }
      .pw-field { margin-bottom:18px; }
    .pw-field label { display:block; font-size:0.8rem; font-weight:700; color:#6b7280; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
    .pw-input-wrap { position:relative; }
    .pw-input-wrap input { width:100%; padding:11px 40px 11px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:0.9rem; font-family:'Manrope',sans-serif; background:#f8fafc; }
    .pw-input-wrap input:focus { outline:none; border-color:var(--blue); background:#fff; box-shadow:0 0 0 3px rgba(26,86,219,.1); }
    .toggle-pw { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#9ca3af; cursor:pointer; font-size:0.85rem; }
    .toggle-pw:hover { color:var(--blue); }
    .btn-save-pw { width:100%; background:var(--blue); color:#fff; padding:12px; border-radius:8px; font-size:0.95rem; font-weight:700; border:none; cursor:pointer; font-family:'Manrope',sans-serif; display:flex; align-items:center; justify-content:center; gap:8px; margin-top:8px; transition:all .2s; }
    .btn-save-pw:hover { background:#1344b8; }
  </style>
</head>
<body>

<!-- ── VIEW MODAL ── -->
<div class="modal-overlay" id="viewModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3><i class="fa-solid fa-envelope-open-text"></i> Enquiry Details</h3>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="modal-badge" id="modalStatus">New</span>
        <button class="modal-close" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
      </div>
    </div>
    <div class="modal-body">
      <div class="modal-row">
        <div class="modal-field">
          <label>Full Name</label>
          <p id="mName">—</p>
        </div>
        <div class="modal-field">
          <label>Phone</label>
          <p id="mPhone">—</p>
        </div>
      </div>
      <div class="modal-row">
        <div class="modal-field">
          <label>Email</label>
          <p id="mEmail">—</p>
        </div>
        <div class="modal-field">
          <label>Service Requested</label>
          <p id="mService">—</p>
        </div>
      </div>
      <div class="modal-row">
        <div class="modal-field">
          <label>Date Submitted</label>
          <p id="mDate">—</p>
        </div>
        <div class="modal-field">
          <label>Enquiry ID</label>
          <p id="mId">—</p>
        </div>
      </div>
      <div class="modal-field full">
        <label>Requirement / Message</label>
        <div class="req-text" id="mRequirement">—</div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn-close-modal" onclick="closeModal()">Close</button>
      <button class="btn-print" onclick="printEnquiry()"><i class="fa-solid fa-print"></i> Print</button>
    </div>
  </div>
</div>

<div class="layout">
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="icon"><i class="fa-solid fa-gear" style="color:#fff;font-size:1rem"></i></div>
      <span>INTEGRATED<br>ENGINEERS POINT</span>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-item active" onclick="showSection('dashboard', this)"><i class="fa-solid fa-gauge-high"></i> Dashboard</div>
      <div class="nav-item" onclick="showSection('enquiries', this)">
        <i class="fa-solid fa-envelope-open-text"></i> Enquiries
        <span class="badge badge-new" style="margin-left:auto"><?= $newEnquiries ?></span>
      </div>
      <div class="nav-item" onclick="showSection('feedback', this)">
        <i class="fa-solid fa-star"></i> Feedback
        <span class="badge badge-pending" style="margin-left:auto"><?= $pendingFeedback ?></span>
      </div>
      <div class="nav-item" onclick="showSection('approved', this)"><i class="fa-solid fa-check-circle"></i> Published Reviews</div>
      <div class="nav-item" onclick="showSection('password', this)"><i class="fa-solid fa-key"></i> Change Password</div>
      <div class="nav-item" onclick="showSection('password', this)"><i class="fa-solid fa-key"></i> Change Password</div>
    </nav>
    <div class="sidebar-footer">
      <a href="../index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
      <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <h1 id="pageTitle">Dashboard</h1>
      <div class="topbar-right">
        <i class="fa-solid fa-user-circle" style="font-size:1.2rem;color:#1a56db"></i>
        <?= htmlspecialchars($_SESSION['iep_admin_name'] ?? 'Admin') ?>
      </div>
    </div>

    <div class="content">

      <!-- DASHBOARD -->
      <div class="section active" id="sec-dashboard">
        <div class="stats-row">
          <div class="stat-card">
            <div class="icon" style="background:#dbeafe"><i class="fa-solid fa-envelope-open-text" style="color:#1a56db"></i></div>
            <div><h3><?= $totalEnquiries ?></h3><p>Total Enquiries</p></div>
          </div>
          <div class="stat-card">
            <div class="icon" style="background:#fef9c3"><i class="fa-solid fa-bell" style="color:#ca8a04"></i></div>
            <div><h3><?= $newEnquiries ?></h3><p>New Enquiries</p></div>
          </div>
          <div class="stat-card">
            <div class="icon" style="background:#d1fae5"><i class="fa-solid fa-star" style="color:#16a34a"></i></div>
            <div><h3><?= $totalFeedback ?></h3><p>Total Feedback</p></div>
          </div>
          <div class="stat-card">
            <div class="icon" style="background:#fee2e2"><i class="fa-solid fa-clock" style="color:#dc2626"></i></div>
            <div><h3><?= $pendingFeedback ?></h3><p>Pending Reviews</p></div>
          </div>
        </div>
        <div class="table-wrap">
          <div class="table-header"><h3>Recent Enquiries</h3></div>
          <table>
            <thead><tr><th>Name</th><th>Phone</th><th>Service</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($enquiries, 0, 5) as $e): ?>
              <tr>
                <td><?= htmlspecialchars($e['full_name']) ?></td>
                <td><?= htmlspecialchars($e['phone']) ?></td>
                <td><?= htmlspecialchars($e['service']) ?></td>
                <td><span class="badge badge-<?= $e['status'] ?>"><?= ucfirst(str_replace('_',' ',$e['status'])) ?></span></td>
                <td><?= date('d M Y', strtotime($e['created_at'])) ?></td>
                <td>
                  <button class="action-btn btn-view" onclick="viewEnquiry(
                    '<?= $e['id'] ?>',
                    '<?= addslashes(htmlspecialchars($e['full_name'])) ?>',
                    '<?= addslashes(htmlspecialchars($e['phone'])) ?>',
                    '<?= addslashes(htmlspecialchars($e['email'] ?? '—')) ?>',
                    '<?= addslashes(htmlspecialchars($e['service'])) ?>',
                    '<?= addslashes(htmlspecialchars($e['requirement'])) ?>',
                    '<?= date('d M Y, h:i A', strtotime($e['created_at'])) ?>',
                    '<?= ucfirst(str_replace('_',' ',$e['status'])) ?>'
                  )"><i class="fa-solid fa-eye"></i> View</button>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$enquiries): ?>
              <tr><td colspan="6"><div class="empty"><i class="fa-solid fa-inbox"></i>No enquiries yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ALL ENQUIRIES -->
      <div class="section" id="sec-enquiries">
        <div class="table-wrap">
          <div class="table-header">
            <h3>All Enquiries</h3>
            <input type="text" class="search-bar" placeholder="Search enquiries..." oninput="filterTable(this,'enquiry-tbody')">
          </div>
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Service</th><th>Requirement</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="enquiry-tbody">
            <?php foreach ($enquiries as $i => $e): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td style="white-space:nowrap"><?= htmlspecialchars($e['full_name']) ?></td>
                <td style="white-space:nowrap"><a href="tel:<?= htmlspecialchars($e['phone']) ?>"><?= htmlspecialchars($e['phone']) ?></a></td>
                <td><?= $e['email'] ? htmlspecialchars($e['email']) : '—' ?></td>
                <td style="white-space:nowrap"><?= htmlspecialchars($e['service']) ?></td>
                <td><div class="req-preview"><?= htmlspecialchars($e['requirement']) ?></div></td>
                <td>
                  <form method="POST">
                    <input type="hidden" name="action" value="update_enquiry_status">
                    <input type="hidden" name="id" value="<?= $e['id'] ?>">
                    <select name="status" class="status-select" onchange="this.form.submit()">
                      <?php foreach(['new','in_progress','resolved','closed'] as $s): ?>
                        <option value="<?= $s ?>" <?= $e['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
                      <?php endforeach; ?>
                    </select>
                  </form>
                </td>
                <td style="white-space:nowrap"><?= date('d M Y', strtotime($e['created_at'])) ?></td>
                <td>
                  <div class="action-group">
                    <button class="action-btn btn-view" onclick="viewEnquiry(
                      '<?= $e['id'] ?>',
                      '<?= addslashes(htmlspecialchars($e['full_name'])) ?>',
                      '<?= addslashes(htmlspecialchars($e['phone'])) ?>',
                      '<?= addslashes(htmlspecialchars($e['email'] ?? '—')) ?>',
                      '<?= addslashes(htmlspecialchars($e['service'])) ?>',
                      '<?= addslashes(htmlspecialchars($e['requirement'])) ?>',
                      '<?= date('d M Y, h:i A', strtotime($e['created_at'])) ?>',
                      '<?= ucfirst(str_replace('_',' ',$e['status'])) ?>'
                    )"><i class="fa-solid fa-eye"></i> View</button>
                    <form method="POST" onsubmit="return confirm('Delete this enquiry?')">
                      <input type="hidden" name="action" value="delete_enquiry">
                      <input type="hidden" name="id" value="<?= $e['id'] ?>">
                      <button type="submit" class="action-btn btn-delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$enquiries): ?>
              <tr><td colspan="9"><div class="empty"><i class="fa-solid fa-inbox"></i>No enquiries received yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PENDING FEEDBACK -->
      <div class="section" id="sec-feedback">
        <div class="table-wrap">
          <div class="table-header"><h3>Pending Feedback (Awaiting Approval)</h3></div>
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Service</th><th>Rating</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
            <?php $pending = array_filter($feedbackAll, fn($f) => !$f['is_approved']); $i=0;
            foreach ($pending as $f): $i++; ?>
              <tr>
                <td><?= $i ?></td>
                <td><?= htmlspecialchars($f['customer_name']) ?></td>
                <td><?= $f['service_used'] ? htmlspecialchars($f['service_used']) : '—' ?></td>
                <td class="stars-sm"><?= str_repeat('★',$f['rating']).str_repeat('☆',5-$f['rating']) ?></td>
                <td><div class="req-preview"><?= htmlspecialchars($f['message']) ?></div></td>
                <td style="white-space:nowrap"><?= date('d M Y', strtotime($f['created_at'])) ?></td>
                <td>
                  <div class="action-group">
                    <form method="POST">
                      <input type="hidden" name="action" value="approve_feedback">
                      <input type="hidden" name="id" value="<?= $f['id'] ?>">
                      <button type="submit" class="action-btn btn-approve"><i class="fa-solid fa-check"></i> Approve</button>
                    </form>
                    <form method="POST" onsubmit="return confirm('Delete this feedback?')">
                      <input type="hidden" name="action" value="reject_feedback">
                      <input type="hidden" name="id" value="<?= $f['id'] ?>">
                      <button type="submit" class="action-btn btn-delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$pending): ?>
              <tr><td colspan="7"><div class="empty"><i class="fa-solid fa-check-circle"></i>No pending feedback.</div></td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- PUBLISHED REVIEWS -->
      <div class="section" id="sec-approved">
        <div class="table-wrap">
          <div class="table-header"><h3>Published Reviews</h3></div>
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Service</th><th>Rating</th><th>Message</th><th>Date</th><th>Action</th></tr></thead>
            <tbody>
            <?php $approved = array_filter($feedbackAll, fn($f) => $f['is_approved']); $i=0;
            foreach ($approved as $f): $i++; ?>
              <tr>
                <td><?= $i ?></td>
                <td><?= htmlspecialchars($f['customer_name']) ?></td>
                <td><?= $f['service_used'] ? htmlspecialchars($f['service_used']) : '—' ?></td>
                <td class="stars-sm"><?= str_repeat('★',$f['rating']).str_repeat('☆',5-$f['rating']) ?></td>
                <td><div class="req-preview"><?= htmlspecialchars($f['message']) ?></div></td>
                <td style="white-space:nowrap"><?= date('d M Y', strtotime($f['created_at'])) ?></td>
                <td>
                  <form method="POST" onsubmit="return confirm('Remove this published review?')">
                    <input type="hidden" name="action" value="reject_feedback">
                    <input type="hidden" name="id" value="<?= $f['id'] ?>">
                    <button type="submit" class="action-btn btn-delete"><i class="fa-solid fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$approved): ?>
              <tr><td colspan="7"><div class="empty"><i class="fa-solid fa-star"></i>No published reviews yet.</div></td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>


      <!-- CHANGE PASSWORD -->
      <div class="section" id="sec-password">
        <div class="table-wrap" style="max-width:480px">
          <div class="table-header"><h3><i class="fa-solid fa-key"></i> Change Password</h3></div>
          <div style="padding:28px">
            <div id="pwMsg" style="display:none;margin-bottom:16px;padding:10px 14px;border-radius:8px;font-size:0.875rem;"></div>
            <form id="changePasswordForm">
              <div class="pw-field">
                <label>Current Password</label>
                <div class="pw-input-wrap">
                  <input type="password" id="currentPassword" placeholder="Enter current password" required>
                  <i class="fa-solid fa-eye toggle-pw" onclick="togglePw('currentPassword', this)"></i>
                </div>
              </div>
              <div class="pw-field">
                <label>New Password</label>
                <div class="pw-input-wrap">
                  <input type="password" id="newPassword" placeholder="Min 6 characters" required>
                  <i class="fa-solid fa-eye toggle-pw" onclick="togglePw('newPassword', this)"></i>
                </div>
              </div>
              <div class="pw-field">
                <label>Confirm New Password</label>
                <div class="pw-input-wrap">
                  <input type="password" id="confirmPassword" placeholder="Re-enter new password" required>
                  <i class="fa-solid fa-eye toggle-pw" onclick="togglePw('confirmPassword', this)"></i>
                </div>
              </div>
              <button type="submit" class="btn-save-pw">
                <span class="btn-text"><i class="fa-solid fa-floppy-disk"></i> Update Password</span>
                <span class="btn-loader" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i> Updating...</span>
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
// ── Section nav ──
const titles = { dashboard:'Dashboard', enquiries:'Enquiries', feedback:'Pending Feedback', approved:'Published Reviews' };
function showSection(name, el) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById('sec-' + name).classList.add('active');
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('pageTitle').textContent = titles[name] || 'Dashboard';
}

// ── Search filter ──
function filterTable(input, tbodyId) {
  const q = input.value.toLowerCase();
  document.querySelectorAll('#' + tbodyId + ' tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

// ── View Modal ──
function viewEnquiry(id, name, phone, email, service, requirement, date, status) {
  document.getElementById('mId').textContent          = '#' + id;
  document.getElementById('mName').textContent        = name;
  document.getElementById('mPhone').textContent       = phone;
  document.getElementById('mEmail').textContent       = email || '—';
  document.getElementById('mService').textContent     = service;
  document.getElementById('mRequirement').textContent = requirement;
  document.getElementById('mDate').textContent        = date;
  document.getElementById('modalStatus').textContent  = status;
  document.getElementById('viewModal').classList.add('open');
}

function closeModal() {
  document.getElementById('viewModal').classList.remove('open');
}

// Close modal on overlay click
document.getElementById('viewModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeModal();
});

// Print enquiry
function printEnquiry() {
  const name    = document.getElementById('mName').textContent;
  const phone   = document.getElementById('mPhone').textContent;
  const email   = document.getElementById('mEmail').textContent;
  const service = document.getElementById('mService').textContent;
  const req     = document.getElementById('mRequirement').textContent;
  const date    = document.getElementById('mDate').textContent;
  const id      = document.getElementById('mId').textContent;
  const status  = document.getElementById('modalStatus').textContent;

  const win = window.open('', '_blank');
  win.document.write(`
    <html><head><title>Enquiry ${id}</title>
    <style>
      body { font-family: Arial, sans-serif; padding: 40px; color: #111; }
      h2 { color: #0a1f5c; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
      table { width: 100%; border-collapse: collapse; margin-top: 20px; }
      td { padding: 10px 14px; border: 1px solid #e5e7eb; font-size: 14px; }
      td:first-child { font-weight: bold; background: #f8fafc; width: 160px; }
      .req { white-space: pre-wrap; }
      .footer { margin-top: 30px; font-size: 12px; color: #9ca3af; }
        .pw-field { margin-bottom:18px; }
    .pw-field label { display:block; font-size:0.8rem; font-weight:700; color:#6b7280; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
    .pw-input-wrap { position:relative; }
    .pw-input-wrap input { width:100%; padding:11px 40px 11px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:0.9rem; font-family:'Manrope',sans-serif; background:#f8fafc; }
    .pw-input-wrap input:focus { outline:none; border-color:var(--blue); background:#fff; box-shadow:0 0 0 3px rgba(26,86,219,.1); }
    .toggle-pw { position:absolute; right:12px; top:50%; transform:translateY(-50%); color:#9ca3af; cursor:pointer; font-size:0.85rem; }
    .toggle-pw:hover { color:var(--blue); }
    .btn-save-pw { width:100%; background:var(--blue); color:#fff; padding:12px; border-radius:8px; font-size:0.95rem; font-weight:700; border:none; cursor:pointer; font-family:'Manrope',sans-serif; display:flex; align-items:center; justify-content:center; gap:8px; margin-top:8px; transition:all .2s; }
    .btn-save-pw:hover { background:#1344b8; }
  </style></head><body>
    <h2>Integrated Engineers Point — Enquiry ${id}</h2>
    <table>
      <tr><td>Full Name</td><td>${name}</td></tr>
      <tr><td>Phone</td><td>${phone}</td></tr>
      <tr><td>Email</td><td>${email}</td></tr>
      <tr><td>Service</td><td>${service}</td></tr>
      <tr><td>Status</td><td>${status}</td></tr>
      <tr><td>Date</td><td>${date}</td></tr>
      <tr><td>Requirement</td><td class="req">${req}</td></tr>
    </table>
    <div class="footer">Printed from IEP Admin Panel &mdash; ${new Date().toLocaleString()}</div>
    </body></html>
  `);
  win.document.close();
  win.print();
}

// Change Password
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const current = document.getElementById('currentPassword').value;
  const newPw   = document.getElementById('newPassword').value;
  const confirm = document.getElementById('confirmPassword').value;
  const btn     = document.querySelector('.btn-save-pw');
  if (newPw.length < 6) { showPwMsg('New password must be at least 6 characters.', 'error'); return; }
  if (newPw !== confirm) { showPwMsg('Passwords do not match.', 'error'); return; }
  btn.querySelector('.btn-text').style.display = 'none';
  btn.querySelector('.btn-loader').style.display = 'inline-flex';
  btn.disabled = true;
  const fd = new FormData();
  fd.append('current_password', current);
  fd.append('new_password', newPw);
  fetch('change_password.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => { showPwMsg(data.message, data.success ? 'success' : 'error'); if(data.success) document.getElementById('changePasswordForm').reset(); })
    .catch(() => showPwMsg('Network error.', 'error'))
    .finally(() => { btn.querySelector('.btn-text').style.display='inline-flex'; btn.querySelector('.btn-loader').style.display='none'; btn.disabled=false; });
});
function showPwMsg(msg, type) {
  const el = document.getElementById('pwMsg');
  el.textContent = msg; el.style.display = 'block';
  el.style.background = type==='success' ? '#f0fdf4' : '#fff1f2';
  el.style.color      = type==='success' ? '#15803d' : '#be123c';
  el.style.border     = type==='success' ? '1px solid #bbf7d0' : '1px solid #fecdd3';
  setTimeout(() => { el.style.display='none'; }, 5000);
}
function togglePw(id, icon) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  icon.classList.toggle('fa-eye'); icon.classList.toggle('fa-eye-slash');
}

</script>
</body>
</html>
