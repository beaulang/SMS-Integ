<?php
// Included inside dashboard.php
$jsonFile  = "materials.json";
$data      = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : ['materials' => []];
$materials = $data['materials'] ?? [];

// My materials only
$myMaterials = array_filter($materials, fn($m) => $m['uploaded_by'] === $_SESSION['username']);
$myMaterials = array_reverse(array_values($myMaterials));

// Subject filter
$filterSubj = $_GET['subj'] ?? 'all';
$subjects   = array_unique(array_column($myMaterials, 'subject'));

if ($filterSubj !== 'all') {
    $myMaterials = array_filter($myMaterials, fn($m) => $m['subject'] === $filterSubj);
}
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 28px; }

    .two-col { display: grid; grid-template-columns: 400px 1fr; gap: 28px; align-items: start; }

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 28px 30px;
        border: 1px solid #e5e7eb;
        position: sticky;
        top: 20px;
    }

    .form-card h3 { font-family: 'Playfair Display', serif; font-size: 1.05rem; margin-bottom: 20px; color: #0f1923; padding-bottom: 12px; border-bottom: 1px solid #f3f4f6; }

    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.4px; }
    .form-group input, .form-group textarea {
        width: 100%; padding: 10px 13px; border: 1px solid #d1d5db; border-radius: 8px;
        font-size: 0.875rem; font-family: 'Inter', sans-serif; resize: vertical;
        transition: border-color 0.2s; box-sizing: border-box;
    }
    .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #D4AF37; box-shadow: 0 0 0 3px rgba(212,175,55,0.12); }
    .form-hint { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }

    .submit-btn { width: 100%; padding: 12px; background: #D4AF37; color: white; border: none; border-radius: 9px; font-size: 0.9rem; font-weight: 700; font-family: 'Inter', sans-serif; cursor: pointer; transition: background 0.2s; margin-top: 6px; }
    .submit-btn:hover { background: #a8891e; }

    /* Material Cards */
    .filter-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
    .filter-pill { padding: 5px 14px; border-radius: 20px; border: 1px solid #d1d5db; background: white; font-size: 0.8rem; text-decoration: none; color: #374151; transition: all 0.2s; }
    .filter-pill:hover, .filter-pill.active { background: #D4AF37; color: white; border-color: #D4AF37; }

    .mat-card {
        background: white;
        border-radius: 12px;
        padding: 20px 24px;
        border: 1px solid #e5e7eb;
        margin-bottom: 14px;
        transition: box-shadow 0.2s;
    }
    .mat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.07); }

    .mat-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
    .mat-title { font-weight: 700; font-size: 0.95rem; color: #0f1923; }
    .mat-subject { display: inline-block; padding: 3px 10px; background: rgba(212,175,55,0.12); color: #a8891e; border-radius: 20px; font-size: 0.72rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
    .mat-desc { font-size: 0.85rem; color: #6b7280; margin: 8px 0; line-height: 1.5; }
    .mat-meta { font-size: 0.77rem; color: #9ca3af; margin-top: 10px; }
    .mat-link { font-size: 0.82rem; color: #2563eb; text-decoration: none; }
    .mat-link:hover { text-decoration: underline; }
    .mat-content { font-size: 0.83rem; color: #374151; background: #f9fafb; border-radius: 8px; padding: 10px 14px; margin-top: 10px; line-height: 1.5; max-height: 80px; overflow: hidden; }

    .delete-btn { padding: 5px 12px; background: #fee2e2; color: #991b1b; border: none; border-radius: 6px; font-size: 0.77rem; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; }
    .delete-btn:hover { background: #fecaca; }

    .empty-state { text-align: center; padding: 50px 20px; color: #9ca3af; }
    .empty-state span { font-size: 2.5rem; display: block; margin-bottom: 10px; }
</style>

<div class="section-title">Upload Materials</div>
<div class="section-sub">Share course materials with your students. All students can view what you upload here.</div>

<div class="two-col">

    <!-- Upload Form -->
    <div class="form-card">
        <h3>📤 Add New Material</h3>
        <form action="process_upload_material.php" method="POST">
            <div class="form-group">
                <label>Title *</label>
                <input type="text" name="title" placeholder="e.g. Week 3 Lecture Notes" required>
            </div>
            <div class="form-group">
                <label>Subject / Course *</label>
                <input type="text" name="subject" placeholder="e.g. Introduction to Programming" required>
            </div>
            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" rows="3" placeholder="Brief description of what this material covers…" required></textarea>
            </div>
            <div class="form-group">
                <label>Content</label>
                <textarea name="content" rows="4" placeholder="Paste the material content, notes, or key points here…"></textarea>
                <div class="form-hint">Optional: Include text content directly</div>
            </div>
            <div class="form-group">
                <label>Resource Link</label>
                <input type="url" name="link" placeholder="https://drive.google.com/…">
                <div class="form-hint">Optional: Link to Google Drive, PDF, slides, etc.</div>
            </div>
            <button type="submit" class="submit-btn">Upload Material</button>
        </form>
    </div>

    <!-- Materials List -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <strong style="font-size:0.9rem; color:#374151;"><?= count($myMaterials) ?> material(s) uploaded</strong>
        </div>

        <?php if (!empty($subjects)): ?>
        <div class="filter-bar">
            <a href="dashboard.php?page=upload" class="filter-pill <?= $filterSubj==='all'?'active':'' ?>">All</a>
            <?php foreach ($subjects as $sub): ?>
            <a href="dashboard.php?page=upload&subj=<?= urlencode($sub) ?>" class="filter-pill <?= $filterSubj===$sub?'active':'' ?>"><?= htmlspecialchars($sub) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($myMaterials)): ?>
        <div class="empty-state">
            <span>📚</span>
            <p>No materials uploaded yet. Use the form to add your first material.</p>
        </div>
        <?php else: ?>
            <?php foreach ($myMaterials as $mat): ?>
            <div class="mat-card">
                <div class="mat-card-header">
                    <div>
                        <div class="mat-title"><?= htmlspecialchars($mat['title']) ?></div>
                        <span class="mat-subject"><?= htmlspecialchars($mat['subject']) ?></span>
                    </div>
                    <form method="POST" action="process_upload_material.php" style="flex-shrink:0; margin-left:10px;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="material_id" value="<?= (int)$mat['id'] ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($mat['title'])) ?>\'?')">🗑 Delete</button>
                    </form>
                </div>

                <div class="mat-desc"><?= htmlspecialchars($mat['description']) ?></div>

                <?php if (!empty($mat['content'])): ?>
                <div class="mat-content"><?= nl2br(htmlspecialchars($mat['content'])) ?></div>
                <?php endif; ?>

                <?php if (!empty($mat['link'])): ?>
                <div style="margin-top:10px;">
                    🔗 <a href="<?= htmlspecialchars($mat['link']) ?>" target="_blank" rel="noopener noreferrer" class="mat-link">Open Resource</a>
                </div>
                <?php endif; ?>

                <div class="mat-meta">Uploaded on <?= htmlspecialchars($mat['upload_date']) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>