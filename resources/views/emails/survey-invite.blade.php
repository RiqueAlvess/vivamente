<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convite para Pesquisa HSE-IT</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #0284c7; color: white; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; }
        .content { background: white; padding: 30px; border: 1px solid #e5e7eb; border-top: none; }
        .button { display: inline-block; background: #0284c7; color: white; padding: 14px 28px; border-radius: 6px; text-decoration: none; font-weight: 600; margin: 20px 0; }
        .footer { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Plataforma NR1</h1>
        <p>Pesquisa de Saúde e Bem-Estar no Trabalho</p>
    </div>
    <div class="content">
        <p>Olá, <strong>{{ $invite->collaborator->name }}</strong>!</p>
        <p>Você foi convidado(a) para participar da pesquisa <strong>{{ $invite->campaign->name }}</strong>.</p>
        <p>Esta pesquisa é confidencial e anônima. Suas respostas são muito importantes para melhorarmos as condições de trabalho na nossa organização.</p>
        <p style="text-align: center;">
            <a href="{{ $surveyUrl }}" class="button">Responder Pesquisa</a>
        </p>
        <p>Ou copie e cole o link abaixo no seu navegador:</p>
        <p style="word-break: break-all; color: #0284c7;">{{ $surveyUrl }}</p>
        <p><strong>Importante:</strong> Este link é de uso único e pessoal. Não compartilhe com outras pessoas.</p>
    </div>
    <div class="footer">
        <p>Plataforma NR1 — Sistema de Gestão de Riscos Psicossociais HSE-IT</p>
        <p>Este e-mail foi enviado automaticamente. Não responda a este e-mail.</p>
    </div>
</body>
</html>
