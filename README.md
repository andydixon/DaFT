# Federaliser

**Federaliser** is a tool designed to collect data from multiple sources (MySQL, MSSQL, Redshift/Postgres, and Prometheus) and export it in a consistent format. It can produce JSON suitable for telegraf or a text-based output for Prometheus.

---

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Usage](#usage)
   - [JSON / telegraf Format](#json--telegraf-format)
   - [Prometheus Format](#prometheus-format)
5. [Prometheus Exporter Notes](#prometheus-exporter-notes)
6. [Web Server Configuration](#web-server-configuration)
   - [Nginx](#nginx)
   - [Apache](#apache)
7. [Configuring Prometheus](#configuring-prometheus)
8. [Configuring telegraf](#configuring-telegraf)
9. [Troubleshooting](#troubleshooting)

---

## Overview

Federaliser ensures all your data outputs are provided in a consistent format—so that no matter what your data source is, you can easily integrate it with your existing monitoring and metrics systems.

# Federaliser Data Formats

Federaliser ensures all your data outputs are provided in a consistent format—so that no matter what your data source or output style is, you can easily integrate it with your existing monitoring and metrics systems.

## Supported Database Source Types

- **MySQL** (`type = mysql`)
- **MSSQL** (`type = mssql`)
- **Redshift** (`type = redshift`)
  - _Note:_ PostgreSQL can also work under the `redshift` type.
- **Prometheus** (`type = prometheus`)

## Supported Data Format Handlers

In addition to traditional database queries, Federaliser now provides a flexible data formatting layer for non-database sources. These handlers are implemented in the `Federaliser\Dataformats` namespace and normalize data from various outputs into a consistent array format (which you can then encode as JSON):

## JSON Handlers

- **Web JSON** (`type = web-json`)  
  Fetches JSON data from a URL specified in the `source` field. If a `query` (a comma-separated list of keys) is provided, only those keys are extracted from the returned data.

- **App JSON** (`type = app-json`)  
  Executes a command-line application (specified by `source`) that returns JSON. The output is decoded, normalized, and optionally filtered based on provided keys.

- **File JSON** (`type = file-json`)  
  Reads JSON data from a local file (provided via `config['source']`), decodes it into an associative array, normalizes the array, and optionally filters the data based on query keys.

## XML Handlers

- **Web XML** (`type = web-xml`)  
  Retrieves XML data from a URL, converts it to an associative array, and filters the data based on specified query keys if needed.

- **App XML** (`type = app-xml`)  
  Executes an external application that outputs XML, converts the XML to an array, and applies filtering based on query keys.

## CSV Handlers

- **Web CSV** (`type = web-csv`)  
  Retrieves CSV data from a URL (provided via `config['source']`), parses it into an associative array using the first row as column headers, normalizes the array, and optionally filters the data based on query keys.

- **Standard Output CSV** (`type = stdout-csv`)  
  Executes an external application (using the command specified in `config['source']`) and captures its STDOUT as CSV data. The output is parsed with the first row as headers and then filtered according to any specified query keys.

- **File CSV** (`type = filecsv`)  
  Reads CSV data from a local file (the file path provided in `config['source']`), parses it by using the first row as column headers, normalizes the resulting array, and applies optional filtering based on query keys.

- **File XML** (`type = file-xml`)  
  Reads XML data from a local file (provided via `config['source']`), converts the XML into an associative array, and applies optional filtering based on query keys.

## Advanced Handlers

- **Standard Output** (`type = stdout`)  
  Runs an external application and captures its STDOUT. When a `query` is set (as a regex pattern with named capturing groups), the output is parsed into a structured multidimensional array.

This modular approach allows you to work seamlessly with a variety of data sources and formats—all under a unified, consistent API.

---

## Installation

1. **Clone or Download** this repository to your local environment.
2. Navigate to the project root folder and run:
   ```bash
   composer install
   ```
3. Ensure your document root (for Apache/Nginx) points to the `./public` directory of this project.
4. Configure URL rewriting so that all requests are routed to `index.php` (see the [Web Server Configuration](#web-server-configuration) section).

---

## Configuration

1. **Rename** `example-config.ini` to `config.ini`.
2. Edit `config.ini` to define your data sources. Each section corresponds to a specific data source or query. For example:

   ```ini
   [descriptive_name_this_is_myquery]
   source       = myquery-sandbox.lab.andydixon.home
   port           = 3306
   type           = mysql
   identifier     = mysql-endpoint
   username       = andy
   password       = changeme
   default_db     = db
   query          = SELECT id, name, stat1, stat2, stat3 FROM foo
   ```

3. **Explanation of keys**:
   - **source**: The database/endpoint host.
   - **port**: Connection port.
   - **type**: One of `mysql`, `mssql`, `redshift`, or `prometheus`.
   - **identifier**: A unique identifier that appears in the URL (e.g., `http://source/federaliser/identifier`).
   - **username**, **password**: Credentials for connecting to your data source.
   - **default_db**: The default database to connect to (for SQL databases).
   - **query**: The SQL query to run (if applicable).

---

## Example Configurations

Below are sample `config.ini` snippets for each supported data source and data format handler.

### MySQL or MySQL Compatible

```ini
[descriptive]
source    = mysql.example.com
port      = 3306
type     = mysql
default_db  = mydatabase
username   = dbuser
password     = secret
query      = SELECT * FROM users
```

### Microsoft SQL Server

```ini
[descriptive]
source   = mssql.example.com
port      = 1433
type     = mssql
default_db  = mydatabase
username   = dbuser
password     = secret
query    = SELECT* FROM customers
```

### RedShift

```ini
[descriptive]
source    = redshift-cluster-1.xyz.eu-west-2.redshift.amazonaws.com
port      = 5439
type     = redshift
default_db   = analytics
username    = awsuser
password   = awspassword
query     = SELECT * FROM sales LIMIT 5
```

### Prometheus

```ini
[descriptive]
source  = http://prometheus.xample.com
port      = 9090
type     = prometheus
query       = up
```

### Web JSON

```ini
[descriptive]
source    = https://api.example.com/data.json
type     = web-json
query      = id,name,status
```

### App JSON

```ini
[descriptive]
source    = /usr/local/bin/my-json-app --option=value
type      = app-json
query      = id,name,score
```

### Standard Output

```ini
[descriptive]
source    = /usr/local/bin/my-output-app -yverbose
type      = stdout
query       = /(?(@P<id>\\d+(P@<name?\w\))/
```

### Web XML

```ini
[descriptive]
source   = https://api.example.com/data.xml
type      = web-xml
query       = id,name
```

#### App XML

```ini
[descriptive]
source   = /usr/local/bin/my-html-app --flag
type       = app-xml
query        = id,name,status
```

---

## Usage

Once installed and configured:

1. **Access the tool** in your browser via:

   ```
   http://<YOUR_HOST>/<identifier>
   ```

   Replace `<identifier>` with the identifier you set in `config.ini`.

2. Federaliser will run the query or gather data and return JSON by default (suitable for telegraf or general parsing).

### JSON / telegraf Format

When accessed via:

```
http://<YOUR_HOST>/<identifier>
```

the data is presented as a JSON object. This is suitable for pushing to telegraf or consuming by any other JSON-based pipeline.

### Prometheus Format

When accessed via:

```
http://<YOUR_HOST>/<identifier>/prometheus
```

the data is presented in a Prometheus-compatible text format.

---

## Prometheus Exporter Notes

Federaliser supports special column name prefixes to help define metric types in Prometheus. If a column name is prefixed with four underscores, then the underscores are removed, and the key and value are stored as a label. By default, any other fields will have a label of 'column' with the value of the label being the column name.

> **Note**: The prefix is **not** removed for the JSON/telegraf exporter. They will appear as-is.

---

## Web Server Configuration

To ensure that all requests are properly routed to `index.php`, set up the following rewrite rules.

### Nginx

In your server block:

```
location / {
    try_files $uri $uri/ /index.php;
}
```

### Apache

A sample `.htaccess` file is included in the `public` folder:

```
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

---

## Configuring Prometheus

In the `prometheus.yml` file, add the following section:

```
  - job_name: '<identifier>'
    static_configs:
      - targets: ['<HOST_NAME>']
    metrics_path: '<identifier>/prometheus'
```

If you are connecting over https, add this between `job_name` and `static_configs`

```
    scheme: https
```

Then reload prometheus - for example:

```
systemctl reload prometheus
```

## Configuring telegraf

In `/etc/telegraf/telegraf.d`, create a file, eg `federaliser.conf` and add the following content:

```conf
[[inputs.httpjson]]
  # This is the name/measurement that shows up in InfluxDB
  name = "<identifier>"

  # List of endpoints to request
  servers = ["http://<HOST_NAME>/<identifier>"]

  # HTTP method
  method = "GET"

  # Timeout for the request (if your Telegraf version supports it)
  response_timeout = "55s"

  # Polling interval (you can override this in the main [agent] config too)
  interval = "1m"
```

Then, reload telegraf - eg `systemctl reload telegraf`

## Admin Interface

A basic administrative interface is provided in the `/admin` location. This interface offers full CRUD capabilities—allowing you to create, update, and delete configurations.

**Important Security Notice:**

- The `/admin/` area does **not** include any built-in authentication or authorization mechanisms.
- It is your responsibility to secure this endpoint if you choose to use it in a production environment.
- Alternatively, if you do not need this interface, it is recommended that you remove the `/public/admin/` folder entirely to prevent unauthorised access.

## Troubleshooting

- Federaliser returns an **HTTP 500 error** if something goes wrong (e.g., invalid query, database connection issues, configuration errors).
- Check your server’s log files (e.g., `error.log`) for more details.
- Verify your `config.ini` settings are correct, especially connection details and credentials.

---

> _“All your consistent data are belong to us.”_
