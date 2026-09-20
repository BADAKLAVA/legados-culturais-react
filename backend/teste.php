<?php 
    session_start();
    require_once "config/database.php";
    require_once "model/Usuario.php"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de tela de usuario</title>
</head>
<body>
    
    <?php 
        $user = new Usuario($pdo);
        $dados = $user->listarInformacoes($_SESSION["id_usuario"]);
        echo 
        "Id: " . $_SESSION["id_usuario"] .
        "<br>Tipo de usuario: " . $_SESSION["tipo"] .
        "<br>Nome: " . $dados["nome"] .
        "<br>Sobrenome: " . $dados["sobrenome"] .
        "<br>Email: " . $dados["email"] .
        "<br>Senha: " . $dados["senha"] .
        "<br>Telefone: " . $dados["telefone"] .
        "<br>Data_nascimento: " . $dados["data_nascimento"] .
        "<br>Sexo: " . $dados["sexo"] .
        "<br>Genero: " . $dados["genero"] .
        "<br>Estado: " . $dados["estado"] .
        "<br>Cidade: " . $dados["cidade"] .
        "<br>Bairro: " . $dados["bairro"] .
        "<br>Logradouro: " . $dados["logradouro"] .
        "<br>Numero: " . $dados["numero"] .
        "<br>Complemento: " . $dados["complemento"];
    ?>

</body>
</html>