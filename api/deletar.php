<?php
// Inclua a conexão com o banco de dados
include 'db.php';

// Inicialize a variável
$postId = null;
$results = [];  // Inicialize a variável results para evitar o erro

// Verifique se a conexão com o banco foi bem-sucedida
if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        // Lê o corpo da requisição (JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Se o ID do post for fornecido
        if (isset($data['id'])) {
            $postId = $data['id'];
        }

        // Exclui o post
        if ($postId) {
            $sql = "DELETE FROM posts WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $postId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $results = ["message" => "Post excluído com sucesso!"];
            } else {
                $results = ["message" => "Nenhum post foi excluído."];
            }
            $stmt->close();
        } else {
            $results = ["message" => "ID do post não fornecido."];
        }
    } else {
        $results = ["message" => "Método não permitido ou inválido."];
    }
} catch (Exception $e) {
    $results = ["error" => $e->getMessage()];
} finally {
    $conn->close();
}

// Retorna a resposta em JSON
header('Content-Type: application/json');
echo json_encode($results);
exit;
?>
