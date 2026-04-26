<?php
/**
 * Samburu EWS: Admin Dashboard
 *
 * Password-protected page listing contact messages
 * with basic filtering by read status and stakeholder group.
 */
require __DIR__ . '/config.php';
require __DIR__ . '/includes/Auth.php';
require __DIR__ . '/includes/Csrf.php';
require __DIR__ . '/includes/Db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Handle login
$loginError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
        $loginError = 'Invalid session. Please reload and try again.';
    } else {
        $password = $_POST['password'] ?? '';
        if (Auth::login($password)) {
            Csrf::regenerate();
            header('Location: admin.php');
            exit;
        } else {
            $loginError = 'Incorrect password.';
        }
    }
}

// Handle mark-as-read
if (Auth::check() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    if (Csrf::verify($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['msg_id'] ?? 0);
        if ($id > 0) {
            try {
                Db::query("UPDATE contact_messages SET is_read = 1, read_at = NOW() WHERE id = :id", [':id' => $id]);
            } catch (Throwable) {}
        }
    }
    header('Location: admin.php?' . http_build_query(array_filter([
        'filter' => $_GET['filter'] ?? '',
        'group'  => $_GET['group']  ?? '',
    ])));
    exit;
}

// Handle delete
if (Auth::check() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_msg'])) {
    if (Csrf::verify($_POST['csrf_token'] ?? '')) {
        $id = (int)($_POST['msg_id'] ?? 0);
        if ($id > 0) {
            try {
                Db::query("DELETE FROM contact_messages WHERE id = :id", [':id' => $id]);
            } catch (Throwable) {}
        }
    }
    header('Location: admin.php?' . http_build_query(array_filter([
        'filter' => $_GET['filter'] ?? '',
        'group'  => $_GET['group']  ?? '',
    ])));
    exit;
}

