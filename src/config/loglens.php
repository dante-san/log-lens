<?php

return [
    'route_prefix' => 'logs',
    'middleware'   => ['web'],
    'chunk_size'   => 8192,
    'max_entries'  => 500,
];
