<?php
// ============================================================
//  CONTROLLER: Auth – Login y Logout
// ============================================================
require_once APP_ROOT . '/core/Controller.php';
require_once APP_ROOT . '/core/Middleware.php';
require_once APP_ROOT . '/app/models/Usuario.php';
require_once APP_ROOT . '/app/models/Vendedor.php';

class AuthController extends Controller {

    private Usuario $usuario;
    private Vendedor $vendedor;

    public function __construct() {
        $this->usuario = new Usuario();
        $this->vendedor = new Vendedor();
    }

    // GET/POST /auth/login
    public function login(): void {
        Middleware::guest();

        $error = '';

        if ($this->isPost()) {
            $email    = $this->sanitize($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$email || !$password) {
                $error = 'Completa todos los campos.';
            } else {
                $user = $this->usuario->findByEmail($email);
                
                if ($user && $this->usuario->verifyPassword($password, $user->password)) {
                    // Login exitoso Administrador
                    session_regenerate_id(true);
                    $_SESSION['usuario_id']     = $user->id;
                    $_SESSION['usuario_nombre'] = $user->nombre;
                    $_SESSION['usuario_email']  = $user->email;
                    $_SESSION['usuario_rol']    = 'admin';
                    $this->redirect('admin/dashboard');
                } else {
                    // Intentar buscar como Vendedor
                    $vend = $this->vendedor->findByEmail($email);
                    if ($vend && password_verify($password, $vend->password)) {
                        // Login exitoso Vendedor
                        session_regenerate_id(true);
                        $_SESSION['usuario_id']     = $vend->id;
                        $_SESSION['usuario_nombre'] = $vend->nombre . ' ' . $vend->apellido;
                        $_SESSION['usuario_email']  = $vend->email;
                        $_SESSION['usuario_rol']    = 'vendedor';
                        
                        if ($vend->requiere_cambio_pass) {
                            $this->redirect('auth/cambiar_password');
                        } else {
                            $this->redirect('admin/dashboard');
                        }
                    } else {
                        // Registrar intento fallido (R6)
                        Middleware::logFailedLogin($email);
                        $error = 'Credenciales incorrectas. Verifica tu email y contraseña.';
                    }
                }
            }
        }

        $this->render('auth/login', ['error' => $error]);
    }

    // GET/POST /auth/cambiar_password
    public function cambiar_password(): void {
        Middleware::auth(); // Debe estar logueado
        if ($_SESSION['usuario_rol'] !== 'vendedor') {
            $this->redirect('admin/dashboard');
        }

        $error = '';
        $success = '';

        if ($this->isPost()) {
            $pass1 = $_POST['password'] ?? '';
            $pass2 = $_POST['password_confirm'] ?? '';

            if (empty($pass1) || empty($pass2)) {
                $error = 'Completa todos los campos.';
            } elseif ($pass1 !== $pass2) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($pass1) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } else {
                $hashed = password_hash($pass1, PASSWORD_DEFAULT);
                $this->vendedor->update($_SESSION['usuario_id'], [
                    'password' => $hashed,
                    'requiere_cambio_pass' => 0
                ]);
                $this->flash('success', 'Contraseña actualizada correctamente. Bienvenido.');
                $this->redirect('admin/dashboard');
            }
        }

        $this->render('auth/cambiar_password', ['error' => $error, 'success' => $success]);
    }

    // GET /auth/logout
    public function logout(): void {
        session_destroy();
        $this->redirect('auth/login');
    }
}
