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
        '/nurse'                       => ['DashboardController', 'nurse'],
        '/pharmacist'                  => ['DashboardController', 'pharmacist'],
        '/lab'                         => ['DashboardController', 'labTechnician'],
        '/accountant'                  => ['DashboardController', 'accountant'],

        // Reports (admin)
        '/admin/reports'               => ['ReportsController', 'index'],

        // Invoices & payments (accountant)
        '/accountant/invoices'         => ['AccountantController', 'index'],
        '/accountant/invoices/generate'=> ['AccountantController', 'generateForm'],
        '/accountant/invoices/{id}'    => ['AccountantController', 'show'],
        '/accountant/payments'         => ['AccountantController', 'payments'],

        // Patient billing portal
        '/patient/invoices'            => ['PaymentController', 'patientInvoices'],
        '/patient/telebirr/pay'        => ['PaymentController', 'gateway'],
        '/patient/telebirr/verify'     => ['PaymentController', 'verifyStatus'],

        // Notification bell (JSON)
        '/notifications'               => ['NotificationController', 'index'],

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

        // Laboratory (doctor + lab technician)
        '/doctor/patients/{id}/lab/new'         => ['LabController', 'create'],
        '/lab/requests'                         => ['LabController', 'index'],
        '/lab/requests/{id}'                    => ['LabController', 'show'],
        '/lab/files/{id}/download'              => ['LabController', 'download'],

        // Pharmacy (admin + pharmacist)
        '/admin/medicines'                      => ['PharmacyController', 'index'],
        '/admin/medicines/create'               => ['PharmacyController', 'create'],
        '/admin/medicines/{id}/edit'            => ['PharmacyController', 'edit'],
        '/pharmacist/medicines'                 => ['PharmacyController', 'pharmacistIndex'],
        '/pharmacist/dispense'                  => ['PharmacyController', 'queue'],
        '/pharmacist/dispense/{id}'             => ['PharmacyController', 'review'],

        // Staff accounts (admin)
        '/admin/staff'                          => ['StaffController', 'index'],
        '/admin/staff/doctors/create'           => ['StaffController', 'doctorCreate'],
        '/admin/staff/nurses/create'            => ['StaffController', 'nurseCreate'],
        '/admin/staff/pharmacists/create'       => ['StaffController', 'pharmacistCreate'],
        '/admin/staff/lab-technicians/create'   => ['StaffController', 'labTechnicianCreate'],
        '/admin/staff/receptionists/create'     => ['StaffController', 'receptionistCreate'],
        '/admin/staff/{id}/edit'                => ['StaffController', 'edit'],

        // Doctor availability
        '/doctor/availability'                  => ['AvailabilityController', 'index'],

        // Wards & beds (admin + nurse)
        '/admin/wards'                          => ['WardController', 'index'],
        '/admin/wards/create'                   => ['WardController', 'create'],
        '/admin/wards/{id}'                     => ['WardController', 'show'],
        '/admin/wards/{id}/edit'                => ['WardController', 'edit'],
        '/nurse/wards'                          => ['WardController', 'nurseIndex'],
        '/nurse/wards/{id}'                     => ['WardController', 'show'],

        // Admissions (admin + nurse)
        '/admin/admissions'                     => ['AdmissionController', 'index'],
        '/admin/admissions/new'                 => ['AdmissionController', 'create'],
        '/admin/admissions/{id}'                => ['AdmissionController', 'show'],
        '/nurse/admissions'                     => ['AdmissionController', 'index'],
        '/nurse/admissions/new'                 => ['AdmissionController', 'create'],
        '/nurse/admissions/{id}'                => ['AdmissionController', 'show'],

        // Appointment booking
        '/patient/book'                         => ['AppointmentController', 'create'],
        '/receptionist/appointments/book'       => ['AppointmentController', 'create'],
        '/appointments/doctors'                 => ['AppointmentController', 'doctorsByDepartment'],
        '/appointments/slots'                   => ['AppointmentController', 'slots'],

        // Appointment lists
        '/doctor/appointments'                  => ['AppointmentController', 'index'],
        '/receptionist/appointments'            => ['AppointmentController', 'index'],
