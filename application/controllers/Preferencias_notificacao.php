<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Preferencias_notificacao extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('Preferencias_notificacao_model');
        $this->data['menuConfiguracoes'] = 'Preferências';
    }

    /**
     * Exibe e salva preferências de notificação
     */
    public function index()
    {
        $usuario_id = $this->session->userdata('id_admin');
        $cliente_id = $this->session->userdata('id_cliente');
        
        if ($this->input->post('preferencias')) {
            // Salvar múltiplas preferências
            $preferencias = $this->input->post('preferencias');
            $salvas = 0;
            
            foreach ($preferencias as $categoria => $pref) {
                $tipo_notificacao = $pref['tipo_notificacao'] ?? 'email';
                $habilitado = isset($pref['habilitado']) ? 1 : 0;
                $dias_antes_prazo = !empty($pref['dias_antes_prazo']) ? (int)$pref['dias_antes_prazo'] : null;
                
                if ($this->Preferencias_notificacao_model->salvar($usuario_id, $cliente_id, $tipo_notificacao, $categoria, $habilitado, $dias_antes_prazo)) {
                    $salvas++;
                }
            }
            
            if ($salvas > 0) {
                $this->session->set_flashdata('success', "{$salvas} preferência(s) salva(s) com sucesso!");
            } else {
                $this->session->set_flashdata('error', 'Erro ao salvar preferências.');
            }
            
            redirect(site_url('preferencias_notificacao'));
        }
        
        // Obter preferências atuais
        if ($usuario_id) {
            $this->data['preferencias'] = $this->Preferencias_notificacao_model->getTodasPreferenciasUsuario($usuario_id);
        } elseif ($cliente_id) {
            $this->data['preferencias'] = $this->Preferencias_notificacao_model->getTodasPreferenciasCliente($cliente_id);
        } else {
            $this->data['preferencias'] = [];
        }
        
        $this->data['view'] = 'preferencias_notificacao/index';
        return $this->layout();
    }
}

