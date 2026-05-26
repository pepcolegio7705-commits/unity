<?php
echo "<h3>Git Status</h3><pre>";
system("git status 2>&1");
echo "</pre><h3>Git Remote</h3><pre>";
system("git remote -v 2>&1");
echo "</pre><h3>Git Log</h3><pre>";
system("git log -n 5 2>&1");
echo "</pre>";
?>
