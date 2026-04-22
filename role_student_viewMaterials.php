<?php
// Included inside dashboard.php — session already started
$jsonFile  = "materials.json";
$data      = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['materials' => []];
$materials = $data['materials'] ?? [];

// Newest first
$materials = array_reverse($materials);

// Subject & search filters
$filterSubj = $_GET['subj']   ?? 'all';
$search     = strtolower(trim($_GET['search'] ?? ''));
$subjects   = array_unique(array_column($materials, 'subject'));
sort($subjects);

$filtered = $materials;
if ($filterSubj !== 'all') {
    $filtered = array_filter($filtered, fn($m) => $m['subject'] === $filterSubj);
}
if ($search) {
    $filtered = array_filter($filtered, fn($m) =>
        strpos(strtolower($m['title']), $search) !== false ||
        strpos(strtolower($m['subject']), $search) !== false ||
        strpos(strtolower($m['description']), $search) !== false
    );
}
$filtered = array_values($filtered);
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 24px; }

    /* ── TOOLBAR ── */
    .toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        margin-bottom: 22px;
    }

    .toolbar input[type="text"] {
        padding: 9px 15px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        font-family: 'Inter', sans-serif;
        width: 260px;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .toolbar input[type="text"]:focus {
        outline: none;
        border-color: #D4AF37;
        box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
    }

    .search-btn {
        padding: 9px 20px;
        background: #D4AF37;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: background 0.2s;
    }
    .search-btn:hover { background: #a8891e; }

    .reset-link {
        font-size: 0.82rem;
        color: #6b7280;
        text-decoration: none;
        padding: 9px 10px;
    }
    .reset-link:hover { color: #374151; }

    /* ── SUBJECT PILLS ── */
    .subject-bar {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #e5e7eb;
    }

    .subj-pill {
        padding: 5px 15px;
        border-radius: 20px;
        border: 1px solid #d1d5db;
        background: white;
        font-size: 0.79rem;
        text-decoration: none;
        color: #374151;
        transition: all 0.2s;
        font-family: 'Inter', sans-serif;
    }
    .subj-pill:hover, .subj-pill.active { background: #D4AF37; color: white; border-color: #D4AF37; }

    /* ── MATERIAL CARDS GRID ── */
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .mat-card {
        background: white;
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: box-shadow 0.2s, transform 0.2s;
    }

    .mat-card:hover {
        box-shadow: 0 8px 24px rgba(0,0,0,0.09);
        transform: translateY(-2px);
    }

    .mat-card-top {
        background: linear-gradient(135deg, #0f1923 0%, #1a2837 100%);
        padding: 20px 22px;
        position: relative;
        overflow: hidden;
    }

    .mat-card-top::after {
        content: '📄';
        position: absolute;
        right: 18px;
        top: 12px;
        font-size: 2rem;
        opacity: 0.25;
    }

    .mat-subject-tag {
        display: inline-block;
        padding: 3px 10px;
        background: rgba(212,175,55,0.2);
        border: 1px solid rgba(212,175,55,0.4);
        color: #D4AF37;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .mat-title {
        font-family: 'Playfair Display', serif;
        font-size: 1rem;
        color: white;
        line-height: 1.3;
    }

    .mat-card-body {
        padding: 18px 22px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .mat-desc {
        font-size: 0.845rem;
        color: #6b7280;
        line-height: 1.55;
        flex: 1;
        margin-bottom: 14px;
    }

    .mat-content-preview {
        background: #f9fafb;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.8rem;
        color: #374151;
        line-height: 1.5;
        max-height: 72px;
        overflow: hidden;
        margin-bottom: 14px;
        position: relative;
    }

    .mat-content-preview::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 22px;
        background: linear-gradient(transparent, #f9fafb);
    }

    .mat-card-footer {
        padding: 12px 22px;
        border-top: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fafafa;
    }

    .mat-meta { font-size: 0.75rem; color: #9ca3af; }
    .mat-meta strong { color: #6b7280; }

    .open-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #D4AF37;
        color: white;
        border-radius: 7px;
        font-size: 0.79rem;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s;
    }
    .open-link:hover { background: #a8891e; }

    /* Full content modal */
    .view-btn {
        background: none;
        border: none;
        color: #2563eb;
        font-size: 0.79rem;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        padding: 0;
        text-decoration: underline;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-overlay.open { display: flex; }

    .modal {
        background: white;
        border-radius: 16px;
        padding: 36px;
        width: 620px;
        max-width: 95vw;
        max-height: 85vh;
        overflow-y: auto;
        box-shadow: 0 24px 64px rgba(0,0,0,0.22);
    }

    .modal-header { margin-bottom: 18px; }
    .modal-header .subj { color: #D4AF37; font-size: 0.78rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
    .modal-header h2 { font-family: 'Playfair Display', serif; font-size: 1.3rem; color: #0f1923; }
    .modal-desc { color: #6b7280; font-size: 0.88rem; margin-bottom: 18px; line-height: 1.55; }
    .modal-content-box { background: #f9fafb; border-radius: 10px; padding: 16px 20px; font-size: 0.875rem; color: #374151; line-height: 1.65; white-space: pre-wrap; margin-bottom: 18px; max-height: 260px; overflow-y: auto; }
    .modal-footer { display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #f3f4f6; }
    .modal-meta { font-size: 0.78rem; color: #9ca3af; }
    .close-btn { padding: 9px 20px; background: #f3f4f6; color: #374151; border: none; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; }
    .close-btn:hover { background: #e5e7eb; }

    .empty-state { text-align: center; padding: 70px 20px; color: #9ca3af; }
    .empty-state span { font-size: 3rem; display: block; margin-bottom: 12px; }
    .empty-state p { font-size: 0.9rem; }

    .result-count { font-size: 0.83rem; color: #6b7280; margin-bottom: 18px; }
</style>

<div class="section-title">Course Materials</div>
<div class="section-sub">Browse and access all materials shared by your faculty. Click a card to read the full content.</div>

<!-- Search bar -->
<form method="GET" action="dashboard.php">
    <input type="hidden" name="page" value="view">
    <?php if ($filterSubj !== 'all'): ?>
    <input type="hidden" name="subj" value="<?= htmlspecialchars($filterSubj) ?>">
    <?php endif; ?>
    <div class="toolbar">
        <input type="text" name="search" placeholder="Search by title, subject, or description…" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="search-btn">🔍 Search</button>
        <?php if ($search || $filterSubj !== 'all'): ?>
        <a href="dashboard.php?page=view" class="reset-link">✕ Clear filters</a>
        <?php endif; ?>
    </div>
</form>

<!-- Subject pills -->
<?php if (!empty($subjects)): ?>
<div class="subject-bar">
    <a href="dashboard.php?page=view<?= $search ? '&search='.urlencode($search) : '' ?>" class="subj-pill <?= $filterSubj==='all'?'active':'' ?>">All Subjects</a>
    <?php foreach ($subjects as $sub): ?>
    <a href="dashboard.php?page=view&subj=<?= urlencode($sub) ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="subj-pill <?= $filterSubj===$sub?'active':'' ?>"><?= htmlspecialchars($sub) ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (empty($materials)): ?>
<div class="empty-state">
    <span>📚</span>
    <p>No materials have been uploaded yet.<br>Check back after your faculty adds course content.</p>
</div>
<?php elseif (empty($filtered)): ?>
<div class="empty-state">
    <span>🔍</span>
    <p>No materials match your search. <a href="dashboard.php?page=view" style="color:#D4AF37;">Clear filters</a></p>
</div>
<?php else: ?>

<div class="result-count">Showing <strong><?= count($filtered) ?></strong> of <strong><?= count($materials) ?></strong> material(s)</div>

<div class="materials-grid">
    <?php foreach ($filtered as $mat): ?>
    <div class="mat-card">
        <div class="mat-card-top">
            <div class="mat-subject-tag"><?= htmlspecialchars($mat['subject']) ?></div>
            <div class="mat-title"><?= htmlspecialchars($mat['title']) ?></div>
        </div>
        <div class="mat-card-body">
            <div class="mat-desc"><?= nl2br(htmlspecialchars($mat['description'])) ?></div>
            <?php if (!empty($mat['content'])): ?>
            <div class="mat-content-preview"><?= nl2br(htmlspecialchars($mat['content'])) ?></div>
            <button class="view-btn" onclick='openMaterial(<?= htmlspecialchars(json_encode($mat), ENT_QUOTES) ?>)'>📖 Read full content</button>
            <?php endif; ?>
        </div>
        <div class="mat-card-footer">
            <div class="mat-meta">
                By <strong><?= htmlspecialchars($mat['uploaded_by']) ?></strong><br>
                <?= htmlspecialchars(substr($mat['upload_date'] ?? '', 0, 10)) ?>
            </div>
            <?php if (!empty($mat['link'])): ?>
            <a href="<?= htmlspecialchars($mat['link']) ?>" target="_blank" rel="noopener noreferrer" class="open-link">🔗 Open Resource</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Content Modal -->
<div class="modal-overlay" id="matModal">
    <div class="modal">
        <div class="modal-header">
            <div class="subj" id="modalSubj"></div>
            <h2 id="modalTitle"></h2>
        </div>
        <div class="modal-desc" id="modalDesc"></div>
        <div class="modal-content-box" id="modalContent"></div>
        <div id="modalLinkWrap" style="margin-bottom:16px; display:none;">
            🔗 <a id="modalLink" href="#" target="_blank" rel="noopener noreferrer" style="color:#2563eb; font-size:0.875rem;">Open External Resource</a>
        </div>
        <div class="modal-footer">
            <div class="modal-meta" id="modalMeta"></div>
            <button class="close-btn" onclick="closeMaterial()">Close</button>
        </div>
    </div>
</div>

<script>
function openMaterial(mat) {
    document.getElementById('modalSubj').textContent    = mat.subject;
    document.getElementById('modalTitle').textContent   = mat.title;
    document.getElementById('modalDesc').textContent    = mat.description;
    document.getElementById('modalContent').textContent = mat.content || '';
    document.getElementById('modalMeta').textContent    = 'Uploaded by ' + mat.uploaded_by + ' · ' + (mat.upload_date || '').substring(0, 10);

    const linkWrap = document.getElementById('modalLinkWrap');
    const link     = document.getElementById('modalLink');
    if (mat.link) {
        link.href = mat.link;
        linkWrap.style.display = 'block';
    } else {
        linkWrap.style.display = 'none';
    }
    document.getElementById('matModal').classList.add('open');
}
function closeMaterial() {
    document.getElementById('matModal').classList.remove('open');
}
document.getElementById('matModal').addEventListener('click', function(e) {
    if (e.target === this) closeMaterial();
});
</script>