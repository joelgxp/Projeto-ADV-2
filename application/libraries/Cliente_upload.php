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
        
        // Configuração padrão
        $this->config = [
            'upload_path' => './assets/userImage/',
            'allowed_types' => 'gif|jpg|jpeg|png|pdf',
            'max_size' => 2048, // 2MB
            'encrypt_name' => true,
            'remove_spaces' => true,
        ];

        // Mesclar com configuração customizada
        if (! empty($config)) {
            $this->config = array_merge($this->config, $config);
        }

        // Criar diretório se não existir
        if (! is_dir($this->config['upload_path'])) {
            mkdir($this->config['upload_path'], 0755, true);
        }
    }

    /**
     * Valida arquivo antes do upload
     *
     * @param string $field_name Nome do campo do formulário
     * @return array ['valid' => bool, 'error' => string|null]
     */
    public function validarArquivo($field_name = 'fotoCliente')
    {
        if (! isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return ['valid' => true, 'error' => null]; // Arquivo não é obrigatório
        }

        $file = $_FILES[$field_name];

        // Verificar erro de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return [
                'valid' => false,
                'error' => $this->getUploadErrorMessage($file['error'])
            ];
        }

        // Verificar tamanho
        $max_size_bytes = $this->config['max_size'] * 1024; // Converter KB para bytes
        if ($file['size'] > $max_size_bytes) {
            return [
                'valid' => false,
                'error' => 'Arquivo muito grande. Tamanho máximo: ' . $this->config['max_size'] . 'KB'
            ];
        }

        // Verificar extensão
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = explode('|', $this->config['allowed_types']);
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
     * @return array ['success' => bool, 'file_name' => string|null, 'error' => string|null]
     */
    public function fazerUpload($field_name = 'fotoCliente')
    {
        $validation = $this->validarArquivo($field_name);
        if (! $validation['valid']) {
            return [
                'success' => false,
                'file_name' => null,
                'error' => $validation['error']
            ];
        }

        if (! isset($_FILES[$field_name]) || $_FILES[$field_name]['error'] == UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'file_name' => null,
                'error' => null
            ];
        }

        $this->CI->load->library('upload', $this->config);

        if (! $this->CI->upload->do_upload($field_name)) {
            return [
                'success' => false,
                'file_name' => null,
                'error' => $this->CI->upload->display_errors('', '')
            ];
        }

        $upload_data = $this->CI->upload->data();

        return [
            'success' => true,
            'file_name' => $upload_data['file_name'],
            'error' => null
        ];
    }

    /**
     * Remove arquivo antigo se existir
     *
     * @param string $file_name Nome do arquivo a remover
     * @return bool true se removido com sucesso ou não existia
     */
    public function removerArquivoAntigo($file_name)
    {
        if (empty($file_name)) {
            return true;
        }

        $file_path = $this->config['upload_path'] . $file_name;

        if (file_exists($file_path)) {
            return @unlink($file_path);
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
}
