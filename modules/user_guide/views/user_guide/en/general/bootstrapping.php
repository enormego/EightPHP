# System Bootstrapping

Article status [Draft] requires [Proof Reading] 

Kohana uses a <definition>Front Controller</definition> as part of its design. This file is the <file>index</file> file that rests in the directory that Kohana is installed in.

The front controller validates the application and system paths, then loads <file>system/core/Bootstrap</file>. The Bootstrap file begins the process of initializing Kohana.

## Loading
Benchmarking is loaded, and the <benchmark>total_execution_time</benchmark> is started. Next the <benchmark>base_classes_loading</benchmark> benchmark is started.

Core classes (Config, Event, Kohana, Log, utf8) are loaded. Kohana setup is run:

 1. Global output buffer registered, enabling a central function that will replace the following strings in the buffer before sending it to the browser:
   * <code>&#123;kohana_version&#125;</code>: Version of Kohana that is running
   * <code>&#123;execution_time&#125;</code>: Benchmark of the total execution time up to this point
   * <code>&#123;memory_usage&#125;</code>: Total memory being used by the current request
 2. Class auto-loading is enabled for Controllers, Libraries, Models, and Helpers
 3. Error handling is changed to Kohana methods, rather than the PHP defaults
 4. <benchmark>base_classes_loading</benchmark> is stopped
 5. <event>system.shutdown</event> is registered
 6. <event>system.ready</event> is executed

Routing is performed for the current request. A controller is chosen and located, the segments are prepared for executing the controller.

<?php /* $Id: bootstrapping.php 1525 2007-12-13 19:06:59Z PugFish $ */ ?>