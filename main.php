<?php

// Configurações do banco de dados
$host = 'localhost';
$dbname = 'banco_de_dados';
$username = 'user';
$password = 'senha';

try {
   
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
   
    function getMovementRanking($movementId) {
        global $conn;
        
        // Consulta SQL para obter o ranking
        $sql = "SELECT u.name AS user_name, pr.value AS personal_record, 
                (SELECT COUNT(DISTINCT pr2.value) FROM personal_record pr2 
                 WHERE pr2.movement_id = :movement_id AND pr2.value >= pr.value) AS position,
                pr.date AS record_date
                FROM personal_record pr
                INNER JOIN user u ON pr.user_id = u.id
                WHERE pr.movement_id = :movement_id
                ORDER BY pr.value DESC, pr.date ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':movement_id', $movementId);
        $stmt->execute();
        
        $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $ranking;
    }
    
 
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['movement_id'])) {
        $movementId = $_GET['movement_id'];
        
       
        $ranking = getMovementRanking($movementId);
        
        // Retorna o ranking como JSON
        header('Content-Type: application/json');
        echo json_encode($ranking);
    } else {
       
        http_response_code(400);
        echo json_encode(array("message" => "Bad request"));
    }
    
} catch(PDOException $e) {
   
    http_response_code(500);
    echo json_encode(array("message" => "Internal server error"));
}

?>
