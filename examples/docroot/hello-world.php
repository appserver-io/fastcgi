<?php
echo 'Hello ' . (@$_POST['name'] ?: 'Unknown dude') . '.';
