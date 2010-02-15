# Configuration

Article status [Draft] requires [Editing] Configuring the Framework, add remaining entries

Kohana adopts the **convention over configuration** philosophy. The goal is to minimize the amount of configuration that the end user has to do. To accomplish this goal, Kohana employs **sensible defaults** whenever possible and **simple to understand options** when necessary.

## Configuring Kohana

Configuration files are regular PHP files that contain a <code>$config</code> array. Each configuration option is specified with a key value pair. Items which can be blank are marked [optional].

base_url
: URL which points to the root of your application, eg: everything before <file>index</file> in the URL. **If this option is not set properly, Kohana will be unable to create any self-linking URL.**

index_page
: Filename of the <definition>front controller</definition>, usually <file>index</file>. If you <?php echo html::anchor('user_guide/general/routing#removing_index', 'remove the index page') ?> from the URL, this setting becomes optional.

url_suffix
: [optional] Filename suffix which should be dynamically added to any URL that Kohana generates. This setting is completely optional.

permitted_uri_chars
: List of characters that are allowed in URI strings. The default settings should work for almost every application. **If you change this option, the security of Kohana may diminish.**

locale
: Sets the system locale using any standard language abbreviation. By default, Kohana only ships with English (en_US) language files.

timezone
: [optional] If you would like your application to be localized to a timezone that is different than the server's timezone, enter a <?php echo html::anchor('http://php.net/timezones', 'supported timezone') ?> here.

autoload
: [optional] To maintain a small footprint, resources are typically loaded by your controllers. In applications where a resource is used in many controllers, you may specify resources to be loaded automatically. **Make sure that the resources are properly configured before attempting to autoload them.**

include_paths
: [optional] Array of additional paths, absolute or relative to <file>index</file>, that will be searched when looking for resources. Paths are searched in the order of this array. Please note that your application path will always be searched first, and the system path will always be searched last.

extension_prefix
: Filename prefix for class extension files. Please see the <?php echo html::anchor('user_guide/general/extensions', 'Extensions') ?> page for more information.

enable_hooks
: [optional] Globally enables or disables hooks. Please see the <?php echo html::anchor('user_guide/general/hooks', 'Hooks') ?> page for more information.

## Configuration Files

The following configuration files are reserved for Kohana components:

<file>config</file>
: Global Kohana configuration

<file>log</file>
: <?php echo html::anchor('user_guide/general/logging', 'Message Logging') ?> 

<file>mimes</file>
: <?php echo html::anchor('user_guide/general/mimes', 'Mime Types') ?> 

<file>routes</file>
: <?php echo html::anchor('user_guide/general/routes', 'Routing') ?> 

<file>cookie</file>
: <?php echo html::anchor('user_guide/helpers/cookie', 'Cookie Helper') ?> 

<file>database</file>
: <?php echo html::anchor('user_guide/libraries/database', 'Database Library') ?> 

<file>encryption</file>
: <?php echo html::anchor('user_guide/libraries/encryption', 'Encryption Library') ?> 

<file>pagination</file>
: <?php echo html::anchor('user_guide/libraries/pagination', 'Pagination Library') ?> 

<file>session</file>
: <?php echo html::anchor('user_guide/libraries/session', 'Session Library') ?> 

<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: configuration.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>