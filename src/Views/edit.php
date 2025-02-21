<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Section</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" 
          crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Federaliser::Edit</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="?action=update" method="post">
        <input type="hidden" name="old_section" value="<?= htmlspecialchars($sectionName) ?>">

        <div class="form-group">
            <label for="section_name">Section Name ([bracketed])</label>
            <input type="text" class="form-control" id="section_name" name="section_name"
                   value="<?= htmlspecialchars($sectionName) ?>" required>
        </div>
        <div class="form-group">
            <label for="hostname">Hostname</label>
            <input type="text" class="form-control" id="hostname" name="hostname"
                   value="<?= htmlspecialchars($sectionData['hostname'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port"
                   value="<?= htmlspecialchars($sectionData['port'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type" required>
                <?php
                    $types = [
                        'mysql'      => 'MySQL or MySQL compatible',
                        'mssql'      => 'Microsoft SQL Server',
                        'redshift'   => 'RedShift / Postgres',
                        'prometheus' => 'Prometheus',
                        'web-json'   => 'Web JSON',
                        'app-json'   => 'App JSON',
                        'stdout'     => 'Standard Output from command',
                        'web-xml'    => 'Web XML',
                        'app-xml'    => 'App XML'
                    ];
                    $currentType = $sectionData['type'] ?? '';
                    foreach ($types as $t=>$label) {
                        $selected = ($currentType === $t) ? 'selected' : '';
                        echo "<option value='{$t}' {$selected}>{$label}</option>";
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="identifier">Identifier (must be unique & lowercase)</label>
            <input type="text" class="form-control" id="identifier" name="identifier"
                   value="<?= htmlspecialchars($sectionData['identifier'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label for="username">Username (can be empty if not needed)</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= htmlspecialchars($sectionData['username'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="password">Password (can be empty if not needed)</label>
            <input type="text" class="form-control" id="password" name="password"
                   value="<?= htmlspecialchars($sectionData['password'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="default_db">Default DB</label>
            <input type="text" class="form-control" id="default_db" name="default_db"
                   value="<?= htmlspecialchars($sectionData['default_db'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="query">Query</label>
            <textarea class="form-control" id="query" name="query" rows="3"><?= htmlspecialchars($sectionData['query'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
