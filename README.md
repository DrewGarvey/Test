#Read Me!

## EE Login

	user: support
	pass: devpass

## Getting Started

### Upgrading from Grunt <0.4.0

If you currently have an older version of grunt (say the 0.3.x one), you'll need to follow these instructions:

    npm uninstall -g grunt

Then run:

    npm install -g grunt-cli

### After you've got grunt-cli installed

1. Set up `package.json`: replace `giteeup` in the `sites` array with the appropriate info for your site. If you're working with an MSM site, replace the `short_name` with the appropriate short name.
2. Define your Mountee volume in the `Gruntfile.js` around line ~108.
3. Run `npm install` to install the required npm packages.

##MSM Support (doesn't include grunt… yet)

These instructions will help you set up a new MSM site locally that has already been created in production.

**This guide also assumes that you are converting a local repo from a single EE site into an MSM install.**

For clarity, we will call the existing site 'site1' (at site1.dev) and the new MSM site 'site2' (at site2.dev).

Before you start, grab a database dump from the production servers.

1. Create a directory called 'htdocs-site2' in your repo
2. Copy these files into your new 'htdocs-site2' directory
```
htdocs/.htaccess
htdocs/admin.php
htdocs/index.php
```
And symlink the `themes` directory from `htdocs/themes` into your new `htdocs-site2` directory (run this from within your new `htdocs-site2` directory):
```
$ ln -s ../htdocs/themes/ themes
```
3. Add these lines to htdocs-site2/admin.php in the 'Multiple Site Manager' section (~line 41)
```php
$assign_to_config['site_name']  = 'site_2_short_name';
$assign_to_config['cp_url'] = 'http://site2.dev/admin.php';
```

4. Add these lines to htdocs-site2/index.php in the 'Multiple Site Manager' section (~line 46)
```php
$assign_to_config['site_name']  = 'site_2_short_name';
$assign_to_config['cp_url'] = 'http://site2.dev/admin.php';
$assign_to_config['site_url'] = 'http://site2.dev';
```

5. Update your vhosts config with the new MSM site and restart Apache; use whatever ServerName you wish.
```apache
<VirtualHost *:80>
	ServerAdmin nowhere@loopback.edu
	DocumentRoot "{path-to-your-repo}/htdocs-site2"
	ServerName site2.dev
	ErrorLog "/private/var/log/apache2/site2-error_log"
	CustomLog "/private/var/log/apache2/site2-access_log" common
</VirtualHost>
```

6. Add the site2.dev domain to your `/etc/hosts` file
7. Import the production database into your local environment.
8. Run the database updates in mods.sql, replacing the list of site_ids with the actual site_ids of the MSM sites on which you're working.
9. Log into the EE control panel at the primary site on the MSM: `http://site1.dev/admin.php`
10. Switch to site2, and synchronize templates. If you run into an error, run this command in the terminal:
```
chmod 666 path/to/repo/templates
```

11. ???
12. PROFIT.
