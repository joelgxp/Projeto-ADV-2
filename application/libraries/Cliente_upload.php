<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Library para gerenciar upload de arquivos de clientes
 * (fotos, documentos, etc.)
 */
class Cliente_upload
{
    private $CI;
    private $config;

    public function __construct($config = [])
    {
        $this->CI = &get_instance();

        $defaults = [
            'upload_path' => './assets/userImage/',
            'allowed_types' => 'gif|jpg|jpeg|png|pdf',
            'max_size' => 2048, // 2MB
            'encrypt_name' => true,
            'remove_spaces' => true,
        ];

        $this->config = array_merge($defaults, $config);

        // Garante que o diretório padrão exista logo na construção
        $this->prepareConfig();
    }

    /**
     * Valida arquivo antes do upload
     *
     * @param string $field_name Nome do campo do formulário
     * @param array $configOverride Configurações específicas para o campo
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validarArquivo($field_name = 'fotoCliente', $configOverride = [])
    {
        if (! isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return ['valid' => true, 'error' => null]; // Arquivo não é obrigatório
        }

        $config = $this->prepareConfig($configOverride);
        $file = $_FILES[$field_name];

        // Verificar erro de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Verificar tamanho
        $max_size_bytes = $config['max_size'] * 1024; // Converter KB para bytes
        if ($file['size'] > $max_size_bytes) {
            return [
                'valid' => false,
                'error' => 'Arquivo muito grande. Tamanho máximo: ' . $config['max_size'] . 'KB'
            ];
        }

        // Verificar extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = explode('|', $config['allowed_types']);
        if (! in_array($ext, $allowed)) {
            return [
                'valid' => false,
                'error' => 'Tipo de arquivo não permitido. Tipos aceitos: ' . implode(', ', $allowed)
            ];
        }

        // Verificar MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = [
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
        ];

        if (isset($allowed_mimes[$ext]) && $mime_type !== $allowed_mimes[$ext]) {
            return [
                'valid' => false,
                'error' => 'Tipo MIME do arquivo não corresponde à extensão.'
            ];
        }

        // Verificar se é realmente uma imagem (para tipos de imagem)
        if (in_array($ext, ['gif', 'jpg', 'jpeg', 'png'])) {
            $image_info = @getimagesize($file['tmp_name']);
            if ($image_info === false) {
                return [
                    'valid' => false,
                    'error' => 'Arquivo não é uma imagem válida.'
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Faz upload do arquivo
     *
     * @param string $field_name Nome do campo do formulário
     * @param array $configOverride Configurações específicas para o campo
     * @return array ['success' => bool, 'file_name' => string|null, 'relative_path' => string|null, 'error' => string|null]
     */
    public function fazerUpload($field_name = 'fotoCliente', $configOverride = [])
    {
        $validation = $this->validarArquivo($field_name, $configOverride);
        if (! $validation['valid']) {
            return [
                'success' => false,
                'file_name' => null,
                'relative_path' => null,
                'error' => $validation['error']
            ];
        }

        if (! isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'file_name' => null,
                'relative_path' => null,
                'error' => null
            ];
        }

        $config = $this->prepareConfig($configOverride);
        $relativeDir = trim(str_replace('\\', '/', $config['_public_dir'] ?? ''), '/');

        $this->CI->load->library('upload');
        $this->CI->upload->initialize($config);

        if (! $this->CI->upload->do_upload($field_name)) {
            return [
                'success' => false,
                'file_name' => null,
                'relative_path' => null,
                'error' => $this->CI->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->CI->upload->data();
        $relative_path = ($relativeDir ? $relativeDir . '/' : '') . $upload_data['file_name'];
        $relative_path = ltrim(str_replace('\\', '/', $relative_path), '/');

        return [
            'success' => true,
            'file_name' => $upload_data['file_name'],
            'relative_path' => $relative_path,
            'error' => null
        ];
    }

    /**
     * Remove arquivo antigo se existir
     *
     * @param string $file_name Caminho ou nome do arquivo a remover
     * @return bool true se removido com sucesso ou não existia
     */
    public function removerArquivoAntigo($file_name)
    {
        if (empty($file_name)) {
            return true;
        }

        $caminhos = [];
        $sanitized = str_replace('\\', '/', $file_name);

        if (strpos($sanitized, FCPATH) === 0) {
            $caminhos[] = $sanitized;
        } else {
            $caminhos[] = FCPATH . ltrim($sanitized, '/');
        }

        $config = $this->prepareConfig();
        $caminhos[] = rtrim($config['upload_path'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($file_name);

        foreach ($caminhos as $path) {
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
            if (is_file($path)) {
                return @unlink($path);
            }
        }

        return true;
    }

    /**
     * Obtém mensagem de erro baseada no código de erro do PHP
     *
     * @param int $error_code Código de erro do upload
     * @return string Mensagem de erro
     */
    private function getUploadErrorMessage($error_code)
    {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede o tamanho máximo permitido pelo servidor.',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede o tamanho máximo permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'Arquivo foi enviado parcialmente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta uma pasta temporária.',
            UPLOAD_ERR_CANT_WRITE => 'Falha ao escrever arquivo no disco.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão PHP parou o upload do arquivo.',
        ];

        return $messages[$error_code] ?? 'Erro desconhecido no upload.';
    }

    /**
     * Define configuração customizada
     *
     * @param array $config Configuração
     */
    public function setConfig($config)
    {
        $this->config = array_merge($this->config, $config);
        $this->prepareConfig();
    }

    /**
     * Obtém configuração atual
     *
     * @return array Configuração
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Prepara configuração mesclando overrides e garantindo caminhos válidos
     *
     * @param array $override
     * @return array
     */
    private function prepareConfig($override = [])
    {
        $config = array_merge($this->config, $override);
        $rawPath = $config['upload_path'];

        $absolutePath = $rawPath;
        $publicDir = trim(str_replace(['./', '.\\'], '', $rawPath), '/\\');

        if (strpos($rawPath, './') === 0 || strpos($rawPath, '.\\') === 0) {
            $absolutePath = FCPATH . substr($rawPath, 2);
        } elseif (strpos($rawPath, FCPATH) !== 0) {
            $absolutePath = FCPATH . ltrim($rawPath, '/\\');
        }

        $absolutePath = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (! is_dir($absolutePath)) {
            mkdir($absolutePath, 0755, true);
        }

        if ($publicDir === '') {
            $publicDir = trim(str_replace('\\', '/', str_replace(FCPATH, '', $absolutePath)), '/');
        }

        $config['upload_path'] = $absolutePath;
        $config['_public_dir'] = $publicDir;

        return $config;
    }
}
