<?php
// Inclua a conexão com o banco de dados
include 'db.php';

// Inicialize as variáveis
$results = [];
$postId = null;
$newText = "";

// Verifique se a conexão com o banco foi bem-sucedida
if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
        // Lê o corpo da requisição (JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Se os dados estiverem presentes, atribua
        if (isset($data['id']) && isset($data['text'])) {
            $postId = $data['id'];
            $newText = $data['text'];
        }

        // Atualiza o post
        if ($postId && $newText) {
            $newText = filter_var($newText, FILTER_SANITIZE_STRING);

            // Atualiza o post no banco de dados
            $sql = "UPDATE posts SET texto = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newText, $postId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $results = ["message" => "Post atualizado com sucesso!"];
            } else {
                $results = ["message" => "Nenhum post foi atualizado."];
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
} finally {
    $conn->close();
}

// Retorna a resposta em JSON
header('Content-Type: application/json');
echo json_encode($results);
exit;
?>
