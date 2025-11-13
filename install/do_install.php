<?php

ini_set('max_execution_time', 300); //300 seconds

$settings_file = __DIR__ . DIRECTORY_SEPARATOR . 'settings.json';

if (! file_exists($settings_file)) {
    exit('Arquivo de configuração não encontrado!');
} else {
    $contents = file_get_contents($settings_file);
    $settings = json_decode($contents, true);
}

if (! empty($_POST)) {
    $host = $_POST['host'];
    $dbuser = $_POST['dbuser'];
    $dbpassword = $_POST['dbpassword'];
    $dbname = $_POST['dbname'];

    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $login_password = $_POST['password'] ? $_POST['password'] : '';
    $base_url = $_POST['base_url'];

    //check required fields
    if (! ($host && $dbuser && $dbname && $full_name && $email && $login_password && $base_url)) {
        echo json_encode(['success' => false, 'message' => 'Por favor insira todos os campos.']);
        exit();
    }

    //check for valid email
    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode(['success' => false, 'message' => 'Por favor insira um email válido.']);
        exit();
    }

    //check for valid database connection
    try {
        $mysqli = @new mysqli($host, $dbuser, $dbpassword, $dbname);

        if (mysqli_connect_errno()) {
            echo json_encode(['success' => false, 'message' => $mysqli->connect_error]);
            exit();
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }

    //all input seems to be ok. check required fiels
    if (! is_file($settings['database_file'])) {
        echo json_encode(['success' => false, 'message' => 'O arquivo ../banco.sql não foi encontrado na pasta de instalação!']);
        exit();
    }

    /*
     * check the db config file
     * if db already configured, we'll assume that the installation has completed
     */
    $is_installed = file_exists('..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env');

    if ($is_installed) {
        echo json_encode(['success' => false, 'message' => 'Parece que este aplicativo já está instalado! Você não pode reinstalá-lo novamente.']);
        exit();
    }

    //start installation
    $sql = file_get_contents($settings['database_file']);

    //set admin information to database
    $now = date('Y-m-d H:i:s');
    $sql = str_replace('admin_name', $full_name, $sql);
    $sql = str_replace('admin_email', $email, $sql);
    $sql = str_replace('admin_password', password_hash($login_password, PASSWORD_DEFAULT), $sql);
    $sql = str_replace('admin_created_at', $now, $sql);

    //create tables in datbase
    $mysqli->multi_query($sql);
    do {
    } while (mysqli_more_results($mysqli) && mysqli_next_result($mysqli));
    $mysqli->close();
    // database created

    $env_file_path = '..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env.example';
    
    if (! file_exists($env_file_path)) {
        // Se .env.example não existir, criar um template básico
        $env_file = "# Ambiente\n";
        $env_file .= "APP_ENVIRONMENT=pre_installation\n\n";
        $env_file .= "# Aplicação\n";
        $env_file .= "APP_NAME=Map-OS\n";
        $env_file .= "APP_SUBNAME=Sistema de Controle de Ordens de Serviço\n";
        $env_file .= "APP_BASEURL=enter_baseurl\n";
        $env_file .= "APP_TIMEZONE=America/Sao_Paulo\n";
        $env_file .= "APP_CHARSET=UTF-8\n\n";
        $env_file .= "# Banco de Dados\n";
        $env_file .= "DB_DSN=\n";
        $env_file .= "DB_HOSTNAME=enter_db_hostname\n";
        $env_file .= "DB_USERNAME=enter_db_username\n";
        $env_file .= "DB_PASSWORD=enter_db_password\n";
        $env_file .= "DB_DATABASE=enter_db_name\n";
        $env_file .= "DB_DRIVER=mysqli\n";
        $env_file .= "DB_PREFIX=\n";
        $env_file .= "DB_CHARSET=utf8\n";
        $env_file .= "DB_COLLATION=utf8_general_ci\n\n";
        $env_file .= "# Criptografia\n";
        $env_file .= "APP_ENCRYPTION_KEY=enter_encryption_key\n\n";
        $env_file .= "# API\n";
        $env_file .= "API_ENABLED=enter_api_enabled\n";
        $env_file .= "API_JWT_KEY=enter_jwt_key\n";
        $env_file .= "API_TOKEN_EXPIRE_TIME=enter_token_expire_time\n\n";
        $env_file .= "# Sessão\n";
        $env_file .= "APP_SESS_DRIVER=database\n";
        $env_file .= "APP_SESS_COOKIE_NAME=app_session\n";
        $env_file .= "APP_SESS_EXPIRATION=7200\n";
        $env_file .= "APP_SESS_SAVE_PATH=ci_sessions\n";
        $env_file .= "APP_SESS_MATCH_IP=false\n";
        $env_file .= "APP_SESS_TIME_TO_UPDATE=300\n";
        $env_file .= "APP_SESS_REGENERATE_DESTROY=false\n\n";
        $env_file .= "# Cookies\n";
        $env_file .= "APP_COOKIE_PREFIX=\n";
        $env_file .= "APP_COOKIE_DOMAIN=\n";
        $env_file .= "APP_COOKIE_PATH=/\n";
        $env_file .= "APP_COOKIE_SECURE=false\n";
        $env_file .= "APP_COOKIE_HTTPONLY=false\n\n";
        $env_file .= "# Segurança\n";
        $env_file .= "APP_CSRF_PROTECTION=true\n";
        $env_file .= "APP_CSRF_TOKEN_NAME=MAPOS_TOKEN\n";
        $env_file .= "APP_CSRF_COOKIE_NAME=MAPOS_COOKIE\n";
        $env_file .= "APP_CSRF_EXPIRE=7200\n";
        $env_file .= "APP_CSRF_REGENERATE=true\n";
        $env_file .= "GLOBAL_XSS_FILTERING=true\n\n";
        $env_file .= "# Outros\n";
        $env_file .= "APP_COMPRESS_OUTPUT=false\n";
        $env_file .= "APP_PROXY_IPS=\n";
        $env_file .= "WHOOPS_ERROR_PAGE_ENABLED=false\n\n";
        $env_file .= "# Email\n";
        $env_file .= "EMAIL_PROTOCOL=smtp\n";
        $env_file .= "EMAIL_SMTP_HOST=smtp.gmail.com\n";
        $env_file .= "EMAIL_SMTP_CRYPTO=tls\n";
        $env_file .= "EMAIL_SMTP_PORT=587\n";
        $env_file .= "EMAIL_SMTP_USER=seuemail@gmail.com\n";
        $env_file .= "EMAIL_SMTP_PASS=senhadoemail\n";
        $env_file .= "EMAIL_VALIDATE=true\n";
        $env_file .= "EMAIL_MAILTYPE=html\n";
        $env_file .= "EMAIL_CHARSET=utf-8\n";
        $env_file .= "EMAIL_NEWLINE=\\r\\n\n";
        $env_file .= "EMAIL_BCC_BATCH_MODE=false\n";
        $env_file .= "EMAIL_WORDWRAP=false\n";
        $env_file .= "EMAIL_PRIORITY=3\n";
    } else {
        $env_file = file_get_contents($env_file_path);
    }

    // set the database config file
    $env_file = str_replace('enter_db_hostname', $host, $env_file);
    $env_file = str_replace('enter_db_username', $dbuser, $env_file);
    $env_file = str_replace('enter_db_password', $dbpassword, $env_file);
    $env_file = str_replace('enter_db_name', $dbname, $env_file);

    // set random enter_encryption_key
    $encryption_key = substr(md5(rand()), 0, 15);
    $env_file = str_replace('enter_encryption_key', $encryption_key, $env_file);
    $env_file = str_replace('enter_baseurl', $base_url, $env_file);

    // set random enter_jwt_key
    $env_file = str_replace('enter_jwt_key', base64_encode(openssl_random_pseudo_bytes(32)), $env_file);
    $env_file = str_replace('enter_token_expire_time', $_POST['enter_token_expire_time'], $env_file);
    $env_file = str_replace('enter_api_enabled', (string) $_POST['enter_api_enabled'], $env_file);

    // set the environment = production
    $env_file = str_replace('pre_installation', 'production', $env_file);

    if (file_put_contents('..' . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR . '.env', $env_file)) {
        echo json_encode(['success' => true, 'message' => 'Instalação bem sucedida.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar arquivo env.']);
    }

    exit();
}
