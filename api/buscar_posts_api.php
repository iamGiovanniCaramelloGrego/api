<?php
// Inclua a conexão com o banco de dados
include 'db.php';

// Inicialize a variável de resultados e o termo de pesquisa
$results = [];
$searchTerm = "";

// Verifique se a conexão com o banco foi bem-sucedida
if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
        $searchTerm = $_POST['query'];
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['query'])) {
        $searchTerm = $_GET['query'];
    }

    if (!empty($searchTerm)) {
        $searchTerm = filter_var($searchTerm, FILTER_SANITIZE_STRING);
        $searchLike = "%$searchTerm%";

        // Consulta para buscar posts e dados de usuário
        $sql = "SELECT posts.*, usuarios.nome, usuarios.pic_perfil
                FROM posts
                JOIN usuarios ON posts.usuario_id = usuarios.id
                WHERE posts.texto LIKE ?";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Erro na preparação da consulta de posts: " . $conn->error);
        }

        $stmt->bind_param("s", $searchLike);
        $stmt->execute();
        $result = $stmt->get_result();

        // Loop para buscar cada post
        while ($row = $result->fetch_assoc()) {
            $post = [
                "id" => $row["id"],
                "username" => $row["nome"],
                "profile_pic" => $row["pic_perfil"],
                "text" => $row["texto"],
                "photo" => $row["foto"],
                "video" => $row["video"]
            ];

            $results[] = $post; // Adiciona o post ao array de resultados
        }

        $stmt->close();
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
} finally {
    $conn->close();
}

// Retorna os resultados em JSON
header('Content-Type: application/json');
echo json_encode(["results" => $results]);
exit;
?>