// Handle Official Summaries update
// Updates the `official_summaries` table (KMD or NDMA row).
// KMD  row holds: outlook_category, summary_text, valid_period (forecast)
// NDMA row holds: drought_phase,    summary_text, valid_period (situation)
$summarySuccess = '';
$summaryError   = '';
if (Auth::check() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_summary'])) {
    if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
        $summaryError = 'Invalid session token. Please reload and try again.';
    } else {
        $org     = $_POST['summary_org'] ?? '';
        $summary = trim($_POST['summary_text'] ?? '');
        $period  = trim($_POST['valid_period']  ?? '');

        if (!in_array($org, ['KMD', 'NDMA'], true)) {
            $summaryError = 'Invalid source organisation.';
        } else {
            try {
                if ($org === 'KMD') {
                    $outlook = $_POST['outlook_category'] ?? '';
                    $allowed = ['below_average', 'near_average', 'above_average'];
                    if (!in_array($outlook, $allowed, true)) {
                        throw new InvalidArgumentException('Invalid outlook category.');
                    }
                    Db::query(
                        'UPDATE official_summaries
                            SET outlook_category = ?, summary_text = ?,
                                valid_period = ?, updated_at = NOW()
                          WHERE source_org = ?',
                        [$outlook, $summary, $period, 'KMD']
                    );
                } else {
                    // NDMA
                    $phase   = $_POST['drought_phase'] ?? '';
                    $allowed = ['NORMAL', 'WATCH', 'ALERT', 'ALARM', 'EMERGENCY'];
                    if (!in_array($phase, $allowed, true)) {
                        throw new InvalidArgumentException('Invalid drought phase.');
                    }
                    Db::query(
                        'UPDATE official_summaries
                            SET drought_phase = ?, summary_text = ?,
                                valid_period = ?, updated_at = NOW()
                          WHERE source_org = ?',
                        [$phase, $summary, $period, 'NDMA']
                    );
                }
                $summarySuccess = "Official summary for {$org} updated successfully.";
            } catch (Throwable $e) {
                $summaryError = 'Update failed: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

$pageTitle = 'Admin';
require __DIR__ . '/includes/header.php';
?>

<?php if (!Auth::check()): ?>
<!-- Login Form -->
<section class="page-section">
    <div class="container" style="max-width:420px;">
        <div class="card">
            <div class="text-center mb-lg">
                <h2 class="card-title">Admin Login</h2>
                <p class="text-muted" style="font-size:var(--fs-sm);">Enter the admin password to continue.</p>
            </div>

            <?php if ($loginError): ?>
            <div class="alert-banner alert-red mb-md">
                <div><?= htmlspecialchars($loginError) ?></div>
            </div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <?= Csrf::field() ?>
                <input type="hidden" name="admin_login" value="1">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-input" id="password" name="password" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
            </form>
        </div>
    </div>
</section>

<?php else: ?>
<!-- Admin Dashboard -->
<section class="page-section" style="padding-top:var(--sp-lg);">
    <div class="container">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--sp-md);margin-bottom:var(--sp-lg);">
            <div>
                <h1 style="font-size:var(--fs-xl);color:var(--clr-primary);">Contact Messages</h1>
                <p class="text-muted" style="font-size:var(--fs-sm);">
                    Logged in since <?= date('H:i', Auth::meta()['login_time'] ?? time()) ?>.
                </p>
            </div>
            <a href="logout.php" class="btn btn-outline btn-sm">Logout →</a>
        </div>

        <!-- Filters -->
        <div class="card mb-lg" style="padding:var(--sp-md);">
            <form method="GET" action="admin.php" style="display:flex;gap:var(--sp-md);flex-wrap:wrap;align-items:end;">
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="filter" style="width:auto;">
                        <option value="">All</option>
                        <option value="unread" <?= ($_GET['filter'] ?? '') === 'unread' ? 'selected' : '' ?>>Unread</option>
                        <option value="read"   <?= ($_GET['filter'] ?? '') === 'read'   ? 'selected' : '' ?>>Read</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Group</label>
                    <select class="form-select" name="group" style="width:auto;">
                        <option value="">All groups</option>
                        <?php
                        $groups = ['government'=>'Government','ngo'=>'NGO','radio'=>'Radio','pastoralist'=>'Pastoralist','intermediary'=>'Intermediary','other'=>'Other'];
                        foreach ($groups as $val => $label): ?>
                        <option value="<?= $val ?>" <?= ($_GET['group'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="admin.php" class="btn btn-outline btn-sm">Reset</a>
            </form>
        </div>

        <!-- Messages table -->
        <?php
        try {
            $where  = [];
            $params = [];

            $filter = $_GET['filter'] ?? '';
            if ($filter === 'unread')     { $where[] = 'is_read = 0'; }
            elseif ($filter === 'read')   { $where[] = 'is_read = 1'; }

            $group = $_GET['group'] ?? '';
            if ($group && array_key_exists($group, $groups)) {
                $where[]          = 'stakeholder_group = :grp';
                $params[':grp']   = $group;
            }

            $sql = "SELECT * FROM contact_messages";
            if ($where) $sql .= " WHERE " . implode(' AND ', $where);
            $sql .= " ORDER BY created_at DESC LIMIT 200";

            $messages = Db::query($sql, $params)->fetchAll();
            $total    = count($messages);
        } catch (Throwable $e) {
            $messages = [];
            $total    = 0;
            $dbError  = true;
        }
        ?>

        <?php if (!empty($dbError)): ?>
        <div class="alert-banner alert-red">
            <div>Database error. Have you imported <code>samburu_ews.sql</code> yet?</div>
        </div>
        <?php else: ?>

        <p class="text-muted mb-md" style="font-size:var(--fs-sm);">Showing <strong><?= $total ?></strong> message<?= $total !== 1 ? 's' : '' ?>.</p>

        <?php if ($total === 0): ?>
        <div class="card text-center" style="padding:var(--sp-2xl);">
            <p class="text-muted">No messages found.</p>
        </div>
        <?php else: ?>

        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40px;">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Group</th>
                        <th>Date</th>
                        <th style="width:60px;">Status</th>
                        <th style="width:130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $m): ?>
                    <tr style="<?= !$m['is_read'] ? 'font-weight:600;background:var(--clr-warning-light);' : '' ?>">
                        <td><?= $m['id'] ?></td>
                        <td><?= htmlspecialchars($m['name']) ?></td>
                        <td><a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a></td>
                        <td title="<?= htmlspecialchars($m['message']) ?>"><?= htmlspecialchars($m['subject']) ?></td>
                        <td><?php if ($m['stakeholder_group']): ?>
                            <span class="badge badge-blue"><?= htmlspecialchars($m['stakeholder_group']) ?></span>
                        <?php else: ?>—<?php endif; ?></td>
                        <td style="white-space:nowrap;"><?= date('j M Y H:i', strtotime($m['created_at'])) ?></td>
                        <td><?= $m['is_read'] ? '<span class="badge badge-green">Read</span>' : '<span class="badge badge-amber">New</span>' ?></td>
                        <td style="display:flex;gap:4px;">
                            <?php if (!$m['is_read']): ?>
                            <form method="POST" action="admin.php?<?= http_build_query(array_filter(['filter'=>$filter,'group'=>$group])) ?>" style="margin:0;">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="mark_read" value="1">
                                <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm" title="Mark as read">✓</button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="admin.php?<?= http_build_query(array_filter(['filter'=>$filter,'group'=>$group])) ?>"
                                  style="margin:0;" onsubmit="return confirm('Delete this message?');">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="delete_msg" value="1">
                                <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm" title="Delete">✕</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; // $total ?>
        <?php endif; // dbError ?>
    </div>
</section>

<!-- Message detail modal (click subject to expand) -->
<style>
    .data-table td[title] { cursor: help; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>

<!-- Official Summaries Panel
     Update the manually maintained KMD / NDMA structured summaries that
     feed the risk engine and the source cards on current-alert.php.
     KMD  = Scientific Forecast (outlook_category + advisory text)
     NDMA = Drought Situation   (drought_phase + situation text) -->
<section class="page-section" style="background:var(--clr-bg-alt);padding-top:var(--sp-xl);">
    <div class="container">
        <h2 style="font-size:var(--fs-xl);color:var(--clr-primary);margin-bottom:var(--sp-xs);">
            Official Summaries
        </h2>
        <p class="text-muted" style="font-size:var(--fs-sm);margin-bottom:var(--sp-lg);">
            After reading the latest KMD Forecast and NDMA Drought Bulletin, update the
            structured fields below. These values feed the risk engine and the source
            cards on the Current Alert page.
        </p>

        <?php if ($summarySuccess): ?>
        <div class="alert-banner alert-green mb-md">
            <div><?= htmlspecialchars($summarySuccess) ?></div>
        </div>
        <?php endif; ?>
        <?php if ($summaryError): ?>
        <div class="alert-banner alert-red mb-md">
            <div><?= htmlspecialchars($summaryError) ?></div>
        </div>
        <?php endif; ?>

        <?php
        // Load current values from DB to pre-fill the forms.
        $kmdSum  = null;
        $ndmaSum = null;
        try {
            $kmdSum  = Db::fetch('SELECT * FROM official_summaries WHERE source_org = ?', ['KMD']);
            $ndmaSum = Db::fetch('SELECT * FROM official_summaries WHERE source_org = ?', ['NDMA']);
        } catch (Throwable) {}
        ?>

        <div class="grid grid-2 grid-auto">

            <!-- KMD form: Scientific Forecast (future outlook) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">KMD: Scientific Forecast</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">
                        Source: Kenya Meteorological Department monthly/seasonal forecast.
                        Updates the <em>outlook_category</em> used by the risk engine.
                    </p>
                    <form method="POST" action="admin.php">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="update_summary" value="1">
                        <input type="hidden" name="summary_org"    value="KMD">

                        <div class="form-group">
                            <label class="form-label">Rainfall Outlook</label>
                            <select class="form-select" name="outlook_category" required>
                                <?php
                                $curOutlook = $kmdSum['outlook_category'] ?? '';
                                foreach (['below_average'=>'Below Average','near_average'=>'Near Average','above_average'=>'Above Average'] as $val => $lbl):
                                ?>
                                <option value="<?= $val ?>" <?= $curOutlook === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Valid Period <span class="text-muted">(e.g. March to May 2025)</span></label>
                            <input type="text" class="form-input" name="valid_period"
                                   value="<?= htmlspecialchars($kmdSum['valid_period'] ?? '') ?>"
                                   placeholder="e.g. March to May 2025">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Summary / Advisory</label>
                            <textarea class="form-input" name="summary_text" rows="3"
                                      placeholder="Paste a short advisory from the KMD bulletin…"><?= htmlspecialchars($kmdSum['summary_text'] ?? '') ?></textarea>
                        </div>

                        <?php if ($kmdSum): ?>
                        <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-sm);">
                            Last updated: <?= htmlspecialchars($kmdSum['updated_at']) ?>
                        </p>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-sm">Update KMD Summary</button>
                    </form>
                </div>
            </div>

            <!-- NDMA form: Drought Situation (current phase) -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">NDMA: Drought Situation</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-md);">
                        Source: National Drought Management Authority national/county bulletin.
                        Updates the <em>drought_phase</em> used by the risk engine.
                    </p>
                    <form method="POST" action="admin.php">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="update_summary" value="1">
                        <input type="hidden" name="summary_org"    value="NDMA">

                        <div class="form-group">
                            <label class="form-label">Samburu Drought Phase</label>
                            <select class="form-select" name="drought_phase" required>
                                <?php
                                $curPhase = $ndmaSum['drought_phase'] ?? '';
                                $phases   = ['NORMAL'=>'Normal','WATCH'=>'Watch','ALERT'=>'Alert','ALARM'=>'Alarm','EMERGENCY'=>'Emergency'];
                                foreach ($phases as $val => $lbl):
                                ?>
                                <option value="<?= $val ?>" <?= $curPhase === $val ? 'selected' : '' ?>>
                                    <?= $lbl ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bulletin Month <span class="text-muted">(e.g. February 2025)</span></label>
                            <input type="text" class="form-input" name="valid_period"
                                   value="<?= htmlspecialchars($ndmaSum['valid_period'] ?? '') ?>"
                                   placeholder="e.g. February 2025">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Situation Summary</label>
                            <textarea class="form-input" name="summary_text" rows="3"
                                      placeholder="Key findings: NDVI status, water situation, livestock condition…"><?= htmlspecialchars($ndmaSum['summary_text'] ?? '') ?></textarea>
                        </div>

                        <?php if ($ndmaSum): ?>
                        <p class="text-muted" style="font-size:var(--fs-xs);margin-bottom:var(--sp-sm);">
                            Last updated: <?= htmlspecialchars($ndmaSum['updated_at']) ?>
                        </p>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary btn-sm">Update NDMA Summary</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sync status summary -->
        <div class="card mt-lg">
            <div class="card-header">
                <h3 class="card-title">Auto-Sync Status</h3>
            </div>
            <div class="card-body">
                <?php
                try {
                    $runs = Db::fetchAll(
                        'SELECT job_name, status, message, ran_at
                           FROM ingestion_runs
                          ORDER BY ran_at DESC LIMIT 6'
                    );
                } catch (Throwable) { $runs = []; }
                ?>
                <?php if (empty($runs)): ?>
                <p class="text-muted">
                    No sync runs yet. Run
                    <code>php scripts/sync_official_reports.php</code>
                    from the terminal, or wait for the daily cron job.
                </p>
                <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                            <tr><th>Job</th><th>Status</th><th>Message</th><th>Ran At</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($runs as $r): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($r['job_name']) ?></code></td>
                                <td>
                                    <?php if ($r['status'] === 'success'): ?>
                                        <span class="badge badge-green">success</span>
                                    <?php else: ?>
                                        <span class="badge badge-red">fail</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size:var(--fs-xs);max-width:300px;"><?= htmlspecialchars($r['message']) ?></td>
                                <td style="white-space:nowrap;"><?= htmlspecialchars($r['ran_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php endif; // Auth::check ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
