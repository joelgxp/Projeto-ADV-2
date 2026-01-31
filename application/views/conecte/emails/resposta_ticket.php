<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resposta ao Ticket - Portal do Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #436eee;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-top: none;
        }
        .ticket-info {
            background-color: #fff;
            border: 2px solid #436eee;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background-color: #436eee;
            color: white;
            padding: 15px 40px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background-color: #2d4fc7;
        }
        .footer {
            background-color: #f0f0f0;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-radius: 0 0 5px 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo isset($emitente->nome) ? htmlspecialchars($emitente->nome) : $this->config->item('app_name'); ?></h2>
        <p>Resposta ao Ticket</p>
    </div>
    
    <div class="content">
        <?php if ($quem_respondeu == 'advogado') : ?>
            <p>Olá <strong><?php echo isset($cliente->nomeCliente) ? htmlspecialchars($cliente->nomeCliente) : 'Cliente'; ?></strong>,</p>
            <p>Você recebeu uma resposta ao seu ticket no portal do cliente.</p>
        <?php else : ?>
            <p>Olá <strong><?php echo isset($advogado->nome) ? htmlspecialchars($advogado->nome) : 'Advogado'; ?></strong>,</p>
            <p>O cliente <strong><?php echo isset($cliente->nomeCliente) ? htmlspecialchars($cliente->nomeCliente) : 'Cliente'; ?></strong> respondeu ao ticket.</p>
        <?php endif; ?>
        
        <div class="ticket-info">
            <h3 style="margin-top: 0;">Ticket #<?php echo $ticket->id; ?></h3>
            <p><strong>Assunto:</strong> <?php echo htmlspecialchars($ticket->assunto); ?></p>
        </div>
        
        <div style="text-align: center;">
            <a href="<?php echo base_url(); ?>index.php/tickets/visualizar/<?php echo $ticket->id; ?>" class="button">Ver Ticket e Responder</a>
        </div>
        
        <p style="margin-top: 30px; font-size: 12px; color: #666;">
            <strong>Importante:</strong> Acesse o portal para ver a resposta completa e continuar a conversa.
        </p>
    </div>
    
    <div class="footer">
        <p><?php echo date('Y'); ?> &copy; <?php echo isset($emitente->nome) ? htmlspecialchars($emitente->nome) : $this->config->item('app_name'); ?></p>
        <p>Este é um email automático, por favor não responda.</p>
    </div>
</body>
</html>

