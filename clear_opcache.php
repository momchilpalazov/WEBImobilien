<?php
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache кешът е изчистен успешно!";
} else {
    echo "OPCache не е активиран.";
}
?>
