<?php
session_start();
session_destroy();
header('Location: index.php'); // gražinti į index.php
exit();