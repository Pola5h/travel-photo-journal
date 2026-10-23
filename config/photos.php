<?php

return [

    'max_kb' => (int) env('UPLOAD_MAX_KB', 5120),

    'mimes' => ['jpg', 'jpeg', 'png', 'webp'],

    'dir' => 'uploads/photos',

];
