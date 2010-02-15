<?php
/**
 * Swift Mailer driver, used with the email helper. By default, [native][ref-nat],
 * [sendmail][ref-sdm], and [smtp][ref-smt] drivers are supported.
 *
 * [ref-nat]: http://www.swiftmailer.org/wikidocs/v3/connections/nativemail
 * [ref-sdm]: http://www.swiftmailer.org/wikidocs/v3/connections/sendmail
 * [ref-smt]: http://www.swiftmailer.org/wikidocs/v3/connections/smtp
 */
$config['driver'] = 'native';

/**
 * Driver configuration options. Each driver has different settings. For more
 * information, see the documentation about [sending email][ref-sne].
 */
$config['options'] = nil;
