<?php
// ============================================================
//  CONTROLLER: Vendedor – CRUD
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Vendedor.php';
require_once APP_ROOT . '/app/models/Usuario.php';

class VendedorController extends Controller {

    private Vendedor $vendedor;
    private Usuario $usuario;

    public function __construct() {
        $this->vendedor = new Vendedor();
        $this->usuario = new Usuario();
    }

    // GET /vendedor
    public function index(): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $vendedores = $this->vendedor->findAll('nombre ASC');
        $this->render('vendedores/index', [
            'titulo'     => 'Gestión de Vendedores',
            'vendedores' => $vendedores,
        ]);
    }

    // GET/POST /vendedor/crear
    public function crear(): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => preg_replace('/[^0-9+]/', '', $_POST['telefono'] ?? ''),
                'dni'          => preg_replace('/[^0-9]/', '', $_POST['dni'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
            }
            if (strlen($datos['dni']) !== 8) {
                $errores[] = 'El DNI debe tener exactamente 8 dígitos.';
            }
            
            // Auto-formatear teléfono si tiene 9 dígitos sin prefijo
            $telefonoPuro = preg_replace('/[^0-9]/', '', $datos['telefono']);
            if (strlen($telefonoPuro) === 9) {
                $datos['telefono'] = '+51 ' . $telefonoPuro;
            } elseif (strlen($telefonoPuro) !== 11) { // 11 considerando 51999...
                $errores[] = 'El teléfono debe tener 9 dígitos.';
            }

            if (empty($errores)) {
                // Generar contraseña aleatoria
                $randomPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 8);
                
                // 1. Crear el Usuario de acceso para el vendedor
                try {
                    $usuarioId = $this->usuario->insert([
                        'nombre'   => $datos['nombre'] . ' ' . $datos['apellido'],
                        'email'    => $datos['email'],
                        'password' => password_hash($randomPass, PASSWORD_BCRYPT),
                        'rol'      => 'vendedor',
                        'estado'   => 1,
                        'password_reset_required' => 1
                    ]);

                    // 2. Crear el Vendedor vinculándolo al usuario
                    $datos['usuario_id'] = (int)$usuarioId;
                    $this->vendedor->insert($datos);
                } catch (Exception $e) {
                    $errores[] = 'El correo electrónico ya está en uso por otro usuario.';
                }
                
                if (empty($errores)) {
                
                // Enviar correo real usando PHPMailer
                $asunto = "Bienvenido a Hogar Ideal Perú - Tus Credenciales";
                $cuerpo = "
                    <h2>¡Hola {$datos['nombre']}!</h2>
                    <p>Has sido registrado como agente inmobiliario en <strong>Hogar Ideal Perú</strong>.</p>
                    <p>Tus credenciales temporales de acceso son:</p>
                    <ul>
                        <li><strong>Email:</strong> {$datos['email']}</li>
                        <li><strong>Contraseña:</strong> $randomPass</li>
                    </ul>
                    <p>Por seguridad, el sistema te pedirá cambiar esta contraseña la primera vez que ingreses al panel.</p>
                    <p>Puedes iniciar sesión aquí: <a href='" . BASE_URL . "/auth/login'>" . BASE_URL . "/auth/login</a></p>
                ";
                
                $enviado = Mailer::send($datos['email'], $asunto, $cuerpo);
                
                if ($enviado) {
                    $this->flash('success', "Vendedor registrado. Se envió el correo con credenciales a {$datos['email']}.");
                } else {
                    $this->flash('success', "Vendedor registrado, pero ocurrió un error al enviar el correo. Contraseña temporal: $randomPass");
                }
                
                $this->redirect('vendedor');
                }
            }
        }

        $this->render('vendedores/crear', [
            'titulo'  => 'Nuevo Vendedor',
            'errores' => $errores,
        ]);
    }

    // GET/POST /vendedor/editar/{id}
    public function editar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $vendedor = $this->vendedor->findById((int)$id);
        if (!$vendedor) $this->redirect('vendedor');

        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => preg_replace('/[^0-9+]/', '', $_POST['telefono'] ?? ''),
                'dni'          => preg_replace('/[^0-9]/', '', $_POST['dni'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
            }
            if (strlen($datos['dni']) !== 8) {
                $errores[] = 'El DNI debe tener exactamente 8 dígitos.';
            }

            // Auto-formatear teléfono si tiene 9 dígitos sin prefijo
            $telefonoPuro = preg_replace('/[^0-9]/', '', $datos['telefono']);
            if (strlen($telefonoPuro) === 9) {
                $datos['telefono'] = '+51 ' . $telefonoPuro;
            } elseif (strlen($telefonoPuro) !== 11) { // 11 considerando 51999...
                $errores[] = 'El teléfono debe tener 9 dígitos.';
            }

            if (empty($errores)) {
                $this->vendedor->update((int)$id, $datos);
                $this->flash('success', 'Vendedor actualizado correctamente.');
                $this->redirect('vendedor');
            }
        }

        $this->render('vendedores/editar', [
            'titulo'   => 'Editar Vendedor',
            'vendedor' => $vendedor,
            'errores'  => $errores,
        ]);
    }

    // GET /vendedor/eliminar/{id}
    public function eliminar(string $id = '0'): void {
        Middleware::requireRole(['admin', 'supervisor']);
        $vendedor = $this->vendedor->findById((int)$id);
        if ($vendedor && $vendedor->usuario_id) {
            $this->usuario->delete((int)$vendedor->usuario_id);
        }
        $this->vendedor->delete((int)$id);
        $this->flash('success', 'Vendedor eliminado.');
        $this->redirect('vendedor');
    }
}
