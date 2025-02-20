<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New Section</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" 
          crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <h1>Federliser::Create</h1>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="?action=store" method="post">
        <div class="form-group">
            <label for="section_name">Section Name (the [bracketed] name)</label>
            <input type="text" class="form-control" id="section_name" name="section_name" required>
        </div>
        <div class="form-group">
            <label for="hostname">Hostname</label>
            <input type="text" class="form-control" id="hostname" name="hostname">
        </div>
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port">
        </div>
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="">-- Choose type --</option>
                <option value="mysql">MySQL or MySQL compatible</option>
                <option value="mssql">Microsoft SQL Server</option>
                <option value="redshift">RedShift / Postgres</option>
                <option value="prometheus">Prometheus</option>
            </select>
        </div>
        <div class="form-group">
            <label for="identifier">Identifier (must be unique & lowercase)</label>
            <input type="text" class="form-control" id="identifier" name="identifier" required>
        </div>
        <div class="form-group">
            <label for="username">Username (can be empty if not needed)</label>
            <input type="text" class="form-control" id="username" name="username">
        </div>
        <div class="form-group">
            <label for="password">Password (can be empty if not needed)</label>
            <input type="text" class="form-control" id="password" name="password">
        </div>
        <div class="form-group">
            <label for="default_db">Default DB</label>
            <input type="text" class="form-control" id="default_db" name="default_db">
        </div>
        <div class="form-group">
            <label for="query">Query</label>
            <textarea class="form-control" id="query" name="query" rows="3"></textarea>
        </div>
        <button type="submit" class="btn btn-success">Create</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
