<!-- history_content.php -->
<h1>Riwayat Taruhan User</h1>
<form method="get">
    <label for="user_id">Pilih User:</label>
    <select name="user_id" onchange="this.form.submit()">
        <option value="">-- Pilih User --</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>" <?= $user['id'] == $user_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($user['username']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($user_id && count($history) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Jumlah Taruhan</th>
                <th>Hasil</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr>
                    <td><?= $row['timestamp'] ?></td>
                    <td><?= number_format($row['bet_amount'], 2) ?></td>
                    <td><?= $row['result'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
<div class="pagination mt-3">
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=1" aria-label="First">First</a>
            </li>
            <li class="page-item <?= $page == 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=<?= $page - 1 ?>" aria-label="Previous">Previous</a>
            </li>
            <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=<?= $page + 1 ?>" aria-label="Next">Next</a>
            </li>
            <li class="page-item <?= $page == $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?user_id=<?= $user_id ?>&page=<?= $total_pages ?>" aria-label="Last">Last</a>
            </li>
        </ul>
    </nav>
</div>

<?php elseif ($user_id): ?>
    <p>Belum ada riwayat taruhan untuk user ini.</p>
<?php endif; ?>