'/patient/appointments'             => ['AppointmentController', 'index'],
        '/patient/medical'                  => ['PatientController', 'medical'],

        // Doctor patient care
        '/doctor/patients'                      => ['PatientController', 'doctorIndex'],
        '/doctor/patients/{id}/profile'         => ['PatientController', 'profile'],
        '/doctor/patients/{id}/prescriptions/new' => ['PrescriptionController', 'create'],

        // Reschedule forms
        '/doctor/appointments/{id}/reschedule'        => ['AppointmentController', 'rescheduleForm'],
        '/receptionist/appointments/{id}/reschedule' => ['AppointmentController', 'rescheduleForm'],
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
        '/admin/staff/nurses/store'            => ['StaffController', 'nurseStore'],
        '/admin/staff/pharmacists/store'       => ['StaffController', 'pharmacistStore'],
        '/admin/staff/lab-technicians/store'   => ['StaffController', 'labTechnicianStore'],
        '/admin/staff/receptionists/store'     => ['StaffController', 'receptionistStore'],
        '/admin/staff/{id}/update'             => ['StaffController', 'update'],
        '/admin/staff/{id}/toggle-status'      => ['StaffController', 'toggleStatus'],
        '/admin/staff/{id}/reset-password'     => ['StaffController', 'resetPassword'],

        // Doctor availability
        '/doctor/availability/store'           => ['AvailabilityController', 'store'],
        '/doctor/availability/{id}/destroy'    => ['AvailabilityController', 'destroy'],

// Appointment booking
        '/patient/book'                                    => ['AppointmentController', 'store'],
        '/receptionist/appointments/book'                  => ['AppointmentController', 'store'],

        // Wards & beds (admin)
        '/admin/wards/store'                              => ['WardController', 'store'],
        '/admin/wards/{id}/update'                        => ['WardController', 'update'],
        '/admin/wards/{id}/delete'                        => ['WardController', 'destroy'],
        '/admin/wards/{id}/beds/store'                    => ['WardController', 'bedStore'],
        '/admin/beds/{id}/delete'                         => ['WardController', 'bedDestroy'],

        // Admissions (admin + nurse)
        '/admin/admissions/store'                         => ['AdmissionController', 'store'],
        '/nurse/admissions/store'                         => ['AdmissionController', 'store'],
        '/admin/admissions/{id}/vitals/store'             => ['AdmissionController', 'vitalsStore'],
        '/nurse/admissions/{id}/vitals/store'             => ['AdmissionController', 'vitalsStore'],
        '/admin/admissions/{id}/discharge'                => ['AdmissionController', 'discharge'],
        '/nurse/admissions/{id}/discharge'                => ['AdmissionController', 'discharge'],

        // Pharmacy
        '/admin/medicines/store'                   => ['PharmacyController', 'store'],
        '/admin/medicines/{id}/update'             => ['PharmacyController', 'update'],
        '/admin/medicines/{id}/delete'             => ['PharmacyController', 'destroy'],
        '/admin/medicines/{id}/restock'            => ['PharmacyController', 'restock'],
        '/pharmacist/prescriptions/{id}/dispense'  => ['PharmacyController', 'dispense'],

        // Laboratory
        '/doctor/patients/{id}/lab/store'    => ['LabController', 'store'],
        '/lab/requests/{id}/start'           => ['LabController', 'start'],
        '/lab/requests/{id}/complete'        => ['LabController', 'complete'],

        // Prescriptions (doctor)
        '/doctor/patients/{id}/prescriptions/store'  => ['PrescriptionController', 'store'],

        // Management actions
        '/doctor/appointments/{id}/approve'                => ['AppointmentController', 'approve'],
        '/doctor/appointments/{id}/reject'                 => ['AppointmentController', 'reject'],
        '/doctor/appointments/{id}/complete'               => ['AppointmentController', 'complete'],
        '/doctor/appointments/{id}/reschedule'             => ['AppointmentController', 'reschedule'],
        '/receptionist/appointments/{id}/approve'          => ['AppointmentController', 'approve'],
        '/receptionist/appointments/{id}/reject'           => ['AppointmentController', 'reject'],
        '/receptionist/appointments/{id}/reschedule'       => ['AppointmentController', 'reschedule'],
        '/patient/appointments/{id}/cancel'                => ['AppointmentController', 'cancel'],

        // Billing (accountant cashier actions)
        '/accountant/invoices/generate'        => ['AccountantController', 'generate'],
        '/accountant/invoices/{id}/pay'        => ['AccountantController', 'payCash'],

        // Billing (patient online payment via Telebirr)
        '/patient/invoices/{id}/pay'           => ['PaymentController', 'initiate'],

        // Telebirr callback — PUBLIC, signature-guarded inside the service.
        '/payment/telebirr/webhook'            => ['PaymentController', 'webhook'],

        // Notifications (JSON)
        '/notifications/mark-all-read'         => ['NotificationController', 'markAllRead'],
        '/notifications/{id}/read'             => ['NotificationController', 'markRead'],
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