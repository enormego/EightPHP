Article status [Draft] requires [Editing] Complete Alternator method
# Text Helper
Provides methods for working with Text.

## Methods

### Word Limiter
<code>text::limit_words()</code> accepts multiple parameters. Only the input **string** is required.
The default end character is the ellipsis.

    $long_description = 'The rain in Spain falls mainly in the plain';
    $limit = 4;
    $end_char = '&amp;nbsp;';

    $short_description = html::limit_words($long_description, $limit, $end_char);

Generates: <pre>The rain in Spain </pre>

****

### Character Limiter
<code>text::limit_chars()</code> accepts multiple parameters. Only the input **string** is required.
The default end character is the ellipsis.

    $long_description = 'The rain in Spain falls mainly in the plain';
    $limit = 4;
    $end_char = '&amp;nbsp;';
    $preserve_words = FALSE;

    $short_description = html::limit_chars($long_description, $limit, $end_char, $preserve_words);

Generates: <pre>The r </pre>

****

### Alternator
<code>text::alternator()</code> accepts multiple parameters. 

    echo text::alternator('what the hell does this do again?');

****

### Random
<code>text::random()</code> accepts multiple optional parameters.
Returns a random text string of specified length.

    echo text::random($type = 'alnum', $length = 10);


****

### Censor
<code>text::censor()</code> accepts multiple optional parameters. The input string and an array of marker
words is required.
Returns a string with the marker words censored by the specified replacement character.

    $str = 'The income tax is a three letter word, but telemarketers are scum.';
	$replacement = '*';
	$badwords = array('tax', 'scum');
    echo text::censor($str, $badwords, $replacement, $replace_partial_words = FALSE);

Generates <pre>The income *** is a three letter word, but telemarketers are ****.</pre>

<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: text.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>