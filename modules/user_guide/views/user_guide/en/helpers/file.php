Article status [Draft] requires [Editing] 
# File Helper
Provides methods for splitting and joining Files.

## Methods

### Split a file into segments.
<code>file::split()</code> accepts multiple parameters. Only the input **filename** is required.
Returns the number of file segments created.

    $segments = file::split($filename, $output_directory = FALSE, $piece_size = 10);

****

### Join segments of a split file.
<code>file::join()</code> accepts multiple parameters. Only the input segment **filename** is required.
Returns the number of file segments joined.

    $segments = file::split($filename, $output_file = FALSE);

<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: file.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>