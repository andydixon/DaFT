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
    <h1>Federaliser::Create</h1>
    <p class="mb-4">
        Fill in the fields below to create a new configuration section. Each field serves a specific purpose depending on the data source or output handler type you choose.
    </p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="?action=store" method="post">
        <!-- Section Name -->
        <div class="form-group">
            <label for="section_name">Section Name (the [bracketed] name)</label>
            <input type="text" class="form-control" id="section_name" name="section_name" required>
            <small class="form-text text-muted">
                This name is used as the header for your configuration (e.g. [descriptive]). It must be unique.
            </small>
        </div>
        <!-- Hostname -->
        <div class="form-group">
            <label for="hostname">Hostname</label>
            <input type="text" class="form-control" id="hostname" name="hostname">
            <small class="form-text text-muted">
                For databases, enter the server address (e.g. mysql.example.com). For web handlers, provide the full URL (e.g. https://api.example.com/data.json). For app handlers, provide the command to execute.
            </small>
        </div>
        <!-- Port -->
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port">
            <small class="form-text text-muted">
                The port number used for the connection. Common values are: 3306 for MySQL, 1433 for MSSQL, 5439 for Redshift, and 9090 for Prometheus. May be omitted for web or app handlers if not applicable.
            </small>
        </div>
        <!-- Type -->
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type" required>
                <option value="mysql">MySQL or MySQL compatible</option>
                <option value="mssql">Microsoft SQL Server</option>
                <option value="redshift">RedShift / Postgres</option>
                <option value="prometheus">Prometheus</option>
                <option value="web-json">Web JSON</option>
                <option value="app-json">App JSON</option>
                <option value="stdout">Standard Output from command</option>
                <option value="web-xml">Web XML</option>
                <option value="app-xml">App XML</option>
            </select>
            <small class="form-text text-muted">
                Select the data source type or output handler. This choice determines how the other fields are interpreted. For example, choose "mysql" for a MySQL database, or "web-json" to fetch JSON data from a web URL.
            </small>
        </div>
        <!-- Identifier -->
        <div class="form-group">
            <label for="identifier">Identifier (must be unique &amp; lowercase)</label>
            <input type="text" class="form-control" id="identifier" name="identifier" required>
            <small class="form-text text-muted">
                A unique, lowercase identifier for this configuration. This value is used internally to reference the configuration.
            </small>
        </div>
        <!-- Username -->
        <div class="form-group">
            <label for="username">Username (can be empty if not needed)</label>
            <input type="text" class="form-control" id="username" name="username">
            <small class="form-text text-muted">
                The username required to connect to the data source. Not needed for web or app handlers.
            </small>
        </div>
        <!-- Password -->
        <div class="form-group">
            <label for="password">Password (can be empty if not needed)</label>
            <input type="text" class="form-control" id="password" name="password">
            <small class="form-text text-muted">
                The password required to connect to the data source. Not needed for web or app handlers.
            </small>
        </div>
        <!-- Default DB -->
        <div class="form-group">
            <label for="default_db">Default DB</label>
            <input type="text" class="form-control" id="default_db" name="default_db">
            <small class="form-text text-muted">
                The default database name to connect to. Applicable for database types like MySQL, MSSQL, and Redshift.
            </small>
        </div>
        <!-- Query -->
        <div class="form-group">
            <label for="query">Query</label>
            <textarea class="form-control" id="query" name="query" rows="3"></textarea>
            <small class="form-text text-muted">
                For database types (MySQL, MSSQL, Redshift), enter the SQL query to execute.
                For Prometheus, enter the PromQL query.
                For Web or App JSON/XML types, you may provide a comma-separated list of keys to extract from the returned data.
                For Standard Output, enter a regex pattern with named capturing groups to parse the output.
            </small>
        </div>
        <button type="submit" class="btn btn-success">Create</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
