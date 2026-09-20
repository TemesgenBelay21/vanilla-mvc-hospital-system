<?php

/**
 * Front controller / bootstrap + URL router.
 *
 * Pretty URLs are produced by public/.htaccess, which rewrites
 * everything non-asset to index.php?url=<path>.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

// -------------------------------------------------------------
// Route table. Patterns may contain {param} placeholders.
// Order matters — more specific patterns come first.
// -------------------------------------------------------------
$routes = [
    'GET' => [
        '/'                            => ['AuthController', 'redirectFromHome'],
        '/login'                       => ['AuthController', 'loginForm'],
        '/logout'                      => ['AuthController', 'logout'],
        '/register'                    => ['AuthController', 'registerForm'],

        // Dashboards
        '/admin'                       => ['DashboardController', 'admin'],
        '/receptionist'                => ['DashboardController', 'receptionist'],
        '/doctor'                      => ['DashboardController', 'doctor'],
        '/patient'                     => ['DashboardController', 'patient'],

        // Patients (admin + receptionist)
        '/admin/patients'              => ['PatientController', 'index'],
        '/admin/patients/{id}/profile' => ['PatientController', 'profile'],
        '/receptionist/patients'                => ['PatientController', 'index'],
        '/receptionist/patients/create'         => ['PatientController', 'create'],
        '/receptionist/patients/{id}/profile'   => ['PatientController', 'profile'],

        // Departments (admin)
        '/admin/departments'                    => ['DepartmentController', 'index'],
        '/admin/departments/create'             => ['DepartmentController', 'create'],
        '/admin/departments/{id}/edit'          => ['DepartmentController', 'edit'],

        // Staff accounts (admin)
        '/admin/staff'                          => ['StaffController', 'index'],
        '/admin/staff/doctors/create'           => ['StaffController', 'doctorCreate'],
        '/admin/staff/receptionists/create'     => ['StaffController', 'receptionistCreate'],
        '/admin/staff/{id}/edit'                => ['StaffController', 'edit'],

        // Doctor availability
        '/doctor/availability'                  => ['AvailabilityController', 'index'],
    ],
    'POST' => [
        '/login'                       => ['AuthController', 'login'],
        '/register'                    => ['AuthController', 'register'],

        // Patients
        '/receptionist/patients/store' => ['PatientController', 'store'],

        // Departments
        '/admin/departments/store'             => ['DepartmentController', 'store'],
        '/admin/departments/{id}/update'       => ['DepartmentController', 'update'],
        '/admin/departments/{id}/delete'       => ['DepartmentController', 'destroy'],

        // Staff accounts
        '/admin/staff/doctors/store'           => ['StaffController', 'doctorStore'],
        '/admin/staff/receptionists/store'     => ['StaffController', 'receptionistStore'],
        '/admin/staff/{id}/update'             => ['StaffController', 'update'],
        '/admin/staff/{id}/toggle-status'      => ['StaffController', 'toggleStatus'],
        '/admin/staff/{id}/reset-password'     => ['StaffController', 'resetPassword'],

        // Doctor availability
        '/doctor/availability/store'           => ['AvailabilityController', 'store'],
        '/doctor/availability/{id}/destroy'    => ['AvailabilityController', 'destroy'],
    ],
];

// -------------------------------------------------------------
// Dispatch
// -------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$path   = '/' . trim($_GET['url'] ?? '', '/');
if ($path !== '/') {
    $path = rtrim($path, '/');
}

[$controllerClass, $action, $params] = dispatch($routes, $method, $path);

$controller = new $controllerClass();
call_user_func_array([$controller, $action], [$params]);

// -------------------------------------------------------------
// Matching
// -------------------------------------------------------------
/**
 * @param array{g: array<string, array{0:string,1:string}>} $routes
 * @return array{0:string,1:string,2:array<string,string>}
 */
function dispatch(array $routes, string $method, string $path): array
{
    foreach ($routes[$method] ?? [] as $pattern => $handler) {
        $params = match_route($pattern, $path);
        if ($params !== null) {
            return [$handler[0], $handler[1], $params];
        }
    }

    foreach ($routes['GET'] ?? [] as $pattern => $handler) {
        $params = match_route($pattern, $path);
        if ($params !== null) {
            return [$handler[0], $handler[1], $params];
        }
    }

    http_response_code(404);
    $content = render_partial('errors/404', ['path' => $path]);
    require APP_ROOT . '/views/layouts/guest.php';
    exit;
}

/**
 * Match a pattern (e.g. "/admin/patients/{id}/profile") against a URL path.
 * Returns capture params, or null when it does not match.
 */
function match_route(string $pattern, string $path): ?array
{
    $patternParts = explode('/', trim($pattern, '/'));
    $pathParts    = $path === '/' ? [''] : explode('/', trim($path, '/'));

    if (count($patternParts) !== count($pathParts)) {
        return null;
    }

    $params = [];
    foreach ($patternParts as $i => $segment) {
        if (strlen($segment) > 2 && $segment[0] === '{' && substr($segment, -1) === '}') {
            $key = substr($segment, 1, -1);
            $params[$key] = urldecode($pathParts[$i]);
        } elseif ($segment !== $pathParts[$i]) {
            return null;
        }
    }
    return $params;
}