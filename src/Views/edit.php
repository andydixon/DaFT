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
    <p class="mb-4">
        Update the fields below to edit your configuration section. Each field has a specific purpose based on the chosen type.
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="?action=update" method="post">
        <input type="hidden" name="old_section" value="<?= htmlspecialchars($sectionName) ?>">

        <!-- Section Name -->
        <div class="form-group">
            <label for="section_name">Section Name ([bracketed])</label>
            <input type="text" class="form-control" id="section_name" name="section_name"
                   value="<?= htmlspecialchars($sectionName) ?>" required>
            <small class="form-text text-muted">
                This is the header name for your configuration (e.g. [descriptive]). It must be unique.
            </small>
        </div>

        <!-- source -->
        <div class="form-group">
            <label for="source">Source</label>
            <input type="text" class="form-control" id="source" name="source"
                   value="<?= htmlspecialchars($sectionData['source'] ?? '') ?>">
            <small class="form-text text-muted">
                For databases, use the server address (e.g. mysql.example.com). For web handlers, enter the full URL (e.g. https://api.example.com/data.json). For app handlers, provide the command to run, and for file-based parsing, enter the full path and filename to parse.
            </small>
        </div>

        <!-- Port -->
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port"
                   value="<?= htmlspecialchars($sectionData['port'] ?? '') ?>">
            <small class="form-text text-muted">
                Enter the connection port. Typical values: 3306 for MySQL, 1433 for MSSQL, 5439 for Redshift, and 9090 for Prometheus. May be left blank for web or app handlers.
            </small>
        </div>

        <!-- Type -->
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
                        'app-xml'    => 'App XML',
                        'web-csv'    => 'Web CSV',
                        'file-csv'   => 'Parse CSV file',
                        'stdout-csv'=> 'Execute command and parse CSV output',
                    ];
                    $currentType = $sectionData['type'] ?? '';
                    foreach ($types as $t => $label) {
                        $selected = ($currentType === $t) ? 'selected' : '';
                        echo "<option value='{$t}' {$selected}>{$label}</option>";
                    }
                ?>
            </select>
            <small class="form-text text-muted">
                Select the data source or handler type. This determines how other fields are interpreted. For example, choose "mysql" for a MySQL database or "web-json" to retrieve JSON from a URL.
            </small>
        </div>

        <!-- Identifier -->
        <div class="form-group">
            <label for="identifier">Identifier (must be unique &amp; lowercase)</label>
            <input type="text" class="form-control" id="identifier" name="identifier"
                   value="<?= htmlspecialchars($sectionData['identifier'] ?? '') ?>" required>
            <small class="form-text text-muted">
                A unique, lowercase identifier for this configuration. Used internally to reference this section.
            </small>
        </div>

        <!-- Username -->
        <div class="form-group">
            <label for="username">Username (can be empty if not needed)</label>
            <input type="text" class="form-control" id="username" name="username"
                   value="<?= htmlspecialchars($sectionData['username'] ?? '') ?>">
            <small class="form-text text-muted">
                Username for connecting to the data source. Not required for web or app handlers.
            </small>
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password">Password (can be empty if not needed)</label>
            <input type="text" class="form-control" id="password" name="password"
                   value="<?= htmlspecialchars($sectionData['password'] ?? '') ?>">
            <small class="form-text text-muted">
                Password for the data source connection. Not required for web or app handlers.
            </small>
        </div>

        <!-- Default DB -->
        <div class="form-group">
            <label for="default_db">Default DB</label>
            <input type="text" class="form-control" id="default_db" name="default_db"
                   value="<?= htmlspecialchars($sectionData['default_db'] ?? '') ?>">
            <small class="form-text text-muted">
                Enter the default database name for connections (applicable for MySQL, MSSQL, and Redshift).
            </small>
        </div>

        <!-- Query -->
        <div class="form-group">
            <label for="query">Query</label>
            <textarea class="form-control" id="query" name="query" rows="3"><?= htmlspecialchars($sectionData['query'] ?? '') ?></textarea>
            <small class="form-text text-muted">
                For databases, enter the SQL query to execute. For Prometheus, enter the PromQL query.
                For Web or App JSON/XML types, provide a comma-separated list of keys to extract.
                For Standard Output, enter a regex with named capturing groups to parse the output.
                <br><strong>Note: </strong> For data to be exported through the Prometheus or OpenMetrics exporter, the last column must be the integer metric. Any other columns will be presented as labels, the label name is the column name, and the value is the column value.
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
