<?php
    class RecuperacaoSenha {
        private PDO $pdo;

        public function __construct($pdo) {
           $this->pdo = $pdo;
        }

        public function criarRecuperacaoSenha($idUsuario, $token, $expiracao) {
            error_log("Criar recuperacao");
            $resultado = null;
            try {

                $this->pdo->beginTransaction();

                //apagar o ultimo
                $sql = "DELETE FROM recuperacao_senha WHERE id_usuario = ?";
                $stmt = $this->pdo->prepare($sql);

                error_log(print_r($stmt, true));

                $stmt->execute([$idUsuario]);

                //cria um novo
                $sql = "INSERT INTO recuperacao_senha(id_usuario, token, expiracao) VALUES (?, ?, ?)";
                $stmt = $this->pdo->prepare($sql);
                
                error_log(print_r($stmt, true));

                $resultado = $stmt->execute([
                    $idUsuario,
                    $token,
                    $expiracao
                ]);

                error_log("RESULTADO: " . var_export($resultado, true));

                $this->pdo->commit();
                
                return $resultado;
            } catch(\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                error_log("ERRO: " . $e->getMessage());
                return null;
            }
        }

        public function pesquisarToken($token) {
            $resultado = null;
            try {
                $sql = "SELECT id_usuario
                FROM recuperacao_senha
                WHERE token = ?
                AND utilizado = FALSE
                AND expiracao > CURRENT_TIMESTAMP
                FOR UPDATE";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$token]);
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("RESULTADO: " . var_export($resultado, true));

                return $resultado;

            } catch(Throwable $e) {
                error_log("ERRO: " . $e->getMessage());
                return null;
            }
        }

        //UPDATE
        //marcar um token como utilizado
        public function invalidarToken($token) {
            $resultado = null;
            try {
                $sql = "UPDATE recuperacao_senha
                        SET utilizado = TRUE
                        WHERE token = ?
                        AND utilizado = FALSE";

                $stmt = $this->pdo->prepare($sql);
                $resultado = $stmt->execute([$token]);
                return $resultado && $stmt->rowCount() === 1;

            } catch (\Throwable $e) {
                error_log("ERRO: " . $e->getMessage());
                return null;
            }
        }

        public function excluirToken($idUsuario) {
            $resultado = null;
            try {
                $sql = "DELETE FROM recuperacao_senha WHERE id_usuario = ?";
                $stmt = $this->pdo->prepare($sql);
                $resultado = $stmt->execute([$idUsuario]);
                return $resultado;
            } catch (\Throwable $e) {
                error_log("RESULTADO" . $e->getMessage());
                return null;
            }
        }
    }