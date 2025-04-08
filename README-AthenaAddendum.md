# Athena

**Rewrite and add to main README**

Needs ODBC for PHP:

```
sudo apt-get install php-odbc
```

Athena is accessed via ODBC — you'll need to install Amazon's Athena ODBC driver on your server.

Download from AWS:
Amazon Athena ODBC Driver Downloads - https://docs.aws.amazon.com/athena/latest/ug/athena-odbc.html

Install it for your server OS (Linux, macOS, Windows).

During installation, note where the driver is installed and ensure it's registered correctly in your ODBC config:

On Linux: check /etc/odbcinst.ini

On Windows: use the ODBC Data Source Administrator.

## Check PHP ODBC support

php -m | grep odbc

may need:

```
extension=php_odbc.dll
```

in php.ini

## config.ini differences

type = athena
source = AwsDataCatalog
region = eu-west-2
workgroup = primary
output = s3://your-output-bucket/
identifier = athena_endpoint
query = <SQL or path to file as normal>
aws_access_key_id = YOUR_AWS_ACCESS_KEY_ID
aws_secret_access_key = YOUR_AWS_SECRET_ACCESS_KEY

nb the aws\_ are only needed if the server does not have an IAM role setup.

## S3 Bucket

User config needs to include

```json
{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Action": [
        "athena:*",
        "s3:GetObject",
        "s3:PutObject",
        "s3:ListBucket",
        "glue:*" // optional, if you’re using Glue Data Catalog
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ]
}
```
