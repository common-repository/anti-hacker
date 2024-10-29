=== Anti-Hacker - All-In-One Security (hide admin, waf, brute force blocker, vulnerability protection) ===

Plugin Name: Anti-Hacker, hide admin and WAF
Plugin URI: http://wordpress.org/plugins/wp-anti-hacker/
Description: This plugin protects your Wordpress against hackers attacks, hiding sensitive information that would be used to exploit your site, detecting and fixing weak security configuration and detecting and blocking vulnerability scanners.  
Tags: hacker,security,firewall,waf,hide,antivirus,wp-login,wp-admin,hide wordpress,hide wp,security plugin,vulnerability,scanner,brute-force
Version: 0.5.2
Author: AHT Security
Author URI: https://ahtsecurity.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Text Domain: wp-anti-hacker
Domain Path: /languages
Network: true
Requires at least: 4.3
Tested up to: 6.3.2
Stable tag: 0.5.0
Requires PHP: 5.6

Anti-Hacker protects your Wordpress against hackers attacks, hiding sensitive information that would be used to exploit your site, detecting and fixing weak security configuration and detecting and blocking vulnerability scanners.  


== Description ==

**Anti-Hacker** is a **WordPress Security plugin**. It gives you the best security solutions with its powerful and easy-to-use features. Without physically changing any directory or file, Anti-Hacker can take your website’s security to the next level with the ultimate wordpress protection technology.
Our team have worked with vulnerabilities scanner for long time, and now, we have created a thecnology to protect the other side, making the life of hackers really hard, and force them to choose a new target that is not you.

The plugin avoid vulnerability scanner to detect yor wordpress information and sensitive data, as version, themes, plugins, valid users and more.

**Anti-Hacker** also offer the protections bellow:
* Brute Force attacks
* Hide wp-admin
* XML-RPC attacks
* XSS, SQL Injection, PHP Injection, CMD Injection and Transversal Directory
* Detect and block vulnerability scanner activities
* HTTP Header level attack
* and more.

It hides the WP sensitive information, common paths, plugins, and themes paths, users, offering the **best protection against real hacker and bots attacks**.

Note! **No file or directory is physically changed**. All the changes are made by server rewrite rules without affecting the SEO or the loading speed.

**Anti-Hacker** works with other security plugins without any problem.

Anti-Hacker is compatible with all servers, hosting services, and also supports WP Multisite.

Over 90,000 hacking attacks per minute strike WordPress sites and WordPress hosting around the world, hitting not only large corporate websites packed with sensitive data, but also sites belonging to small businesses, independent entrepreneurs, and individuals running personal blogs.

Security of WordPress sites typically tops the list of concerns for new and experienced website owners alike.

For owners of WordPress sites, statistics like that one raises particular worries about the security not just of individual WordPress sites, but of WordPress itself.

== Installation ==
  
1. Upload the plugin folder to your /wp-content/plugins/ folder.
2. Go to the **Plugins** page and activate the plugin.
3. Activate all protections.

== Screenshots ==
1. Anti-Hacker settings screen. 

== Changelog ==

= 0.5.0 =
Option to hide wp-admin changing its name to new one

= 0.4.9 =
Added option to show Protected by Anti-Hacker in page footer
Fixed bug in brute force database log

= 0.4.8 =
Block user enumeration using wp-json

= 0.4.7 =
Update tested up version

= 0.4.6 =
Fixed xss protection bypass using admin-ajax.php

= 0.4.5 =
Removed curl
Changed function names to be unique
Fixed incorrect stable version

= 0.4.4 =
Added support to send logs to SIEM using syslog (only works if sockets module is active)

= 0.4.3 =
Encoding wp-content only if is usin default dir name.
Load configuration from config.json if it exist

= 0.4.2 =
Escaping output html in log area.

= 0.4.1 =
Fixed issues detected by Wordpress review.

= 0.0.4 =
Added event log menu to see the plugin detections and blocks.

= 0.0.3 =
Detects and block OWASP Zap, Nuclei and Nikto vulnerability scanner

= 0.0.2 =
Remove commented codes and filter user agent logs when detect and block possible scanner.

= 0.0.1 =
First versions available for WordPress.