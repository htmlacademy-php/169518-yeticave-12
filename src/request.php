<?php

function request_is_post(): bool {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

function request_get_post_val($name) {
    return filter_input(INPUT_POST, $name);
}

function request_save_file($param_name): array {
    $result_save_file = [
        'success' => true,
        'tmp_name' => '',
        'upload_name' => '',
        'error' => ''
    ];

    if (empty($_FILES[$param_name]['name'])) {
        $result_save_file['success'] = false;
        $result_save_file['error'] = 'Вы не загрузили файл';
        return $result_save_file;
    }
            
    $tmp_name = $_FILES[$param_name]['tmp_name'];
    $filename = $_FILES[$param_name]['name'];

    if ($_FILES[$param_name]['size'] > UPLOAD_MAX_SIZE) {
        $result_save_file['success'] = false;
        $result_save_file['error'] = 'Превышен максимальный размер файла 2 мб';
        return $result_save_file;
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $tmp_name);
    $mimetype = ['image/jpg', 'image/jpeg', 'image/png'];

    if (!in_array($file_type, $mimetype)) {
        $result_save_file['success'] = false;
        $result_save_file['error'] = 'Загрузите картинку в формате JPG или PNG';
        return $result_save_file;
    }

	
    $result_save_file['tmp_name'] = $tmp_name;
    $result_save_file['upload_name'] = 'uploads/' . $filename; 
    return $result_save_file;
}


