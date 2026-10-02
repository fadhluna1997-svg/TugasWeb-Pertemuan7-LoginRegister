<?php
require 'config.php';
redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');
