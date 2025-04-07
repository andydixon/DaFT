# Additional, requiring adding into README after rewriting

## Prometheus Exporter

If the data passed to the exporter has a field called `__backfill` it will set that as a unix timestamp to backfill data. Either a Unix timestamp or a MySQL timestamp (YYYY-mm-dd HH;ii:ss) are accepted. Will default to the current unix timestamp if something breaks. If using datasources that need fields specified (eg json,xml), __backfill needs to be added to the list.
@change: Specify field to be the backfill field in config.ini


## Database Helpers

If the `query` is prefixed with <<, then it will convert the rest of the string as a full path and filename of an sql file. For example:

```inifile
query = <</shared/sql/dataSnapshotQuery.sql
```


## Helpers

Some useful scripts which can be used with app-json. This needs to be documented.

## Webex notification

This needs to be part of a larger alerting section, but for now, creating an alerting.ini file next to config.ini with:

```
bot=<bot token>
space=<space ID>
```

Will alert to that specific webex space. Need to add other methods.

## Replication check

If this is set:
```
checkReplication=true
```
Then replication is checked, and if a slave is >60s out of sync, it will report the issue. If there are no records, then it will report it once, and mute itself until replication is sorted, or data is returned.


## Alerting Tolerances

**This needs to be replicated into other Handlers, perhaps merged into the Abstract, and replicated across all other data sources**

If the following value is specified:

```
alertTolerance=10
``

Then, there are no alerts raised until the 11th scrape returns back no information. If a scrape during that time occurs, then the counter is reset.