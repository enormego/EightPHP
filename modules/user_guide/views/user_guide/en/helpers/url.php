Article status [Draft] requires [Editing] Amendments and corrections
# Url Helper
Provides methods for working with URL (s)

## Methods

### Base
<code>url::base()</code> accepts one **optional** parameter.
Returns the *base_url* defined in <file>config</file>

    // base_url = "http://localhost/kohana/"
    echo url::base();

Generates 

    http://localhost/kohana/

****

### Site
<code>url::site()</code> accepts one **mandatory** parameter.
Returns a url, based on the *base_url*, *index_page*, *url_suffix* defined in <file>config</file> and the url segments passed to the method.

    // base_url = 'http://localhost/kohana/'
	// index_page = 'index.php'
	// url_suffix = ''
	$uri = 'welcome';
    echo url::site($uri);

Generates

    http://localhost/kohana/index.php/welcome

****

### Title
<code>url::title()</code> accepts multiple parameters. Only the input **title** string is mandatory.
Returns a properly formatted title, for use in a URI.

    $input_title = " _Eclectic title's entered by crazed users- ?>  ";

    echo url::title($input_title, $seperator = '_');


Generates: 

    Eclectic_titles_entered_by_crazed_users-

****

### Redirect
<code>url::redirect()</code> accepts multiple **optional** parameters.
Generates an HTTP Server Header (302), which will redirect the browser to a specified URL, by default *base_url*.

    url::redirect("www.whitehouse.gov");

Will redirect the browser to the Big house website.


<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: url.php 1766 2008-01-21 12:56:39Z Geert $ */ ?>