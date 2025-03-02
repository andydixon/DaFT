# Federaliser

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
14. [Using Docker](#using-docker-for-federaliser)
    - [Using in Production](#4-deploying-in-production)
15. [Troubleshooting](#troubleshooting)
16. [Contribution and Feedback](#contribution-and-feedback)

---

## Overview

Federaliser ensures that all your data outputs are provided in a consistent format. No matter what your data source or output style is, Federaliser makes it easy to integrate your data with existing monitoring and metrics systems.

---

## What's New

### Refactoring and Improvements

- **Enhanced Security**:
  - URL and file path validation to prevent SSRF and directory traversal attacks.
  - Use of prepared statements and input sanitisation where applicable.
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

- **Prometheus/OpenMetrics** (`type = prometheus`)

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

### JSON Format

Access using:

```
http://<YOUR_HOST>/<identifier>
```

### JSON Format for Telegraf

Access using:

```
http://<YOUR_HOST>/<identifier>/telegraf
```

### Prometheus/OpenMetrics Format

Access using:

```
http://<YOUR_HOST>/<identifier>/prometheus
```

---

## Exporter Formats

Federaliser supports multiple export formats for different use cases.

### JSON Exporter (default)

- Outputs data as a JSON object.
- Suitable for integration with custom dashboards, or any JSON-based pipeline.

### Telegraf Exporter

- Outputs data as a single-level JSON object suitable for Telegraf.
- **Column 0** becomes the key, and **Column 1** becomes the value.
- Example:
  ```json
  {
    "cpu_usage": 5,
    "memory_usage": 27,
    "disk_usage": 32
  }
  ```
- Throws `400 Bad Request` if:
  - More than 2 columns are present.
  - Non-numeric value is found.

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

### **Advanced JSON Handling in Web & App Handlers**

The `web-json` and `app-json` handlers in **Federaliser** allow for powerful JSON extraction and filtering, enabling users to:

- Extract **specific paths** from deeply nested JSON.
- Select only **certain fields** from the extracted data.
- Maintain **full backward compatibility** (default behavior is unchanged).

---

## **Configurable JSON Extraction Options**

| Key         | Description                                          | Example Value                       |
| ----------- | ---------------------------------------------------- | ----------------------------------- |
| `source`    | URL (for `web-json`) or command (for `app-json`)     | `https://api.example.com/data.json` |
| `type`      | The handler type (`web-json` or `app-json`)          | `web-json`                          |
| `json_path` | _(Optional)_ Extract a **specific JSON path**        | `data.items`                        |
| `fields`    | _(Optional)_ Select **specific fields** from results | `id,name,score`                     |

---

## **How JSON Path Extraction Works**

The `json_path` option lets you specify **a dot-separated path** within a JSON response.

For example, given this API response:

```
{
    "status": "success",
    "data": {
        "items": [
            { "id": 1, "name": "Alice", "score": 95 },
            { "id": 2, "name": "Bob", "score": 88 }
        ]
    }
}
```

You can extract **only `data.items`** using this configuration:

```
source = "https://api.example.com/data.json"
type = "web-json"
json_path = "data.items"
```

**Returned Output:**

```
[
    { "id": 1, "name": "Alice", "score": 95 },
    { "id": 2, "name": "Bob", "score": 88 }
]
```

---

## **Field Filtering: Selecting Only the Data You Need**

The `fields` option allows you to select **only certain fields** from the extracted data.

For example, with this configuration:

```
source = "https://api.example.com/data.json"
type = "web-json"
json_path = "data.items"
fields = "id,name"
```

**Returned Output:**

```
[
    { "id": 1, "name": "Alice" },
    { "id": 2, "name": "Bob" }
]
```

---

## **Handling Single JSON Objects**

If the extracted `json_path` points to a **single JSON object**, it will be returned **as an object** instead of an array.

**Example JSON Response**

```
{
    "metadata": {
        "info": {
            "version": "1.0",
            "author": "Admin"
        }
    }
}
```

**Configuration**

```
source = "https://api.example.com/data.json"
type = "web-json"
json_path = "metadata.info"
```

**Returned Output:**

```
{
    "version": "1.0",
    "author": "Admin"
}
```

---

## **Using App JSON Handler**

The `app-json` handler **works exactly the same**, but instead of fetching JSON from a web URL, it **runs a shell command** that outputs JSON.

**Example Configuration**

```
source = "/usr/local/bin/my-json-app --option=value"
type = "app-json"
json_path = "response.results"
fields = "id,score"
```

**Expected Output:**

```
[
    { "id": 1, "score": 95 },
    { "id": 2, "score": 88 }
]
```

---

## **What Happens If a Path Doesn’t Exist?**

If the specified `json_path` does not exist in the response:

- Instead of returning an error, it will **return an empty array (`[]`)**.

If the specified `fields` don’t exist in the extracted data:

- Those fields will **be ignored** without errors.

This ensures **consistent behavior** and prevents API failures due to missing keys.

---

# **Advanced XML Handling in Web & App Handlers**

The `web-xml` and `app-xml` handlers in **Federaliser** provide advanced XML extraction and filtering capabilities, allowing users to:

- Extract **specific paths** from deeply nested XML.
- Select only **certain fields** from the extracted data.
- Convert XML to **JSON-like structures** for consistent data handling.
- Maintain **full backward compatibility** (default behavior is unchanged).

---

## ** How XML Path Extraction Works**

The `xml_path` option allows you to extract **specific parts** of an XML response using **dot notation**.

For example, given this XML response:

```
<response>
    <status>success</status>
    <data>
        <items>
            <item>
                <id>1</id>
                <name>Alice</name>
                <score>95</score>
            </item>
            <item>
                <id>2</id>
                <name>Bob</name>
                <score>88</score>
            </item>
        </items>
    </data>
</response>
```

You can extract **only `<data><items>`** using this configuration:

```
source = "https://api.example.com/data.xml"
type = "web-xml"
xml_path = "data.items.item"
```

**Returned Output (Converted to JSON-like format):**

```
[
    { "id": "1", "name": "Alice", "score": "95" },
    { "id": "2", "name": "Bob", "score": "88" }
]
```

---

## ** Field Filtering: Selecting Only the Data You Need**

The `fields` option allows you to extract only **certain fields** from the extracted data.

For example, with this configuration:

```
source = "https://api.example.com/data.xml"
type = "web-xml"
xml_path = "data.items.item"
fields = "id,name"
```

**Returned Output:**

```
[
    { "id": "1", "name": "Alice" },
    { "id": "2", "name": "Bob" }
]
```

---

## ** Handling Single XML Elements**

If the extracted `xml_path` points to a **single XML element**, it will be returned as an **object** instead of an array.

**Example XML Response**

```
<response>
    <metadata>
        <info>
            <version>1.0</version>
            <author>Admin</author>
        </info>
    </metadata>
</response>
```

**Configuration**

```
source = "https://api.example.com/data.xml"
type = "web-xml"
xml_path = "metadata.info"
```

**Returned Output:**

```
{
    "version": "1.0",
    "author": "Admin"
}
```

---

## **🛠 Using App XML Handler**

The `app-xml` handler **works exactly the same**, but instead of fetching XML from a web URL, it **runs a shell command** that outputs XML.

**Example Configuration**

```
source = "/usr/local/bin/my-xml-app --option=value"
type = "app-xml"
xml_path = "response.results.item"
fields = "id,score"
```

**Expected Output:**

```
[
    { "id": "1", "score": "95" },
    { "id": "2", "score": "88" }
]
```

---

## ** What Happens If a Path Doesn’t Exist?**

If the specified `xml_path` does not exist in the response:

- Instead of returning an error, it will **return an empty array (`[]`)**.

If the specified `fields` don’t exist in the extracted data:

- Those fields will **be ignored** without errors.

This ensures **consistent behavior** and prevents API failures due to missing XML elements.

---

## ** Backward Compatibility**

- **If you don’t use `xml_path` or `fields`, the handler works exactly as before.**
- **No breaking changes for existing users!**

For example, this configuration:

```
source = "https://api.example.com/data.xml"
type = "web-xml"
```

Will still return the **entire XML response** as a **JSON-like array**.

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
  urls = ["http://<YOUR_HOST>/<identifier>/telegraf"]
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

# Using Docker for Federaliser

Federaliser can be deployed as a **Docker container** for both **production** and **development** environments. This section provides detailed instructions for building, configuring, and running the Docker image.

---

## Prerequisites

Ensure you have the following installed:

- [Docker](https://docs.docker.com/get-docker/)
- [Docker Compose](https://docs.docker.com/compose/install/) (optional, but recommended for development)

Check your installations:

```bash
docker --version
docker-compose --version
```

---

## Configuration Management

Federaliser uses an **INI configuration file** located within the container:

```
/var/www/html/config.ini
```

This file is **mapped from the host to the Docker container**, allowing you to:

- Edit configurations on the host without rebuilding the image.
- Manage configurations in version control.

---

## Building the Docker Image

1. **Clone the Repository** (if you haven't already):

```bash
git clone https://github.com/your-repo/federaliser.git
cd federaliser
```

2. **Build the Docker Image**:

```bash
docker build -t federaliser-app .
```

- The `-t` flag assigns a name (`federaliser-app`) to the image.
- This builds the image using the `Dockerfile` in the current directory.

---

## Running in Production

In production, the focus is on **performance, security, and scalability**. A prebuilt image can be started with:

```
docker run -v /path/to/config.ini:/var/www/html/config.ini nddxn/federaliser
```

Alternatively, a container can be built with the bleeding-edge version:

### 1. Start the Container:

```bash
docker run -d \
  -p 8080:80 \
  --name federaliser \
  -v $(pwd)/config/config.ini:/var/www/html/config.ini \
  federaliser-app
```

### 2. Configuration:

- The `-v` flag maps the configuration file to the container.
- Edit `config/config.ini` on the host to change configurations.
- Changes are instantly reflected without rebuilding the image.

### 3. Scaling with Docker Compose:

For easier scaling and management, use Docker Compose:

```yaml
version: "3.7"

services:
  app:
    image: federaliser-app:latest
    ports:
      - "80:80"
    volumes:
      - ./config/config.ini:/var/www/html/config.ini
    restart: always
```

**Start with**:

```bash
docker-compose up -d
```

### 4. Deploying in Production:

- Use a **reverse proxy** like **Nginx** or **Traefik** to handle SSL and load balancing.
- Consider using **Kubernetes** or **AWS ECS** for container orchestration.

### 5. Environment Variables:

Override configuration using environment variables:

```bash
docker run -d \
  -e DB_HOST="prod-db.example.com" \
  -e DB_USER="prod_user" \
  -e DB_PASS="securepassword" \
  -p 80:80 \
  federaliser-app
```

---

## Running in Development

Development mode uses **volume mapping** for hot-reloading and easier debugging.

### 1. Start the Container:

```bash
docker run -d \
  -p 8080:80 \
  --name federaliser-dev \
  -v $(pwd)/config/config.ini:/var/www/html/config.ini \
  -v $(pwd)/src:/var/www/html/src \
  -v $(pwd)/public:/var/www/html/public \
  federaliser-app
```

### 2. Configuration:

- The `-v` flags map the `src`, `public`, and `config` directories for hot-reloading.
- Changes made on the host are instantly reflected inside the container.
- Ideal for rapid development and testing.

### 3. Using Docker Compose:

```yaml
version: "3.7"

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    ports:
      - "8080:80"
    volumes:
      - ./config/config.ini:/var/www/html/config.ini
      - ./src:/var/www/html/src
      - ./public:/var/www/html/public
    environment:
      - APP_ENV=development
    restart: always
```

**Start with**:

```bash
docker-compose up -d
```

**Stop with**:

```bash
docker-compose down
```

### 4. Debugging and Accessing the Container:

```bash
docker exec -it federaliser-dev bash
```

### 5. Install Dependencies:

After starting the container, install dependencies with:

```bash
docker exec -it federaliser-dev composer install
```

---

## Troubleshooting and Tips

### 1. Viewing Logs

To view application logs:

```bash
docker logs -f federaliser-app
```

Or with `docker-compose`:

```bash
docker-compose logs -f app
```

### 2. Rebuilding the Image

If you make changes to the `Dockerfile` or dependencies, rebuild the image:

```bash
docker-compose build
```

### 3. Clearing Cache

Clear PHP's OPcache and application cache by restarting the container:

```bash
docker-compose restart app
```

### 4. Stopping and Removing Containers

```bash
docker-compose down
```

### 5. Updating Dependencies

```bash
docker-compose exec app composer update
```

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
