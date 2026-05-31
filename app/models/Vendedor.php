<?php
// ============================================================
//  MODEL: Vendedor
// ============================================================
require_once APP_ROOT . '/core/Model.php';

class Vendedor extends Model {
    protected string $table = 'vendedores';

    // Obtener vendedores para selects
    public function listaParaSelect(): array {
        return $this->findAll('nombre ASC');
    }

    public function findByEmail(string $email) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
