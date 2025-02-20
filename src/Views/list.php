<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Config List</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" 
          crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Federaliser Administration</h1>
    <a href="?action=create" class="btn btn-primary mb-3">Add New Section</a>

    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
        <tr>
            <th>Section Name</th>
            <th>Hostname</th>
            <th>Port</th>
            <th>Type</th>
            <th>Identifier</th>
            <th>Username</th>
            <th>Default DB</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($sections as $sectionName => $data): ?>
            <tr>
                <td><?= htmlspecialchars($sectionName) ?></td>
                <td><?= htmlspecialchars($data['hostname'] ?? '') ?></td>
                <td><?= htmlspecialchars($data['port'] ?? '') ?></td>
                <td><?= htmlspecialchars($data['type'] ?? '') ?></td>
                <td><?= htmlspecialchars($data['identifier'] ?? '') ?></td>
                <td><?= htmlspecialchars($data['username'] ?? '') ?></td>
                <td><?= htmlspecialchars($data['default_db'] ?? '') ?></td>
                <td>
                    <a class="btn btn-sm btn-warning" 
                       href="?action=edit&section=<?= urlencode($sectionName) ?>">Edit</a>
                    <a class="btn btn-sm btn-danger" 
                       href="?action=delete&section=<?= urlencode($sectionName) ?>" 
                       onclick="return confirm('Are you sure you want to delete this section?');">
                        Delete
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
