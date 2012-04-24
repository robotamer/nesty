<?php

// Autoload classes
Autoloader::namespaces(array(
    'Nesty' => Bundle::path('nesty'),
));

// Set the global alias for Nesty
Autoloader::alias('Nesty\\Nesty', 'Nesty');