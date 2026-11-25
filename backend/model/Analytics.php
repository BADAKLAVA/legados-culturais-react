<?php

class Analytics {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function hashSessaoAtual(): string {
        $chave = $_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? getenv('APP_KEY') ?: 'legados-culturais-local-session-key';
        if (empty($_SESSION['analytics_visitor_id'])) {
            $_SESSION['analytics_visitor_id'] = bin2hex(random_bytes(16));
        }
        return hash_hmac('sha256', (string)$_SESSION['analytics_visitor_id'], (string)$chave);
    }

    public function registrarEvento(
        string $tipoEvento,
        string $pagina,
        ?int $idUsuario,
        string $sessaoHash
    ): bool {
        $tiposPermitidos = ['visualizacao', 'heartbeat', 'login'];
        if (!in_array($tipoEvento, $tiposPermitidos, true)) return false;

        $pagina = substr(trim($pagina), 0, 255);
        if ($pagina === '') $pagina = '/';

        try {
            $sql = 'INSERT INTO evento_site (tipo_evento, pagina, id_usuario, sessao_hash, data_evento)
                    VALUES (:tipo, :pagina, :usuario, :sessao, CURRENT_TIMESTAMP)';
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':tipo' => $tipoEvento,
                ':pagina' => $pagina,
                ':usuario' => $idUsuario,
                ':sessao' => $sessaoHash
            ]);
        } catch (Throwable $e) {
            error_log('Erro ao registrar evento de analytics: ' . $e->getMessage());
            return false;
        }
    }

    public function obterRelatorio(): ?array {
        try {
            $sqlResumo = "
                SELECT
                    COUNT(*) FILTER (
                        WHERE tipo_evento = 'visualizacao'
                          AND data_evento >= CURRENT_TIMESTAMP - INTERVAL '30 days'
                    ) AS visitas_30_dias,
                    COUNT(DISTINCT sessao_hash) FILTER (
                        WHERE tipo_evento = 'visualizacao'
                          AND data_evento >= CURRENT_TIMESTAMP - INTERVAL '30 days'
                    ) AS visitantes_30_dias,
                    COUNT(DISTINCT sessao_hash) FILTER (
                        WHERE data_evento >= CURRENT_TIMESTAMP - INTERVAL '5 minutes'
                          AND tipo_evento IN ('visualizacao', 'heartbeat')
                    ) AS visitantes_ativos,
                    COUNT(*) FILTER (
                        WHERE tipo_evento = 'login'
                          AND data_evento >= date_trunc('day', CURRENT_TIMESTAMP)
                    ) AS logins_hoje,
                    COUNT(*) FILTER (
                        WHERE tipo_evento = 'login'
                          AND data_evento >= CURRENT_TIMESTAMP - INTERVAL '30 days'
                    ) AS logins_30_dias
                FROM evento_site
            ";
            $resumo = $this->pdo->query($sqlResumo)->fetch(PDO::FETCH_ASSOC);

            $sqlDownloads = "
                SELECT
                    COUNT(*) FILTER (
                        WHERE data_historico >= date_trunc('day', CURRENT_TIMESTAMP)
                    ) AS downloads_hoje,
                    COUNT(*) FILTER (
                        WHERE data_historico >= CURRENT_TIMESTAMP - INTERVAL '30 days'
                    ) AS downloads_30_dias
                FROM historico
            ";
            $downloads = $this->pdo->query($sqlDownloads)->fetch(PDO::FETCH_ASSOC);

            $sqlPaginas = "
                SELECT pagina, COUNT(*) AS visitas
                FROM evento_site
                WHERE tipo_evento = 'visualizacao'
                  AND data_evento >= CURRENT_TIMESTAMP - INTERVAL '30 days'
                GROUP BY pagina
                ORDER BY visitas DESC, pagina ASC
                LIMIT 10
            ";
            $paginas = $this->pdo->query($sqlPaginas)->fetchAll(PDO::FETCH_ASSOC);

            return [
                'visitas_30_dias' => (int)($resumo['visitas_30_dias'] ?? 0),
                'visitantes_30_dias' => (int)($resumo['visitantes_30_dias'] ?? 0),
                'visitantes_ativos' => (int)($resumo['visitantes_ativos'] ?? 0),
                'logins_hoje' => (int)($resumo['logins_hoje'] ?? 0),
                'logins_30_dias' => (int)($resumo['logins_30_dias'] ?? 0),
                'downloads_hoje' => (int)($downloads['downloads_hoje'] ?? 0),
                'downloads_30_dias' => (int)($downloads['downloads_30_dias'] ?? 0),
                'paginas_mais_visitadas' => array_map(static function (array $linha): array {
                    return [
                        'pagina' => $linha['pagina'],
                        'visitas' => (int)$linha['visitas']
                    ];
                }, $paginas)
            ];
        } catch (Throwable $e) {
            error_log('Erro ao gerar relatório do site: ' . $e->getMessage());
            return null;
        }
    }
}
