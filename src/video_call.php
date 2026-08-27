<?php
// Redirect to the correct file
header('Location: videocall.php?' . $_SERVER['QUERY_STRING']);
exit();
?>
