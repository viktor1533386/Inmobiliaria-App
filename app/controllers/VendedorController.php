<?php
// ============================================================
//  CONTROLLER: Vendedor – CRUD
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/core/Mailer.php';
require_once APP_ROOT . '/app/models/Vendedor.php';

class VendedorController extends Controller {

    private Vendedor $vendedor;

    public function __construct() {
        $this->vendedor = new Vendedor();
    }

    // GET /vendedor
    public function index(): void {
        Middleware::authAdmin();
        $vendedores = $this->vendedor->findAll('nombre ASC');
        $this->render('vendedores/index', [
            'titulo'     => 'Gestión de Vendedores',
            'vendedores' => $vendedores,
        ]);
    }

    // GET/POST /vendedor/crear
    public function crear(): void {
        Middleware::authAdmin();
        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => $this->sanitize($_POST['telefono'] ?? ''),
                'dni'          => $this->sanitize($_POST['dni'] ?? ''),
                'especialidad' => $this->sanitize($_POST['especialidad'] ?? ''),
                'linkedin'     => $this->sanitize($_POST['linkedin'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
            }

            if (empty($errores)) {
                // Generar contraseña aleatoria
                $randomPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 8);
                $datos['password'] = password_hash($randomPass, PASSWORD_DEFAULT);
                $datos['requiere_cambio_pass'] = 1;

                $this->vendedor->insert($datos);
                
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

        $this->render('vendedores/crear', [
            'titulo'  => 'Nuevo Vendedor',
            'errores' => $errores,
        ]);
    }

    // GET/POST /vendedor/editar/{id}
    public function editar(string $id = '0'): void {
        Middleware::authAdmin();
        $vendedor = $this->vendedor->findById((int)$id);
        if (!$vendedor) $this->redirect('vendedor');

        $errores = [];

        if ($this->isPost()) {
            $datos = [
                'nombre'       => $this->sanitize($_POST['nombre']   ?? ''),
                'apellido'     => $this->sanitize($_POST['apellido'] ?? ''),
                'email'        => $this->sanitize($_POST['email']    ?? ''),
                'telefono'     => $this->sanitize($_POST['telefono'] ?? ''),
                'dni'          => $this->sanitize($_POST['dni'] ?? ''),
                'especialidad' => $this->sanitize($_POST['especialidad'] ?? ''),
                'linkedin'     => $this->sanitize($_POST['linkedin'] ?? ''),
            ];

            if (empty($datos['nombre']))   $errores[] = 'El nombre es obligatorio.';
            if (empty($datos['apellido'])) $errores[] = 'El apellido es obligatorio.';
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                $errores[] = 'El email no es válido.';
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
        Middleware::authAdmin();
        $this->vendedor->delete((int)$id);
        $this->flash('success', 'Vendedor eliminado.');
        $this->redirect('vendedor');
    }
}
