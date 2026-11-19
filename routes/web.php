<?php

/**
 * Web Routes - TIF Hub
 */

use App\Core\Router;

/** @var Router $router */

// Auth
$router->get('/login',     'AuthController@showLogin',    ['guest']);
$router->post('/login',    'AuthController@login',        ['guest']);
$router->get('/register',  'AuthController@showRegister', ['guest']);
$router->post('/register', 'AuthController@register',     ['guest']);
$router->get('/logout',    'AuthController@logout',       ['auth']);

// Student - Home
$router->get('/', 'HomeController@index', ['student']);

// Student - Projects
$router->get('/projects',              'ProjectController@index', ['student']);
$router->get('/projects/{slug}',       'ProjectController@show',  ['student']);
$router->post('/projects/{id}/claim',  'ProjectController@claim', ['student']);

// Student - Tickets
$router->get('/tickets',             'TicketController@index',  ['student']);
$router->get('/tickets/create',      'TicketController@create', ['student']);
$router->post('/tickets',            'TicketController@store',  ['student']);
$router->get('/tickets/{id}',        'TicketController@show',   ['student']);
$router->post('/tickets/{id}/reply', 'TicketController@reply',  ['student']);

// Student - Profile
$router->get('/profile', 'ProfileController@index', ['student']);

// Admin
$router->get('/admin', 'AdminController@dashboard', ['admin']);

// Admin - Students
$router->get('/admin/students',              'AdminController@students',       ['admin']);
$router->get('/admin/students/create',       'AdminController@studentCreate',  ['admin']);
$router->post('/admin/students',             'AdminController@studentStore',   ['admin']);
$router->get('/admin/students/{id}/edit',    'AdminController@studentEdit',    ['admin']);
$router->post('/admin/students/{id}',        'AdminController@studentUpdate',  ['admin']);
$router->post('/admin/students/{id}/delete', 'AdminController@studentDelete',  ['admin']);

// Admin - Projects
$router->get('/admin/projects',              'AdminController@projects',       ['admin']);
$router->get('/admin/projects/create',       'AdminController@projectCreate',  ['admin']);
$router->post('/admin/projects',             'AdminController@projectStore',   ['admin']);
$router->get('/admin/projects/{id}/edit',    'AdminController@projectEdit',    ['admin']);
$router->post('/admin/projects/{id}',        'AdminController@projectUpdate',  ['admin']);
$router->post('/admin/projects/{id}/delete', 'AdminController@projectDelete',  ['admin']);

// Admin - Tickets
$router->get('/admin/tickets',       'AdminController@tickets',     ['admin']);
$router->get('/admin/tickets/{id}',  'AdminController@ticketShow',  ['admin']);
$router->post('/admin/tickets/{id}', 'AdminController@ticketReply', ['admin']);
