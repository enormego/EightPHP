Article status [Draft] requires [Editing] Complete and Describe parameters
# Html Helper
Provides methods for HTML generation.

## Methods

### Convert special characters to html entities
<code>html::specialchars()</code> accepts multiple parameters. Only the input **string** is required.
Converts special characters to html entities, using the UFT-8 character set.

    $encoded_string = html::specialchars($string, $double_encode = TRUE);

****

### Generate an html anchor link
<code>html::anchor()</code> method accepts multiple parameters. Only the URL segment(s) are required.

A standard html anchor link is generated. If you want to generate a link that is internal to your website,
pass only the url segments to the function, and the anchor is automatically constructed from the site url
defined in <file>config</file>

    echo html::anchor('pub/articles/7', 'Articles', array('title' => 'Fuel price increase!'));

Generates 

    <a href="http://example.com/index.php/pub/articles/7" title="Fuel price increase!">Articles</a>

****

### Generate an html stylesheet link
<code>html::stylesheet()</code> accepts multiple parameters. Only the stylesheet is required.

    // base_url = "http://example.com/"
    $stylesheet = "user_guide/css/layout";
    echo html::stylesheet($stylesheet, $index = TRUE, $media = FALSE);

Generates

    <link rel="stylesheet" href="http://example.com/index.php/user_guide/css/layout.css" />

****

### Generate an html script link
<code>html::script()</code> accepts multiple parameters. Only the script is required.

    // base_url = "http://example.com/"
    $script = "user_guide/js/prettify";
    echo html::script($script, $index = TRUE, $media = FALSE);

Generates

    <script type="text/javascript" src="http://example.com/index.php/user_guide/js/prettify.js"></script>


<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: html.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>