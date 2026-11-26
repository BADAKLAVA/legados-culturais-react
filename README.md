# Legados Culturais

Aplicação React/Vite no frontend e PHP com PostgreSQL no backend. Os arquivos anexados aos repositórios são armazenados em uma pasta privada do Google Drive; o navegador nunca recebe as credenciais do Google.

## Desenvolvimento local

1. Instale Node.js 22 ou superior, PHP 8.2 com `pdo_pgsql`, `curl` e Composer.
2. Instale as dependências do frontend em `frontend/` com `npm ci`.
3. Na pasta `backend/`, execute `composer update google/apiclient --with-dependencies` para instalar o cliente oficial do Google Drive e atualizar `composer.lock`.
4. Copie `backend/.env.example` para `backend/.env` e preencha os dados do PostgreSQL, uma chave `APP_KEY` longa e aleatória e as credenciais Google descritas na seção abaixo.
5. Execute `database/schema.sql` em um banco novo. Para um banco que já existe, aplique `database/migrations/2026_11_25_analytics_reacoes_estrelas.sql`.
6. Em um terminal, dentro de `backend/`, execute `php -S 127.0.0.1:8000 router.php`. Em outro terminal, dentro de `frontend/`, execute `npm run dev`. O Vite encaminha `/routes.php` ao servidor PHP local.


## Upload, download e histórico

- Upload é exclusivo de administradores. O PHP valida sessão, extensão, MIME e tamanho (25 MB) antes de enviar o arquivo para o Drive.
- Download exige sessão válida. O PHP recupera o conteúdo privado do Drive e o transmite ao navegador; não cria link público.
- Cada download é registrado na tabela `historico`.
- O campo `repositorio.caminho` guarda `gdrive:ID_DO_ARQUIVO` para novos arquivos. Não é necessária migração do schema do PostgreSQL.
- A edição de um repositório envia primeiro o novo arquivo; após confirmar a atualização no banco, remove o objeto anterior. A exclusão do repositório também tenta excluir o arquivo correspondente do Drive.
- Registros locais antigos continuam reconhecidos pela rota de download enquanto os respectivos arquivos ainda existirem no servidor.

## Publicação no Render

O projeto usa Docker. O Dockerfile instala `google/apiclient` durante a construção da imagem e habilita a extensão cURL do PHP. Após configurar as variáveis de ambiente acima, publique a aplicação normalmente. Não é necessário configurar Persistent Disk para os novos uploads: o conteúdo fica no Google Drive.

## Funcionalidades incluídas

- Login identifica o tipo de conta pelo campo `usuario.tipo`; a tela não pergunta se a pessoa é usuário ou administrador.
- Avaliações mantêm uma nota por conta e reações individuais por comentário.
- O início mostra os dois comentários elegíveis de maior nota e, em caso de empate, maior número de likes. A seção só aparece quando existem dois.
- O relatório do site é exclusivo de administradores e apresenta visitantes ativos aproximados, visitas, logins, downloads e páginas mais vistas.
