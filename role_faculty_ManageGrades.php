<?php
// Included inside dashboard.php — session already started
$gradesFile = "grades.json";
$usersFile  = "users.json";

$gradesData = file_exists($gradesFile) ? json_decode(file_get_contents($gradesFile), true) : ['grades' => []];
$usersData  = file_exists($usersFile)  ? json_decode(file_get_contents($usersFile), true)  : ['users' => []];

$allGrades = $gradesData['grades'] ?? [];
$allUsers  = $usersData['users']   ?? [];

// Only grades entered by this faculty member
$myGrades = array_filter($allGrades, fn($g) => $g['faculty'] === $_SESSION['username']);
$myGrades = array_reverse(array_values($myGrades));

// Approved students list for dropdown hint
$approvedStudents = array_filter($allUsers, fn($u) => ($u['role'] ?? 'student') === 'student' && ($u['status'] ?? 'pending') === 'approved');

// Subject filter
$filterSubj = $_GET['subj'] ?? 'all';
$subjects   = array_unique(array_column($myGrades, 'subject'));

$displayedGrades = $myGrades;
if ($filterSubj !== 'all') {
    $displayedGrades = array_filter($myGrades, fn($g) => $g['subject'] === $filterSubj);
}

// Grade values allowed
$gradeOptions = ['1.00','1.25','1.50','1.75','2.00','2.25','2.50','2.75','3.00','5.00','INC','W'];
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 28px; }

    .grades-layout { display: grid; grid-template-columns: 360px 1fr; gap: 28px; align-items: start; }

    /* ── FORM CARD ── */
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

    .form-group { margin-bottom: 14px; }
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
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 10px 13px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        font-family: 'Inter', sans-serif;
        color: #0f1923;
        box-sizing: border-box;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #D4AF37;
        box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
    }

    .form-hint { font-size: 0.74rem; color: #9ca3af; margin-top: 4px; }

    .student-list {
        max-height: 100px;
        overflow-y: auto;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 10px;
        margin-top: 6px;
    }

    .student-list span {
        display: inline-block;
        padding: 2px 8px;
        background: #e0f2fe;
        color: #0369a1;
        border-radius: 12px;
        font-size: 0.72rem;
        margin: 2px;
        cursor: pointer;
        transition: background 0.15s;
    }

    .student-list span:hover { background: #bae6fd; }

    .submit-btn {
        width: 100%;
        padding: 12px;
        background: #D4AF37;
        color: white;
        border: none;
        border-radius: 9px;
        font-size: 0.9rem;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: background 0.2s;
        margin-top: 4px;
    }

    .submit-btn:hover { background: #a8891e; }

    /* ── GRADES TABLE ── */
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

    .grades-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        font-size: 0.865rem;
    }

    .grades-table thead tr { background: #f9fafb; }
    .grades-table th {
        padding: 11px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        border-bottom: 1px solid #e5e7eb;
    }

    .grades-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        vertical-align: middle;
    }

    .grades-table tr:last-child td { border-bottom: none; }
    .grades-table tr:hover td { background: #fafafa; }

    .grade-val {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: #D4AF37;
    }

    .grade-val.fail  { color: #dc2626; }
    .grade-val.inc   { color: #d97706; }

    .btn-sm {
        padding: 5px 11px;
        border-radius: 6px;
        font-size: 0.77rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        transition: opacity 0.2s;
    }

    .btn-edit   { background: #eff6ff; color: #1d4ed8; margin-right: 4px; }
    .btn-delete { background: #fee2e2; color: #991b1b; }
    .btn-sm:hover { opacity: 0.75; }

    .empty-state { text-align: center; padding: 50px 20px; color: #9ca3af; }
    .empty-state span { font-size: 2.5rem; display: block; margin-bottom: 10px; }

    /* ── EDIT MODAL ── */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.45);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal {
        background: white;
        border-radius: 14px;
        padding: 32px;
        width: 400px;
        max-width: 92vw;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .modal h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        margin-bottom: 20px;
        color: #0f1923;
    }

    .modal-actions { display: flex; gap: 10px; margin-top: 20px; }
    .modal-actions button { flex: 1; padding: 11px; border-radius: 8px; font-size: 0.88rem; font-weight: 600; cursor: pointer; border: none; font-family: 'Inter', sans-serif; }
    .btn-save   { background: #D4AF37; color: white; }
    .btn-cancel { background: #f3f4f6; color: #374151; }
</style>

<div class="section-title">Manage Grades</div>
<div class="section-sub">Record, update, and manage your students' grades. Only students with approved accounts are listed below.</div>

<div class="grades-layout">

    <!-- Add Grade Form -->
    <div class="form-card">
        <h3>📝 Add New Grade</h3>
        <form action="process_grade.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>Student Username *</label>
                <input type="text" name="student_username" id="studentInput" placeholder="Type exact username…" required>
                <div class="form-hint">Click a name below to auto-fill</div>
                <?php if (!empty($approvedStudents)): ?>
                <div class="student-list">
                    <?php foreach ($approvedStudents as $stu): ?>
                    <span onclick="document.getElementById('studentInput').value='<?= htmlspecialchars($stu['username']) ?>'"><?= htmlspecialchars($stu['username']) ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="form-hint" style="color:#f59e0b; margin-top:6px;">⚠ No approved students found. Ask admin to approve students first.</div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Subject / Course *</label>
                <input type="text" name="subject" placeholder="e.g. Introduction to Programming" required>
            </div>

            <div class="form-group">
                <label>Grade *</label>
                <select name="grade" required>
                    <option value="" disabled selected>Select grade…</option>
                    <?php foreach ($gradeOptions as $g): ?>
                    <option value="<?= $g ?>"><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" rows="2" placeholder="Optional remarks or notes…"></textarea>
            </div>

            <button type="submit" class="submit-btn">Add Grade</button>
        </form>
    </div>

    <!-- Grades List -->
    <div>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <strong style="font-size:0.9rem; color:#374151;"><?= count($myGrades) ?> grade record(s)</strong>
        </div>

        <?php if (!empty($subjects)): ?>
        <div class="filter-bar">
            <a href="dashboard.php?page=grades" class="filter-pill <?= $filterSubj==='all'?'active':'' ?>">All Subjects</a>
            <?php foreach ($subjects as $sub): ?>
            <a href="dashboard.php?page=grades&subj=<?= urlencode($sub) ?>" class="filter-pill <?= $filterSubj===$sub?'active':'' ?>"><?= htmlspecialchars($sub) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($displayedGrades)): ?>
        <div class="empty-state">
            <span>📝</span>
            <p>No grade records yet. Use the form to add your first entry.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="grades-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Grade</th>
                    <th>Remarks</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($displayedGrades as $g):
                    $gClass = '';
                    if ($g['grade'] === '5.00') $gClass = 'fail';
                    elseif (in_array($g['grade'], ['INC','W'])) $gClass = 'inc';
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($g['student']) ?></strong></td>
                    <td><?= htmlspecialchars($g['subject']) ?></td>
                    <td><span class="grade-val <?= $gClass ?>"><?= htmlspecialchars($g['grade']) ?></span></td>
                    <td style="color:#6b7280; font-size:0.82rem;"><?= htmlspecialchars($g['remarks'] ?: '—') ?></td>
                    <td style="color:#9ca3af; font-size:0.8rem;"><?= htmlspecialchars(substr($g['date'] ?? '', 0, 10)) ?></td>
                    <td>
                        <button class="btn-sm btn-edit" onclick="openEdit(<?= (int)$g['id'] ?>, '<?= htmlspecialchars(addslashes($g['grade'])) ?>', '<?= htmlspecialchars(addslashes($g['remarks'] ?? '')) ?>')">✏ Edit</button>
                        <form method="POST" action="process_grade.php" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="grade_id" value="<?= (int)$g['id'] ?>">
                            <button type="submit" class="btn-sm btn-delete" onclick="return confirm('Delete this grade record for <?= htmlspecialchars(addslashes($g['student'])) ?>?')">🗑</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Grade</h3>
        <form action="process_grade.php" method="POST" id="editForm">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="grade_id" id="editGradeId">

            <div class="form-group">
                <label>Grade</label>
                <select name="grade" id="editGradeVal" required>
                    <?php foreach ($gradeOptions as $g): ?>
                    <option value="<?= $g ?>"><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Remarks</label>
                <textarea name="remarks" id="editRemarks" rows="3" placeholder="Optional remarks…"></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(id, grade, remarks) {
    document.getElementById('editGradeId').value  = id;
    document.getElementById('editGradeVal').value = grade;
    document.getElementById('editRemarks').value  = remarks;
    document.getElementById('editModal').classList.add('open');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>