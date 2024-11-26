<?php
session_start();

function verificar_autenticacao() {
    if (!isset($_SESSION['usuario_id'])) {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(["erro" => "Usuário não autenticado"]);
        exit();
    }
}
?>
