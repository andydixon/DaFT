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
    <h1>DaFT::Create</h1>
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
        </div>

        <!-- Source -->
        <div class="form-group">
            <label for="source">Source</label>
            <input type="text" class="form-control" id="source" name="source">
        </div>

        <!-- Port -->
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port">
        </div>

        <!-- Type -->
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type" required onchange="toggleFields()">
                <option value="mysql">MySQL</option>
                <option value="mssql">MSSQL</option>
                <option value="redshift">RedShift</option>
                <option value="prometheus">Prometheus</option>
                <option value="web-json">Web JSON</option>
                <option value="app-json">App JSON</option>
                <option value="web-xml">Web XML</option>
                <option value="app-xml">App XML</option>
            </select>
        </div>

        <!-- JSON Fields -->
        <div id="json-fields" style="display: none;">
            <div class="form-group">
                <label for="json_path">JSON Path</label>
                <input type="text" class="form-control" id="json_path" name="json_path">
            </div>
            <div class="form-group">
                <label for="json_fields">Fields (comma-separated)</label>
                <input type="text" class="form-control" id="json_fields" name="json_fields">
            </div>
        </div>

        <!-- XML Fields -->
        <div id="xml-fields" style="display: none;">
            <div class="form-group">
                <label for="xml_path">XML Path</label>
                <input type="text" class="form-control" id="xml_path" name="xml_path">
            </div>
            <div class="form-group">
                <label for="xml_fields">Fields (comma-separated)</label>
                <input type="text" class="form-control" id="xml_fields" name="xml_fields">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Create</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    function toggleFields() {
        var type = document.getElementById("type").value;
        document.getElementById("json-fields").style.display = (type === "web-json" || type === "app-json") ? "block" : "none";
        document.getElementById("xml-fields").style.display = (type === "web-xml" || type === "app-xml") ? "block" : "none";
    }
</script>

</body>
</html>
