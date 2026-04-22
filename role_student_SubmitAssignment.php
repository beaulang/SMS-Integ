<?php
// Included inside dashboard.php — session already started
$jsonFile   = "assignments.json";
$data       = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['assignments' => []];
$allAssigns = $data['assignments'] ?? [];

// This student's submissions only — newest first
$myAssigns = array_reverse(array_values(
    array_filter($allAssigns, fn($a) => $a['student'] === $_SESSION['username'])
));

// Subject filter on history
$filterSubj = $_GET['subj'] ?? 'all';
$mySubjects = array_unique(array_column($myAssigns, 'subject'));
sort($mySubjects);

$displayAssigns = $myAssigns;
if ($filterSubj !== 'all') {
    $displayAssigns = array_values(array_filter($myAssigns, fn($a) => $a['subject'] === $filterSubj));
}
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 28px; }

    .assign-layout { display: grid; grid-template-columns: 380px 1fr; gap: 28px; align-items: start; }

    /* ── FORM ── */
    .form-card {
        background: white;
        border-radius: 12px;
        padding: 26px 28px;
        border: 1px solid #e5e7eb;
        position: sticky;
        top: 20px;
    }

    .form-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.05rem;
        margin-bottom: 18px;
        color: #0f1923;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    .form-group { margin-bottom: 15px; }
    .form-group label {
        display: block;
        font-size: 0.77rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px 13px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        font-family: 'Inter', sans-serif;
        box-sizing: border-box;
        resize: vertical;
        color: #0f1923;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #D4AF37;
        box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
    }

    .form-hint { font-size: 0.74rem; color: #9ca3af; margin-top: 4px; }

    .char-count { float: right; font-size: 0.72rem; color: #9ca3af; }

    .submit-btn {
        width: 100%;
        padding: 13px;
        background: #D4AF37;
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.9rem;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        margin-top: 6px;
        letter-spacing: 0.3px;
    }
    .submit-btn:hover { background: #a8891e; transform: translateY(-1px); }

    .info-note {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 11px 14px;
        font-size: 0.8rem;
        color: #1d4ed8;
        margin-bottom: 18px;
        line-height: 1.5;
    }

    /* ── HISTORY ── */
    .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
    .filter-pill {
        padding: 5px 14px;
        border-radius: 20px;
        border: 1px solid #d1d5db;
        background: white;
        font-size: 0.79rem;
        text-decoration: none;
        color: #374151;
        transition: all 0.2s;
    }
    .filter-pill:hover, .filter-pill.active { background: #D4AF37; color: white; border-color: #D4AF37; }

    .assign-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        padding: 20px 24px;
        margin-bottom: 14px;
        transition: box-shadow 0.2s;
    }
    .assign-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.07); }

    .assign-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }

    .assign-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f1923;
        margin-bottom: 4px;
    }

    .assign-subject {
        display: inline-block;
        padding: 3px 10px;
        background: rgba(212,175,55,0.12);
        color: #a8891e;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        flex-shrink: 0;
        margin-left: 12px;
    }

    .status-submitted { background: #eff6ff; color: #1d4ed8; }
    .status-graded    { background: #f0fdf4; color: #15803d; }
    .status-late      { background: #fff7ed; color: #c2410c; }

    .assign-desc { font-size: 0.845rem; color: #6b7280; line-height: 1.55; margin-bottom: 10px; }

    .assign-content {
        background: #f9fafb;
        border-radius: 8px;
        padding: 12px 16px;
        font-size: 0.83rem;
        color: #374151;
        line-height: 1.6;
        max-height: 90px;
        overflow: hidden;
        position: relative;
        margin-bottom: 10px;
        white-space: pre-wrap;
    }

    .assign-content::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 24px;
        background: linear-gradient(transparent, #f9fafb);
    }

    .expand-btn {
        background: none;
        border: none;
        color: #2563eb;
        font-size: 0.79rem;
        cursor: pointer;
        padding: 0;
        font-family: 'Inter', sans-serif;
        text-decoration: underline;
        margin-bottom: 8px;
    }

    .assign-link { font-size: 0.82rem; color: #2563eb; text-decoration: none; }
    .assign-link:hover { text-decoration: underline; }

    .assign-meta { font-size: 0.77rem; color: #9ca3af; margin-top: 10px; }

    .empty-state { text-align: center; padding: 50px 20px; color: #9ca3af; }
    .empty-state span { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    /* stats */
    .stats-row {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    .mini-stat {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 20px;
        min-width: 100px;
    }
    .mini-stat .val { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #0f1923; line-height: 1; }
    .mini-stat .lbl { font-size: 0.7rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.4px; margin-top: 4px; }
</style>

<div class="section-title">Submit Assignment</div>
<div class="section-sub">Submit your assignments here and track your submission history below.</div>

<div class="assign-layout">

    <!-- Submission Form -->
    <div class="form-card">
        <h3>📤 New Submission</h3>

        <div class="info-note">
            ℹ️ Provide either your assignment content directly in the text box, or a link to your file (Google Drive, OneDrive, etc.), or both.
        </div>

        <form action="process_submit_assignment.php" method="POST">

            <div class="form-group">
                <label>Assignment Title *</label>
                <input type="text" name="title" placeholder="e.g. Lab Report #2" required>
            </div>

            <div class="form-group">
                <label>Subject / Course *</label>
                <input type="text" name="subject" placeholder="e.g. Introduction to Programming" required>
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="2" placeholder="Briefly describe what this assignment is about…" required></textarea>
            </div>

            <div class="form-group">
                <label>
                    Assignment Content
                    <span class="char-count" id="contentCount">0 chars</span>
                </label>
                <textarea name="content" id="contentArea" rows="5" placeholder="Paste or type your answer/work here…" oninput="document.getElementById('contentCount').textContent=this.value.length+' chars'"></textarea>
                <div class="form-hint">Optional if you provide a submission link below</div>
            </div>

            <div class="form-group">
                <label>Submission Link</label>
                <input type="url" name="link" placeholder="https://drive.google.com/…">
                <div class="form-hint">Optional: Google Drive, OneDrive, Dropbox, GitHub, etc.</div>
            </div>

            <button type="submit" class="submit-btn">📤 Submit Assignment</button>
        </form>
    </div>

    <!-- Submission History -->
    <div>
        <div class="stats-row">
            <div class="mini-stat">
                <div class="val"><?= count($myAssigns) ?></div>
                <div class="lbl">Total Submitted</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?= count(array_filter($myAssigns, fn($a) => ($a['status'] ?? '') === 'graded')) ?></div>
                <div class="lbl">Graded</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?= count(array_unique(array_column($myAssigns, 'subject'))) ?></div>
                <div class="lbl">Subjects</div>
            </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <strong style="font-size:0.9rem; color:#374151;">Submission History</strong>
        </div>

        <?php if (!empty($mySubjects)): ?>
        <div class="filter-bar">
            <a href="dashboard.php?page=submit" class="filter-pill <?= $filterSubj==='all'?'active':'' ?>">All</a>
            <?php foreach ($mySubjects as $sub): ?>
            <a href="dashboard.php?page=submit&subj=<?= urlencode($sub) ?>" class="filter-pill <?= $filterSubj===$sub?'active':'' ?>"><?= htmlspecialchars($sub) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($myAssigns)): ?>
        <div class="empty-state">
            <span>📤</span>
            <p>No submissions yet. Use the form to submit your first assignment.</p>
        </div>
        <?php elseif (empty($displayAssigns)): ?>
        <div class="empty-state">
            <span>🔍</span>
            <p>No submissions for this subject.</p>
        </div>
        <?php else: ?>
            <?php foreach ($displayAssigns as $idx => $a):
                $status     = $a['status'] ?? 'submitted';
                $statusClass = 'status-' . $status;
                $statusLabel = ucfirst($status);
                $statusIcon  = $status === 'graded' ? '✔' : ($status === 'late' ? '⏰' : '📨');
                $expanded    = false;
            ?>
            <div class="assign-card">
                <div class="assign-header">
                    <div>
                        <div class="assign-title"><?= htmlspecialchars($a['title']) ?></div>
                        <span class="assign-subject"><?= htmlspecialchars($a['subject']) ?></span>
                    </div>
                    <span class="status-badge <?= $statusClass ?>"><?= $statusIcon ?> <?= $statusLabel ?></span>
                </div>

                <div class="assign-desc"><?= nl2br(htmlspecialchars($a['description'])) ?></div>

                <?php if (!empty($a['content'])): ?>
                <div class="assign-content" id="content-<?= $idx ?>"><?= htmlspecialchars($a['content']) ?></div>
                <button class="expand-btn" id="expbtn-<?= $idx ?>" onclick="toggleContent(<?= $idx ?>)">Show full content ▼</button>
                <?php endif; ?>

                <?php if (!empty($a['link'])): ?>
                <div>🔗 <a href="<?= htmlspecialchars($a['link']) ?>" target="_blank" rel="noopener noreferrer" class="assign-link">Open Submission File</a></div>
                <?php endif; ?>

                <div class="assign-meta">Submitted on <?= htmlspecialchars($a['submitted_date'] ?? '—') ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const expandedMap = {};
function toggleContent(idx) {
    const box = document.getElementById('content-' + idx);
    const btn = document.getElementById('expbtn-' + idx);
    if (!expandedMap[idx]) {
        box.style.maxHeight = 'none';
        box.style.overflow  = 'visible';
        btn.textContent     = 'Show less ▲';
        expandedMap[idx]    = true;
    } else {
        box.style.maxHeight = '90px';
        box.style.overflow  = 'hidden';
        btn.textContent     = 'Show full content ▼';
        expandedMap[idx]    = false;
    }
}
</script>