<?php
// api.php - API Backend para o sistema GymPro
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Configurações do banco de dados
class Database {
    private $host = 'localhost';
    private $dbname = 'gympro';
    private $username = 'root'; // Altere conforme sua configuração
    private $password = '';     // Altere conforme sua configuração
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro de conexão com o banco de dados']);
            exit;
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Classe principal da API
class GymAPI {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = new Database();
        $this->pdo = $this->db->getConnection();
        session_start();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_GET['endpoint'] ?? '';

        // Handle preflight requests
        if ($method === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        try {
            switch ($path) {
                case 'login':
                    if ($method === 'POST') {
                        $this->login();
                    }
                    break;
                case 'logout':
                    if ($method === 'POST') {
                        $this->logout();
                    }
                    break;
                case 'dashboard':
                    if ($method === 'GET') {
                        $this->getDashboardData();
                    }
                    break;
                case 'students':
                    if ($method === 'GET') {
                        $this->getStudents();
                    } elseif ($method === 'POST') {
                        $this->addStudent();
                    }
                    break;
                case 'students/delete':
                    if ($method === 'DELETE') {
                        $this->deleteStudent();
                    }
                    break;
                case 'equipment':
                    if ($method === 'GET') {
                        $this->getEquipment();
                    } elseif ($method === 'POST') {
                        $this->addEquipment();
                    }
                    break;
                case 'equipment/delete':
                    if ($method === 'DELETE') {
                        $this->deleteEquipment();
                    }
                    break;
                case 'maintenance':
                    if ($method === 'GET') {
                        $this->getMaintenance();
                    } elseif ($method === 'POST') {
                        $this->addMaintenance();
                    }
                    break;
                case 'maintenance/complete':
                    if ($method === 'PUT') {
                        $this->completeMaintenance();
                    }
                    break;
                case 'maintenance/delete':
                    if ($method === 'DELETE') {
                        $this->deleteMaintenance();
                    }
                    break;
                case 'plans':
                    if ($method === 'GET') {
                        $this->getPlans();
                    }
                    break;
                case 'payments':
                    if ($method === 'GET') {
                        $this->getPayments();
                    }
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint não encontrado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Para simplificar, usando comparação direta. Em produção, use password_verify()
        if ($user && (
            ($username === 'admin' && $password === 'admin123') ||
            ($username === 'funcionario' && $password === 'func123')
        )) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            echo json_encode([
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciais inválidas']);
        }
    }

    private function logout() {
        session_destroy();
        echo json_encode(['success' => true]);
    }

    private function getDashboardData() {
        // Estatísticas gerais
        $stats = [];

        // Total de alunos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM alunos");
        $stats['totalStudents'] = $stmt->fetch()['total'];

        // Alunos ativos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM alunos WHERE status = 'active'");
        $stats['activeStudents'] = $stmt->fetch()['total'];

        // Total de equipamentos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM equipamentos");
        $stats['totalEquipment'] = $stmt->fetch()['total'];

        // Manutenções pendentes
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM equipamentos WHERE status IN ('broken', 'maintenance')");
        $stats['maintenanceNeeded'] = $stmt->fetch()['total'];

        // Estatísticas de equipamentos por status
        $stmt = $this->pdo->query("
            SELECT status, COUNT(*) as total 
            FROM equipamentos 
            GROUP BY status
        ");
        $equipmentStats = $stmt->fetchAll();
        
        foreach ($equipmentStats as $stat) {
            $stats['equipment' . ucfirst($stat['status'])] = $stat['total'];
        }

        // Atividades recentes
        $activities = [
            'Novo aluno cadastrado: João Silva',
            'Equipamento em manutenção: Leg Press 45°',
            'Pagamento recebido: Maria Santos - R$ 249,90',
            'Manutenção agendada: Bicicleta Ergométrica'
        ];

        echo json_encode([
            'stats' => $stats,
            'activities' => $activities
        ]);
    }

    private function getStudents() {
        $stmt = $this->pdo->query("
            SELECT a.*, p.nome as plano_nome, p.valor as plano_valor
            FROM alunos a
            LEFT JOIN planos p ON a.plano_id = p.id
            ORDER BY a.created_at DESC
        ");
        $students = $stmt->fetchAll();

        echo json_encode($students);
    }

    private function addStudent() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO alunos (nome, email, telefone, cpf, data_nascimento, plano_id, data_matricula)
            VALUES (?, ?, ?, ?, ?, ?, CURDATE())
        ");
        
        $result = $stmt->execute([
            $input['name'],
            $input['email'],
            $input['phone'],
            $input['cpf'],
            $input['birth'],
            $input['plan']
        ]);

        if ($result) {
            $studentId = $this->pdo->lastInsertId();
            
            // Criar pagamento automático
            $this->pdo->prepare("CALL sp_criar_pagamento_matricula(?, ?)")
                     ->execute([$studentId, $input['plan']]);
            
            echo json_encode(['success' => true, 'id' => $studentId]);
        } else {
            throw new Exception('Erro ao cadastrar aluno');
        }
    }

    private function deleteStudent() {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];

        $stmt = $this->pdo->prepare("DELETE FROM alunos WHERE id = ?");
        $result = $stmt->execute([$id]);

        echo json_encode(['success' => $result]);
    }

    private function getEquipment() {
        $stmt = $this->pdo->query("
            SELECT e.*, c.nome as categoria_nome
            FROM equipamentos e
            LEFT JOIN categorias_equipamentos c ON e.categoria_id = c.id
            ORDER BY e.created_at DESC
        ");
        $equipment = $stmt->fetchAll();

        echo json_encode($equipment);
    }

    private function addEquipment() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Buscar categoria_id pelo nome
        $stmt = $this->pdo->prepare("SELECT id FROM categorias_equipamentos WHERE nome = ?");
        $stmt->execute([ucfirst($input['category'])]);
        $category = $stmt->fetch();
        
        if (!$category) {
            // Criar nova categoria se não existir
            $stmt = $this->pdo->prepare("INSERT INTO categorias_equipamentos (nome) VALUES (?)");
            $stmt->execute([ucfirst($input['category'])]);
            $categoryId = $this->pdo->lastInsertId();
        } else {
            $categoryId = $category['id'];
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO equipamentos (nome, categoria_id, marca, numero_serie, data_aquisicao, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $input['name'],
            $categoryId,
            $input['brand'],
            $input['serial'],
            $input['date'],
            $input['status']
        ]);

        if ($result) {
            echo json_encode(['success' => true, 'id' => $this->pdo->lastInsertId()]);
        } else {
            throw new Exception('Erro ao cadastrar equipamento');
        }
    }

    private function deleteEquipment() {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];

        $stmt = $this->pdo->prepare("DELETE FROM equipamentos WHERE id = ?");
        $result = $stmt->execute([$id]);

        echo json_encode(['success' => $result]);
    }

    private function getMaintenance() {
        $stmt = $this->pdo->query("
            SELECT m.*, e.nome as equipamento_nome
            FROM manutencoes m
            JOIN equipamentos e ON m.equipamento_id = e.id
            ORDER BY m.data_agendada DESC
        ");
        $maintenance = $stmt->fetchAll();

        echo json_encode($maintenance);
    }

    private function addMaintenance() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO manutencoes (equipamento_id, tipo, data_agendada, tecnico_responsavel, descricao)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $input['equipmentId'],
            $input['type'],
            $input['date'],
            $input['technician'],
            $input['description']
        ]);

        if ($result) {
            // Atualizar status do equipamento
            $this->pdo->prepare("UPDATE equipamentos SET status = 'maintenance' WHERE id = ?")
                     ->execute([$input['equipmentId']]);
            
            echo json_encode(['success' => true, 'id' => $this->pdo->lastInsertId()]);
        } else {
            throw new Exception('Erro ao agendar manutenção');
        }
    }

