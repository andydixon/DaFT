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
    <h1>DaFT::Edit</h1>
    <p class="mb-4">Update the fields below to edit your configuration section.</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="?action=update" method="post">
        <input type="hidden" name="old_section" value="<?= htmlspecialchars($sectionName) ?>">

        <!-- Section Name -->
        <div class="form-group">
            <label for="section_name">Section Name</label>
            <input type="text" class="form-control" id="section_name" name="section_name"
                   value="<?= htmlspecialchars($sectionName) ?>" required>
        </div>

        <!-- Source -->
        <div class="form-group">
            <label for="source">Source</label>
            <input type="text" class="form-control" id="source" name="source"
                   value="<?= htmlspecialchars($sectionData['source'] ?? '') ?>">
        </div>

        <!-- Port -->
        <div class="form-group">
            <label for="port">Port</label>
            <input type="text" class="form-control" id="port" name="port"
                   value="<?= htmlspecialchars($sectionData['port'] ?? '') ?>">
        </div>

        <!-- Type -->
        <div class="form-group">
            <label for="type">Type</label>
            <select class="form-control" id="type" name="type" required onchange="toggleFields()">
                <?php
                    $types = ['mysql' => 'MySQL', 'mssql' => 'MSSQL', 'redshift' => 'RedShift', 'web-json' => 'Web JSON', 'app-json' => 'App JSON', 'web-xml' => 'Web XML', 'app-xml' => 'App XML'];
                    foreach ($types as $t => $label) {
                        $selected = ($sectionData['type'] ?? '' === $t) ? 'selected' : '';
                        echo "<option value='{$t}' {$selected}>{$label}</option>";
                    }
                ?>
            </select>
        </div>

        <!-- JSON Fields -->
        <div id="json-fields" style="display: none;">
            <div class="form-group">
                <label for="json_path">JSON Path</label>
                <input type="text" class="form-control" id="json_path" name="json_path"
                       value="<?= htmlspecialchars($sectionData['json_path'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="json_fields">Fields (comma-separated)</label>
                <input type="text" class="form-control" id="json_fields" name="json_fields"
                       value="<?= htmlspecialchars($sectionData['json_fields'] ?? '') ?>">
            </div>
        </div>

        <!-- XML Fields -->
        <div id="xml-fields" style="display: none;">
            <div class="form-group">
                <label for="xml_path">XML Path</label>
                <input type="text" class="form-control" id="xml_path" name="xml_path"
                       value="<?= htmlspecialchars($sectionData['xml_path'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="xml_fields">Fields (comma-separated)</label>
                <input type="text" class="form-control" id="xml_fields" name="xml_fields"
                       value="<?= htmlspecialchars($sectionData['xml_fields'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="?action=list" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    function toggleFields() {
        var type = document.getElementById("type").value;
        document.getElementById("json-fields").style.display = (type === "web-json" || type === "app-json") ? "block" : "none";
        document.getElementById("xml-fields").style.display = (type === "web-xml" || type === "app-xml") ? "block" : "none";
    }
    document.addEventListener("DOMContentLoaded", toggleFields);
</script>

</body>
</html>
