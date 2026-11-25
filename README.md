# Legados Culturais

Aplicação React/Vite no frontend e PHP com PostgreSQL no backend. Os arquivos anexados aos repositórios são armazenados em uma pasta privada do Google Drive; o navegador nunca recebe as credenciais do Google.

## Desenvolvimento local

1. Instale Node.js 22 ou superior, PHP 8.2 com `pdo_pgsql`, `curl` e Composer.
2. Instale as dependências do frontend em `frontend/` com `npm ci`.
3. Na pasta `backend/`, execute `composer update google/apiclient --with-dependencies` para instalar o cliente oficial do Google Drive e atualizar `composer.lock`.
4. Copie `backend/.env.example` para `backend/.env` e preencha os dados do PostgreSQL, uma chave `APP_KEY` longa e aleatória e as credenciais Google descritas na seção abaixo.
5. Execute `database/schema.sql` em um banco novo. Para um banco que já existe, aplique `database/migrations/2026_11_25_analytics_reacoes_estrelas.sql`.
6. Em um terminal, dentro de `backend/`, execute `php -S 127.0.0.1:8000 router.php`. Em outro terminal, dentro de `frontend/`, execute `npm run dev`. O Vite encaminha `/routes.php` ao servidor PHP local.

## Configurar o Google Drive

O sistema utiliza OAuth 2.0 com acesso offline e o escopo `drive.file`. Esse escopo limita o aplicativo aos arquivos e à pasta criados/gerenciados por ele, em vez de conceder acesso a todo o Drive.

1. Entre em [Google Cloud Console](https://console.cloud.google.com/) com a conta Google que será proprietária dos arquivos do projeto.
2. Crie um projeto e ative a **Google Drive API**.
3. Configure a tela de consentimento OAuth como **External**. Durante os testes, adicione a conta Google que armazenará os arquivos como test user.
4. Crie uma credencial OAuth do tipo **Web application** e adicione exatamente este URI de redirecionamento autorizado: `http://localhost:8081/google_drive_oauth_setup.php`.
5. Copie o Client ID e o Client Secret para `backend/.env` nas variáveis `GOOGLE_CLIENT_ID` e `GOOGLE_CLIENT_SECRET`.
6. Abra um terminal na pasta `backend/` e execute `php -S localhost:8081 -t scripts`.
7. No mesmo computador, abra `http://localhost:8081/google_drive_oauth_setup.php`, entre na conta que armazenará os documentos e autorize o aplicativo. O assistente criará a pasta **Legados Culturais** no Drive e mostrará `GOOGLE_REFRESH_TOKEN` e `GOOGLE_DRIVE_FOLDER_ID` uma única vez.
8. Copie esses dois valores para `backend/.env` e também para as variáveis de ambiente do serviço no Render. Feche o servidor local usado pelo assistente.

O assistente OAuth fica em `backend/scripts/`, fora do diretório público. Não mova esse arquivo para `frontend/public/` e não publique o servidor local de configuração.

### Atenção ao status do app OAuth

Enquanto um app OAuth externo estiver com status **Testing**, o Google emite refresh tokens que expiram em 7 dias para esse tipo de escopo. Para uso contínuo, altere o status de publicação para **In production** no Google Auth Platform. Como o projeto usa `drive.file`, um escopo não sensível, a autorização é mais restrita do que solicitar acesso total ao Drive; o Google ainda pode exibir o aviso de aplicativo não verificado até a conclusão da verificação aplicável. Não deixe o app em Testing esperando que o token seja permanente.

### Variáveis necessárias no Render

Configure estas variáveis em **Environment** no serviço Render. Não as coloque no GitHub:

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REFRESH_TOKEN`
- `GOOGLE_DRIVE_FOLDER_ID`

Mantenha também as variáveis PostgreSQL e `APP_KEY`: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSLMODE`, `APP_KEY` e, quando necessário, `APP_URL`, `RESEND_API_KEY` e `RESEND_FROM`.

## Upload, download e histórico

- Upload é exclusivo de administradores. O PHP valida sessão, extensão, MIME e tamanho (25 MB) antes de enviar o arquivo para o Drive.
- Download exige sessão válida. O PHP recupera o conteúdo privado do Drive e o transmite ao navegador; não cria link público.
- Cada download é registrado na tabela `historico`.
- O campo `repositorio.caminho` guarda `gdrive:ID_DO_ARQUIVO` para novos arquivos. Não é necessária migração do schema do PostgreSQL.
- A edição de um repositório envia primeiro o novo arquivo; após confirmar a atualização no banco, remove o objeto anterior. A exclusão do repositório também tenta excluir o arquivo correspondente do Drive.
- Registros locais antigos continuam reconhecidos pela rota de download enquanto os respectivos arquivos ainda existirem no servidor.

### Migrar arquivos locais antigos (opcional)

Se ainda houver arquivos locais antigos em `backend/uploads/repositorios`, configure as variáveis `GOOGLE_*` no ambiente em que executará a migração e rode, a partir da raiz do projeto:

```bash
php backend/scripts/migrar_arquivos_locais_para_drive.php
```

O script atualiza `repositorio.caminho` para `gdrive:ID` depois de confirmar o upload e preserva a cópia local. Confirme que os downloads funcionam antes de remover manualmente as cópias antigas. Se os arquivos já foram perdidos por causa do armazenamento efêmero do Render, o script não consegue recuperá-los.

## Publicação no Render

O projeto usa Docker. O Dockerfile instala `google/apiclient` durante a construção da imagem e habilita a extensão cURL do PHP. Após configurar as variáveis de ambiente acima, publique a aplicação normalmente. Não é necessário configurar Persistent Disk para os novos uploads: o conteúdo fica no Google Drive.

## Segurança e armazenamento

- Nunca coloque Client Secret, refresh token ou credenciais do Google no React, em arquivos versionados ou em issues do GitHub.
- O bucket/pasta do Drive permanece privado; o PHP é responsável pela autenticação e autorização de downloads.
- Use uma conta Google dedicada ao projeto, não uma conta pessoal com documentos privados. A conta gratuita tem até 15 GB compartilhados entre Drive, Gmail e Google Fotos; quando o armazenamento fica cheio, novos uploads podem falhar.
- Os arquivos permanecem no Drive até serem excluídos. O armazenamento depende da cota e das políticas da conta Google.

## Funcionalidades incluídas

- Login identifica o tipo de conta pelo campo `usuario.tipo`; a tela não pergunta se a pessoa é usuário ou administrador.
- Avaliações mantêm uma nota por conta e reações individuais por comentário.
- O início mostra os dois comentários elegíveis de maior nota e, em caso de empate, maior número de likes. A seção só aparece quando existem dois.
- O relatório do site é exclusivo de administradores e apresenta visitantes ativos aproximados, visitas, logins, downloads e páginas mais vistas.

## Atenção ao banco existente

Antes de aplicar a migração, confira se já existem notas duplicadas por usuário e repositório:

```sql
SELECT id_usuario, id_repositorio, COUNT(*)
FROM estrela
GROUP BY id_usuario, id_repositorio
HAVING COUNT(*) > 1;
```

A migração adiciona uma restrição única para que cada pessoa mantenha uma nota atual por repositório. Resolva eventuais duplicatas antes de executá-la.
