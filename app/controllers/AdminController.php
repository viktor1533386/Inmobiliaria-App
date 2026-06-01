<?php
// ============================================================
//  CONTROLLER: Admin – Dashboard
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Propiedad.php';
require_once APP_ROOT . '/app/models/Vendedor.php';
require_once APP_ROOT . '/app/models/Mensaje.php';

class AdminController extends Controller {

    // GET /admin/dashboard
    public function dashboard(): void {
        Middleware::requireRole(['admin', 'supervisor', 'vendedor']);

        $propiedad = new Propiedad();
        $vendedor  = new Vendedor();
        $mensaje   = new Mensaje();

        $this->render('admin/dashboard', [
            'titulo'          => 'Dashboard – Panel Admin',
            'totalPropiedades'=> $propiedad->count(),
            'totalActivas'    => $propiedad->count('activo = 1'),
            'totalVendedores' => $vendedor->count(),
            'totalMensajes'   => $mensaje->count(),
            'noLeidos'        => $mensaje->noLeidos(),
            'ultimasProp'     => $propiedad->ultimas(5),
            'ultimosMensajes' => $mensaje->findAll('created_at DESC'),
        ]);
    }

    // GET /admin/backup
    public function backup(): void {
        Middleware::requireRole(['admin', 'supervisor']);
        
        $db = Database::getInstance();
        $tables = [];
        
        // Obtener tablas
        $stmt = $db->query("SHOW TABLES");
        $results = $stmt->fetchAll(PDO::FETCH_NUM);
        foreach ($results as $row) {
            $tables[] = $row[0];
        }

        $sqlScript = "-- ==========================================\n";
        $sqlScript .= "-- Backup de Base de Datos Hogar Ideal Perú\n";
        $sqlScript .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
        $sqlScript .= "-- ==========================================\n\n";

        foreach ($tables as $table) {
            // Estructura
            $stmt = $db->query("SHOW CREATE TABLE `{$table}`");
            $row = $stmt->fetch(PDO::FETCH_NUM);
            
            $sqlScript .= "-- --------------------------------------------------------\n";
            $sqlScript .= "-- Estructura de la tabla `{$table}`\n";
            $sqlScript .= "-- --------------------------------------------------------\n\n";
            $sqlScript .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sqlScript .= $row[1] . ";\n\n";

            // Datos
            $stmt = $db->query("SELECT * FROM `{$table}`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sqlScript .= "-- Volcado de datos para la tabla `{$table}`\n\n";
                foreach ($rows as $rowData) {
                    $cols = array_keys($rowData);
                    $vals = array_values($rowData);
                    
                    $escapedVals = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes((string)$val) . "'";
                    }, $vals);

                    $sqlScript .= "INSERT INTO `{$table}` (`" . implode("`, `", $cols) . "`) VALUES (" . implode(", ", $escapedVals) . ");\n";
                }
                $sqlScript .= "\n\n";
            }
        }

        $filename = 'backup_hogarideal_' . date('Y_m_d_His') . '.sql';

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $sqlScript;
        exit;
    }

    // GET/POST /admin/restore
    public function restore(): void {
        Middleware::requireRole(['admin']);

        $error = '';
        if ($this->isPost()) {
            if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmp = $_FILES['backup_file']['tmp_name'];
                $fileName = $_FILES['backup_file']['name'];
                
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if ($ext !== 'sql') {
                    $error = 'El archivo debe tener la extensión .sql';
                } else {
                    $sqlContent = file_get_contents($fileTmp);
                    if (empty(trim($sqlContent))) {
                        $error = 'El archivo está vacío.';
                    } else {
                        try {
                            $db = Database::getInstance();
                            // Desactivar checks de claves foráneas para poder hacer DROP de tablas
                            $db->exec('SET FOREIGN_KEY_CHECKS=0;');
                            $db->exec($sqlContent);
                            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
                            
                            $this->flash('success', 'Base de datos restaurada correctamente a partir del respaldo.');
                            $this->redirect('admin/dashboard');
                            return;
                        } catch (Exception $e) {
                            // Intentar reactivar checks
                            @$db->exec('SET FOREIGN_KEY_CHECKS=1;');
                            $error = 'Error crítico al ejecutar el script SQL: ' . $e->getMessage();
                        }
                    }
                }
            } else {
                $error = 'No se ha subido ningún archivo válido.';
            }
        }

        $this->render('admin/restore', [
            'titulo' => 'Restaurar Sistema (Restore)',
            'error'  => $error
        ]);
    }
}
