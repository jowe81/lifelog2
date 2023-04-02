<?php

  $currentTime = time();

  echo "The current timestamp is: " . $currentTime. "\n";

  $url = "http://bclock.wnet.wn/write?timestamp=$currentTime";

  shell_exec("curl $url");

?>