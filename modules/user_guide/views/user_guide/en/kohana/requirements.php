# Basic Requirements

Article status [Draft] requires [Editing]

Kohana will run in almost any environment with minimal configuration. There are a few minimum server requirements:

 1. Server with [Unicode](http://unicode.org/) support
 2. PHP version &gt;= 5.1 with PDO support
 3. An HTTP server. Kohana is known to work with: Apache 1.3+, Apache 2.0+, lighttpd, and MS IIS

Optionally, if you wish to use a database with Kohana, you will need a database server. Kohana use <?php echo html::anchor('http://php.net/pdo', 'PDO') ?> to connect to your database. All of the PDO <?php echo html::anchor('http://php.net/manual/en/ref.pdo.php#pdo.drivers', 'supported databases') ?> are supported by Kohana, provided that your server has the driver available.

## Recommended Extensions

 1. [mbstring](http://php.net/mbstring) will dramatically speed up Kohana's UTF-8 functions
 2. [mcrypt](http://php.net/mcrypt) will dramatically speed up Kohana's encryption and hashing routines

<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: requirements.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>