    private function completeMaintenance() {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];

        $stmt = $this->pdo->prepare("
            UPDATE manutencoes 
            SET status = 'concluido', data_realizada = CURDATE() 
            WHERE id = ?
        ");
        $result = $stmt->execute([$id]);

        echo json_encode(['success' => $result]);
    }

    private function deleteMaintenance() {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'];

        $stmt = $this->pdo->prepare("DELETE FROM manutencoes WHERE id = ?");
        $result = $stmt->execute([$id]);

        echo json_encode(['success' => $result]);
    }

    private function getPlans() {
        $stmt = $this->pdo->query("SELECT * FROM planos ORDER BY duracao_meses");
        $plans = $stmt->fetchAll();

        // Contar alunos por plano
        foreach ($plans as &$plan) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM alunos WHERE plano_id = ?");
            $stmt->execute([$plan['id']]);
            $plan['total_alunos'] = $stmt->fetch()['total'];
        }

        echo json_encode($plans);
    }

    private function getPayments() {
        $stmt = $this->pdo->query("
            SELECT p.*, a.nome as aluno_nome, pl.nome as plano_nome
            FROM pagamentos p
            JOIN alunos a ON p.aluno_id = a.id
            JOIN planos pl ON p.plano_id = pl.id
            ORDER BY p.data_vencimento DESC
            LIMIT 10
        ");
        $payments = $stmt->fetchAll();

        // Estatísticas de pagamentos
        $stats = [];
        
        $stmt = $this->pdo->query("
            SELECT 
                SUM(CASE WHEN status = 'pago' AND MONTH(data_pagamento) = MONTH(CURDATE()) THEN valor ELSE 0 END) as recebido,
                SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END) as pendente,
                SUM(CASE WHEN status = 'atrasado' THEN valor ELSE 0 END) as atrasado,
                SUM(valor) as total
            FROM pagamentos
            WHERE YEAR(data_vencimento) = YEAR(CURDATE())
        ");
        $stats = $stmt->fetch();

        echo json_encode([
            'payments' => $payments,
            'stats' => $stats
        ]);
    }
}

// Executar API
$api = new GymAPI();
$api->handleRequest();
?>