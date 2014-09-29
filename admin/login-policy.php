<?php

if (false === isset($_SESSION['admin'])) {
    $chan->reUrl('index.php');
}
