Article status [First draft] requires [Editing] Describe input class and usage
# Input Class
todo describe class

## Methods

### Fetch item from GET array
<code>$this->input->get()</code> accepts multiple optional parameters.
Returns item as a string or <code>FALSE</code> if item does not exist.

    $get_item = $this->input->get($index = 'item', $xss_clean = TRUE);

Setting <code>$index = FALSE</code> returns the GET array.<br />
Setting <code>$xss_clean = TRUE</code> filters GET for unsafe data.

****

### Fetch item from POST array
<code>$this->input->post()</code> accepts multiple optional parameters.
Returns item as a string or <code>FALSE</code> if item does not exist.

    $post_item = $this->input->post($index = 'item', $xss_clean = TRUE);

Setting <code>$index = FALSE</code> returns the POST array.<br />
Setting <code>$xss_clean = TRUE</code> filters POST for unsafe data.

****

### Fetch item from COOKIE array
<code>$this->input->post()</code> accepts multiple optional parameters.
Returns item as a string or <code>FALSE</code> if item does not exist.

    $cookie_item = $this->input->post($index = 'item', $xss_clean = TRUE);

Setting <code>$index = FALSE</code> returns the COOKIE array.<br />
Setting <code>$xss_clean = TRUE</code> filters COOKIE for unsafe data.

****

### Fetch item from SERVER array
<code>$this->input->server()</code> accepts multiple optional parameters.
Returns item as a string or <code>FALSE</code> if item does not exist.

    $server_item = $this->input->server($index = 'item', $xss_clean = TRUE);

Setting <code>$index = FALSE</code> returns the SERVER array.<br />
Setting <code>$xss_clean = TRUE</code> filters SERVER for unsafe data

****

### Fetch IP address from SERVER array
<code>$this->input->ip_address()</code> has no parameters.
Returns IP address as a string in dotted quad notation eg. <code>"192.0.0.12"</code><br />
Returns <code>"0.0.0.0"</code> if IP address is not found or invalid.

    echo $this->input->ip_address();

****

### Validate IP address from SERVER array
<code>$this->input->valid_ip()</code> accepts one mandatory parameter.
Returns <code>FALSE</code> if IP address is invalid.

    $ip = "192.0.0.44";
    echo $this->input->valid_ip($ip);

****

### Fetch USER_AGENT from SERVER array
<code>$this->input->user_agent()</code> has no parameters.
Returns the user agent as a string.<br />
Returns <code>FALSE</code> if user agent is not found.

    echo $this->input->user_agent();

****

### Filter input for XSS
<code>$this->input->xss_clean()</code> accepts multiple parameters. Input string to filter is required.
Returns a string with unsafe data filtered out. Note: The input data may be **modified** by filtering.

    $clean_item =  $this->input->xss_clean($item, charset = 'ISO-8859-1');





<?php echo $this->load->view('user_guide/en/abbr') ?>
<?php /* $Id: input.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>