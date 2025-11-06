<div class="modal-header">
    <h5 class="modal-title">Task</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body">
    <div class="mb-3">
        <label>Title *</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Description</label>
        <textarea name="description" class="form-control" rows="3"></textarea>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label>Priority</label><br>
            <div class="form-check form-check-inline">
                <input type="radio" name="priority" value="low" class="form-check-input">
                <label class="form-check-label">Low</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="priority" value="medium" class="form-check-input" checked>
                <label class="form-check-label">Medium</label>
            </div>
            <div class="form-check form-check-inline">
                <input type="radio" name="priority" value="high" class="form-check-input">
                <label class="form-check-label">High</label>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input type="checkbox" name="notifications" class="form-check-input">
            <label class="form-check-label">Email when done</label>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label>Due Date</label>
            <input type="date" name="due_date" class="form-control">
        </div>
        <div class="col-md-6 mb-3">
            <label>Category</label>
            <select name="category_id" class="form-select">
                <option value="">None</option>
                <?php
                $cats = $pdo->prepare("SELECT * FROM categories WHERE user_id = ?");
                $cats->execute([$user_id]);
                foreach ($cats->fetchAll() as $c) {
                    echo "<option value='{$c['id']}'>{$c['name']}</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label>Attachment</label>
        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
    </div>
</div>