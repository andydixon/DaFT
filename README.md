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
10. [Learn to Write Documentation](#learn-to-write-documentation)

---

## Overview

Federaliser ensures all your data outputs are provided in a consistent format—so that no matter what your data source is, you can easily integrate it with your existing monitoring and metrics systems.

Supported data source types:

- **MySQL** (`type = mysql`)
- **MSSQL** (`type = mssql`)
- **Redshift** (`type = redshift`)
  - Postgres can also work under the `redshift` type
- **Prometheus** (`type = prometheus`)

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
   hostname       = myquery-sandbox.lab.andydixon.home
   port           = 3306
   type           = mysql
   identifier     = mysql-endpoint
   username       = andy
   password       = changeme
   default_db     = db
   query          = SELECT id, name, stat1, stat2, stat3 FROM foo
   ```

3. **Explanation of keys**:
   - **hostname**: The database/endpoint host.
   - **port**: Connection port.
   - **type**: One of `mysql`, `mssql`, `redshift`, or `prometheus`.
   - **identifier**: A unique identifier that appears in the URL (e.g., `http://HOSTNAME/federaliser/identifier`).
   - **username**, **password**: Credentials for connecting to your data source.
   - **default_db**: The default database to connect to (for SQL databases).
   - **query**: The SQL query to run (if applicable).

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

```
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

## Troubleshooting

- Federaliser returns an **HTTP 500 error** if something goes wrong (e.g., invalid query, database connection issues, configuration errors).
- Check your server’s log files (e.g., `error.log`) for more details.
- Verify your `config.ini` settings are correct, especially connection details and credentials.

---

## Learn to Write Documentation

> _“All your data are belong to us. In a consistent format, obviously.”_
