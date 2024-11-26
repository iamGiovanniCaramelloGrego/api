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
    // Verifique se a requisição é POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lê o corpo da requisição (JSON)
        $data = json_decode(file_get_contents("php://input"), true);

        // Se estiver criando um novo post
        if (isset($data['texto']) && !empty($data['texto'])) {
            // Dados para inserir o novo post
            $usuarioId = $data['usuario_id'];  // Assumindo que o id do usuário está sendo passado no corpo da requisição
            $texto = $data['texto'];
            $foto = isset($data['foto']) ? $data['foto'] : null; // Foto (opcional)
            $video = isset($data['video']) ? $data['video'] : null; // Vídeo (opcional)

            // Inserção do novo post no banco de dados
            $sql = "INSERT INTO posts (usuario_id, texto, foto, video) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Erro na preparação da consulta de inserção: " . $conn->error);
            }
            $stmt->bind_param("isss", $usuarioId, $texto, $foto, $video);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                // Se o post foi inserido com sucesso
                $results = ["message" => "Post criado com sucesso!"];
            } else {
                // Se algo deu errado na inserção
                $results = ["message" => "Erro ao criar o post."];
            }
            $stmt->close();
        }
        // Se for um POST de pesquisa
        elseif (isset($data['query']) && !empty($data['query'])) {
            $searchTerm = $data['query'];
        }
    }

    // Se houver um termo de pesquisa, faz a consulta no banco
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
echo json_encode($results);
exit;
?>
