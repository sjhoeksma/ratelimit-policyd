# ratelimit-policyd

A Sender rate limit policy daemon for Postfix.

## Credits

This project was forked from [bejelith/send_rate_policyd](https://github.com/bejelith/send_rate_policyd). All credits go to [Simone Caruso](http://www.simonecaruso.com).

## Purpose

This small Perl daemon limits the number of emails sent by users through your Postfix server, and store message quota in RDMS system (MySQL).

For a long time we were using Postfix-Policyd v1 (the old 1.82) in production instead, but that project was no longer maintained and the successor [PolicyD v2 (codename "cluebringer")](http://wiki.policyd.org/) got overly complex and badly documented. Also, PolicyD seems to have been abandoned since 2013.

With this piece of code you can setup a send rate per users or sender domain (via SASL username) on daily/weekly/monthly basis and store the data in MySQL.


## Installation

Recommended installation:

```bash
$ cd /opt/
$ git clone https://github.com/onlime/ratelimit-policyd.git ratelimit-policyd
$ cd ratelimit-policyd
$ chmod +x install.sh
$ ./install.sh
```

Create the DB schema and user:

```bash
$ mysql -u root -p < mysql-schema.sql
```

```sql
GRANT USAGE ON *.* TO policyd@'localhost' IDENTIFIED BY '********';
GRANT SELECT, INSERT, UPDATE, DELETE ON policyd.* TO policyd@'localhost';
```

Adjust configuration options in ```daemon.pl```:

```perl
### CONFIGURATION SECTION
my @allowedhosts    = ('127.0.0.1', '10.0.0.1');
my $LOGFILE         = "/var/log/ratelimit-policyd.log";
my $PIDFILE         = "/var/run/ratelimit-policyd.pid";
my $SYSLOG_IDENT    = "ratelimit-policyd";
my $SYSLOG_LOGOPT   = "ndelay,pid";
my $SYSLOG_FACILITY = LOG_MAIL|LOG_INFO;
chomp( my $vhost_dir = `pwd`);
my $port            = 10032;
my $listen_address  = '127.0.0.1'; # or '0.0.0.0'
my $s_key_type      = 'email'; # domain or email
my $dsn             = "DBI:mysql:policyd:127.0.0.1";
my $db_user         = 'policyd';
my $db_passwd       = '************';
my $db_table        = 'ratelimit';
my $db_quotacol     = 'quota';
my $db_tallycol     = 'used';
my $db_updatedcol   = 'updated';
my $db_expirycol    = 'expiry';
my $db_wherecol     = 'sender';
my $deltaconf       = 'daily'; # hourly|daily|weekly|monthly
my $defaultquota    = 1000;
my $sql_getquota    = "SELECT $db_quotacol, $db_tallycol, $db_expirycol FROM $db_table WHERE $db_wherecol = ? AND $db_quotacol > 0";
my $sql_updatequota = "UPDATE $db_table SET $db_tallycol = $db_tallycol + ?, $db_updatedcol = NOW(), $db_expirycol = ? WHERE $db_wherecol = ?";
my $sql_updatereset = "UPDATE $db_table SET $db_tallycol = ?, $db_updatedcol = NOW(), $db_expirycol = ? WHERE $db_wherecol = ?";
my $sql_insertquota = "INSERT INTO $db_table ($db_wherecol, $db_quotacol, $db_tallycol, $db_expirycol) VALUES (?, ?, ?, ?)";
### END OF CONFIGURATION SECTION
```

Default configuration should be fine. Just don't forget to paste your DB password in ``$db_password``.

Now, start the daemon:

```bash
$ service ratelimit-policyd start
```

## Testing

Check if the daemon is really running:

```bash
$ netstat -tl | grep 10032
tcp        0      0 localhost.localdo:10032 *:*                     LISTEN

$ cat /var/run/ratelimit-policyd.pid
30566

$ ps aux | grep daemon.pl
postfix  30566  0.4  0.1 176264 19304 ?        Ssl  14:37   0:00 /opt/send_rate_policyd/daemon.pl

$ pstree -p | grep ratelimit
init(1)-+-/opt/ratelimit-(11298)-+-{/opt/ratelimit-}(11300)
        |                        |-{/opt/ratelimit-}(11301)
        |                        |-{/opt/ratelimit-}(11302)
        |                        |-{/opt/ratelimit-}(14834)
        |                        |-{/opt/ratelimit-}(15001)
        |                        |-{/opt/ratelimit-}(15027)
        |                        |-{/opt/ratelimit-}(15058)
        |                        `-{/opt/ratelimit-}(15065)

```

Print the cache content (in shared memory) with update statistics:

```bash
$ service ratelimit-policyd status
Printing shm:
Domain		:	Quota	:	Used	:	Expire
Threads running: 6, Threads waiting: 2
```

## Postfix Configuration

Modify the postfix data restriction class ```smtpd_data_restrictions``` like the following, ```/etc/postfix/main.cf```:

```
smtpd_data_restrictions = check_policy_service inet:$IP:$PORT
```

sample configuration (using ratelimitpolicyd as alias as smtpd_data_restrictions does not allow any whitespace):

```
smtpd_restriction_classes = ratelimitpolicyd
ratelimitpolicyd = check_policy_service inet:127.0.0.1:10032

smtpd_data_restrictions =
        reject_unauth_pipelining,
        ratelimitpolicyd,
        permit
```

If you're sure that ratelimit-policyd is really running, restart Postfix:

```
$ service postfix restart
```