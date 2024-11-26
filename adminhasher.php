<?php
// This is a one time script so we can insert the admin account into the database whilst having a secure password
// To generate a hash for 'adminpassword':
echo password_hash('adminpassword', PASSWORD_DEFAULT);
?>