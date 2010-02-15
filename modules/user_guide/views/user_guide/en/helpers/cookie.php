Article status [Draft] requires [Editing] Comments and corrections
# Cookie Helper
Provides methods for working with COOKIE data.

## Configuration
Default settings for Cookies are specified in <file>application/config/cookie</file>. You may override
these settings by passing parameters to the helper.

prefix
: A prefix may be set to avoid name collisions. Default is <code>''</code>.

domain
: A valid domain, for which the Cookie may be written. Default is <code>''</code> (equivalent to *localhost*.)
For site-wide Cookies, prefix your domain with a period <code>.example.com</code>

path
: A valid path for which the Cookie may be written. Default is the root directory <code>'/'</code>

expire
: The Cookie lifetime. Set the number of seconds the Cookie should persist, until expired by the browser, starting from when the Cookie is set.
Note: Set to *0* (zero) seconds for a Cookie which expires when the browser is closed.

secure
: The Cookie will **only** be allowed over a secure transfer protocol (HTTPS). Default is <code>FALSE</code>

httponly
: The Cookie can be accessed via HTTP only, and not via client scripts. Default is <code>FALSE</code>
Note: Requires at least PHP version 5.2.0

## Methods

### Set a Cookie.
<code>cookie::set()</code> accepts multiple parameters, Only the cookie name and value are required.<br />
You may pass parameters to this method as discrete values:

    cookie::set($name, $value, $expire, $path, $domain, $secure, $httponly, $prefix);

Or you may pass an associative array of values as a parameter:

    $cookie_params = array(
                   'name'   => 'Very_Important_Cookie',
                   'value'  => 'Choclate Flavoured Mint Delight',
                   'expire' => '86500',
                   'domain' => '.example.com',
                   'path'   => '/',
                   'prefix' => 'one_',
                       );
    cookie::set($cookie_params);


*****

### Get a Cookie
<code>cookie::get()</code> accepts multiple parameters, Only the cookie name is required.

    $cookie_value = cookie::get($cookie_name, $prefix, $xss_clean = FALSE);

Setting the third parameter to <code>TRUE</code> will filter the Cookie for unsafe data.

Returns <code>FALSE</code> if the Cookie item does not exist.

*****

### Delete a Cookie
<code>cookie::delete()</code> accepts multiple parameters, Only the cookie name is required.<br />
This method is identical <code>cookie::set()</code> but sets the cookie value to <code>''</code>. effectively deleting it.

    cookie::delete('stale_cookie');

<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: cookie.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>