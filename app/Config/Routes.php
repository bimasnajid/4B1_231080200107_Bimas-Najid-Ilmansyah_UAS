<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
// Auth routes
$routes->group('api/auth', function ($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('register', 'AuthController::register');
    $routes->post('refresh', 'AuthController::refresh', ['filter' => 'jwt']); // ✅ Bug #1 fixed
});

// User routes - ✅ Bug #2 fixed (now inside /api)
$routes->group('api/users', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'UserController::index');
    $routes->get('(:num)', 'UserController::show/$1');
    $routes->put('(:num)', 'UserController::update/$1');
    $routes->delete('(:num)', 'UserController::delete/$1');
});

// Project routes
$routes->group('api/projects', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'ProjectController::index');
    $routes->post('/', 'ProjectController::create');
    $routes->get('(:num)', 'ProjectController::show/$1');
    $routes->put('(:num)', 'ProjectController::update/$1');
    $routes->delete('(:num)', 'ProjectController::delete/$1');
});

// Task routes - ✅ Bug #3 fixed (filter name corrected)
$routes->group('api/tasks', ['filter' => 'jwt'], function ($routes) {
    $routes->get('/', 'TaskController::index');
    $routes->post('/', 'TaskController::create');
    $routes->get('(:num)', 'TaskController::show/$1');
    $routes->put('(:num)', 'TaskController::update/$1');
    $routes->delete('(:num)', 'TaskController::delete/$1');
});