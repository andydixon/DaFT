**Federaliser** is a powerful and flexible data integration tool designed to collect data from multiple sources (MySQL, MSSQL, Redshift/Postgres, Prometheus, JSON, XML, and CSV) and export it in a consistent format. It supports JSON output suitable for Telegraf and text-based output compatible with Prometheus/OpenMetrics.

---

## Table of Contents

1. [Overview](#overview)
2. [What's New](#whats-new)
3. [Supported Data Sources and Formats](#supported-data-sources-and-formats)
4. [Installation](#installation)
5. [Configuration](#configuration)
6. [Usage](#usage)
   - [JSON / Telegraf Format](#json--telegraf-format)
   - [Prometheus/OpenMetrics Format](#prometheusopenmetrics-format)
7. [Exporter Formats](#exporter-formats)
   - [JSON Exporter](#json-exporter)
   - [Prometheus/OpenMetrics Exporter](#prometheusopenmetrics-exporter)
8. [Extending Federaliser](#extending-federaliser)
   - [Adding New Data Handlers](#adding-new-data-handlers)
   - [Creating Custom Exporters](#creating-custom-exporters)
   - [Registering and Using New Components](#registering-and-using-new-components)
9. [Prometheus Exporter Notes](#prometheus-exporter-notes)
10. [Web Server Configuration](#web-server-configuration)
    - [Nginx](#nginx)
    - [Apache](#apache)
11. [Configuring Prometheus](#configuring-prometheus)
12. [Configuring Telegraf](#configuring-telegraf)
13. [Admin Interface](#admin-interface)
14. [Troubleshooting](#troubleshooting)
15. [Contribution and Feedback](#contribution-and-feedback)

---

## Overview

Federaliser ensures that all your data outputs are provided in a consistent format. No matter what your data source or output style is, Federaliser makes it easy to integrate your data with existing monitoring and metrics systems.

---

## What's New

### Refactoring and Improvements

- **Dependency Injection**: Improved testability and maintainability by injecting dependencies (e.g., PDO, HTTP client) rather than instantiating them within handlers.
- **Enhanced Security**:
  - URL and file path validation to prevent SSRF and directory traversal attacks.
  - Use of prepared statements and input sanitisation where applicable.
- **Centralised Error Handling and Logging**: Consistent error reporting across the project.
- **Performance Enhancements**: Optimised data normalisation and filtering; support for batch processing in database handlers.

### New Features

- **Extensible Architecture**:
  - Easily add new Data Handlers and Exporters using the factory pattern.
  - Utilises Strategy and Factory patterns for scalable design.
- **Improved Format Support**:
  - Expanded support for JSON, XML, CSV, and Prometheus data formats.
  - Flexible querying and filtering capabilities.

---

## Supported Data Sources and Formats

Federaliser supports a wide variety of data sources and formats, providing flexible integration options.

### Databases

- **MySQL** (`type = mysql`)
- **MSSQL** (`type = mssql`)
- **Redshift/PostgreSQL** (`type = redshift`)

### Monitoring Systems

- **Prometheus** (`type = prometheus`)

### File and Web Formats

- **JSON Handlers**:

  - Web JSON (`type = web-json`) — Fetches JSON from a URL.
  - App JSON (`type = app-json`) — Executes a command that returns JSON.
  - File JSON (`type = file-json`) — Reads JSON from a local file.

- **XML Handlers**:

  - Web XML (`type = web-xml`) — Fetches XML from a URL.
  - App XML (`type = app-xml`) — Executes a command that returns XML.
  - File XML (`type = file-xml`) — Reads XML from a local file.

- **CSV Handlers**:

  - Web CSV (`type = web-csv`) — Fetches CSV from a URL.
  - File CSV (`type = file-csv`) — Reads CSV from a local file.
  - Standard Output CSV (`type = stdout-csv`) — Executes a command and processes its CSV output.

- **Standard Output**:
  - (`type = stdout`) — Executes a command and processes its standard output.

This modular approach allows you to work seamlessly with a variety of data sources and formats under a unified, consistent API.

---

## Installation

1. **Clone or Download** the repository:

   ```bash
   git clone https://github.com/yourusername/federaliser.git
   cd federaliser
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. Ensure your web server document root points to the `./public` directory.

4. Configure URL rewriting to route all requests to `index.php` (see [Web Server Configuration](#web-server-configuration)).

---

## Configuration

1. **Copy Example Configuration**:

   ```bash
   cp example-config.ini config.ini
   ```

2. **Edit `config.ini`** to define your data sources. For example:
   ```ini
   [example_mysql]
   source       = mysql.example.com
   port         = 3306
   type         = mysql
   identifier   = mysql-endpoint
   username     = dbuser
   password     = secret
   default_db   = mydatabase
   query        = SELECT id, name, stat1, stat2, stat3 FROM example_table
   ```

**Key Configuration Options**:

- **source**: Database/endpoint host, URL, or file path.
- **port**: Connection port.
- **type**: One of `mysql`, `mssql`, `redshift`, `prometheus`, `web-json`, `file-json`, `web-xml`, `file-xml`, `web-csv`, `file-csv`, etc.
- **identifier**: Unique identifier for URL routing.
- **username**, **password**: Credentials.
- **default_db**: Default database (for SQL sources).
- **query**: SQL query or command for data extraction.

---

## Usage

Access Federaliser via your browser or an HTTP client using:

```
http://<YOUR_HOST>/<identifier>
```

The output format is determined by the endpoint:

- **Default**: JSON (suitable for Telegraf or general use).
- **Prometheus/OpenMetrics**:
  ```
  http://<YOUR_HOST>/<identifier>/prometheus
  ```

### JSON / Telegraf Format

Access using:

```
http://<YOUR_HOST>/<identifier>
```

### Prometheus/OpenMetrics Format

Access using:

```
http://<YOUR_HOST>/<identifier>/prometheus
```

---

## Exporter Formats

Federaliser supports multiple export formats for different use cases.

### JSON Exporter

- Outputs data as a JSON object.
- Suitable for integration with Telegraf, custom dashboards, or any JSON-based pipeline.

## Prometheus Exporter Notes

For Prometheus/OpenMetrics export:

- The **last column** in your data must represent the metric value.
- All **preceding columns** are treated as labels. The column names become label keys, and their corresponding values become label values.
- Example format:
  ```
  metric_name{label1="value1",label2="value2"} metric_value
  ```
- If multiple rows are returned, each row is output as a separate metric line.

### Example Query for Prometheus Format

For a MySQL data source:

```ini
[example_mysql]
source       = mysql.example.com
port         = 3306
type         = mysql
identifier   = mysql-metrics
username     = dbuser
password     = secret
default_db   = mydatabase
query        = SELECT host, status, COUNT(*) as total FROM connections GROUP BY host, status;
```

Access the Prometheus-formatted output via:

```
http://<YOUR_HOST>/mysql-metrics/prometheus
```

This would produce output like:

```
mysql_metrics{host="server1", status="active"} 42
mysql_metrics{host="server2", status="idle"} 17
```

---

## Extending Federaliser

Federaliser is designed to be extensible. You can add new Data Handlers and Exporters to support additional data sources and output formats.

---

### Adding New Data Handlers

1. **Create a New Handler Class**:

   - Inherit from `AbstractHandler`.
   - Implement the `handle()` method.
   - Place your class in the `Federaliser\\Dataformats` namespace.

   **Example**:

   ```php
   namespace Federaliser\\Dataformats;

   class CustomHandler extends AbstractHandler {
       public function handle(): array {
           // Implement your custom data fetching and processing logic here
           $data = [];
           return $this->normaliseArray($data);
       }
   }
   ```

2. **Register the Handler** in `HandlerFactory`:

   - Open `HandlerFactory.php`.
   - Add a new case for your handler:

   ```php
   case 'custom-type':
       return new CustomHandler($config);
   ```

3. **Configuration**:
   In your `config.ini`, define the new handler like this:
   ```ini
   [example_custom]
   source       = http://example.com/data
   type         = custom-type
   identifier   = custom-metrics
   ```

---

### Creating Custom Exporters

1. **Create a New Exporter Class**:

   - Extend `AbstractExporter`.
   - Implement the `export()` method.
   - Place your class in the `Federaliser\\Exporters` namespace.

   **Example**:

   ```php
   namespace Federaliser\\Exporters;

   class CustomExporter extends AbstractExporter {
       public static function export($data, $statusCode, $additionalConfig): void {
           header('Content-Type: application/custom-format');
           echo custom_format_encode($data);
       }
   }
   ```

2. **Register the Exporter**:

   - Update `Exporter::identify()` to recognise your new exporter type.
   - Add routing logic in `Application` to handle the new export format.

3. **Accessing the New Exporter**:
   In your browser:
   ```
   http://<YOUR_HOST>/<identifier>/custom-format
   ```

---

### Registering and Using New Components

- **Update `HandlerFactory`** to include the new handler type and class.
- **Update `Exporter::identify()`** for new exporters.
- **Add routing** for the new exporter type in `Application`.

This ensures the new components are properly integrated and accessible via HTTP endpoints.

---

## Web Server Configuration

To route all requests to `index.php`, set up the following rewrite rules:

### Nginx

In your server block:

```nginx
location / {
    try_files $uri $uri/ /index.php;
}
```

### Apache

In your `.htaccess` file:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

---

## Configuring Prometheus

Add the following section to your `prometheus.yml`:

```yaml
- job_name: "<identifier>"
  static_configs:
    - targets: ["<YOUR_HOST>"]
  metrics_path: "/<identifier>/prometheus"
```

If using HTTPS, add:

```yaml
scheme: https
```

After updating, reload Prometheus to apply the changes:

```bash
systemctl reload prometheus
```

---

## Configuring Telegraf

Create a configuration file (e.g., `federaliser.conf`) in `/etc/telegraf/telegraf.d/` with the following content:

```toml
[[inputs.http]]
  name = "<identifier>"
  urls = ["http://<YOUR_HOST>/<identifier>"]
  method = "GET"
  response_timeout = "55s"
  interval = "1m"
```

Then reload Telegraf:

```bash
systemctl reload telegraf
```

---

## Admin Interface

Federaliser includes an optional **Admin Interface** accessible at `/admin`. This provides full CRUD capabilities for configurations.

### Features

- Create, Read, Update, and Delete (CRUD) configurations directly from the browser.
- Useful for managing complex environments with multiple data sources.

### Security Notice

- The `/admin/` area **does not** include built-in authentication or authorisation mechanisms.
- It is highly recommended to **secure this endpoint** with web server-level authentication if using in a production environment.
- Alternatively, if not needed, **delete the `/public/admin/` folder** to prevent unauthorised access.

---

## Troubleshooting

- **HTTP 500 Errors**: Where Federaliser can, it will give a JSON object with detailed information, otherwise, check the error.log for more information.
- Verify that your `config.ini` settings (connection details, credentials, query syntax) are correct.
- Ensure external data sources are accessible and returning valid data.
- Use the admin interface to debug and validate configurations.

---

## Contribution and Feedback

We welcome contributions!

- Fork the repository and submit pull requests.
- For bug reports or feature requests, please open an issue on GitHub.

---

> _"Consistent data, integrated effortlessly."_

---

If you need any more sections or modifications, let me know!
