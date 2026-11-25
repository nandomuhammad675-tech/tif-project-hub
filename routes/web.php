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

// Student - Projects (katalog = galeri)
$router->get('/projects',              'ProjectController@index', ['student']);
$router->get('/projects/{slug}',       'ProjectController@show',  ['student']);
$router->post('/projects/{id}/claim',  'ProjectController@claim', ['student']);
$router->get('/projects/{id}/edit',    'ProjectController@edit',  ['student']);
$router->post('/projects/{id}/update', 'ProjectController@update', ['student']);

// Student - Project War / Klaim Tiket
$router->get('/claims/select',  'ClaimController@select', ['student']);
$router->post('/claims',        'ClaimController@store',  ['student']);
$router->get('/claims/{id}',    'ClaimController@show',   ['student']);

// Student - Wishlist
$router->get('/wishlists',             'WishlistController@index',  ['student']);
$router->post('/wishlists/{id}/add',   'WishlistController@add',    ['student']);
$router->post('/wishlists/{id}/remove','WishlistController@remove', ['student']);

// Student - Profile
$router->get('/profile', 'ProfileController@index', ['student']);
$router->post('/profile/avatar', 'ProfileController@updateAvatar', ['student']);
$router->post('/profile/avatar/remove', 'ProfileController@removeAvatar', ['student']);
$router->get('/profile/edit', 'ProfileController@edit', ['student']);
$router->post('/profile/update', 'ProfileController@update', ['student']);
$router->post('/profile/password', 'ProfileController@updatePassword', ['student']);
$router->get('/profile/students/search', 'ProfileController@searchStudents', ['student']);
$router->post('/profile/projects/{id}/members/add', 'ProfileController@addMember', ['student']);
$router->post('/profile/projects/{id}/members/remove', 'ProfileController@removeMember', ['student']);
$router->post('/profile/projects/{id}/leave', 'ProfileController@leaveProject', ['student']);

// Admin
$router->get('/admin', 'AdminController@dashboard', ['admin']);

// Admin - Students
$router->get('/admin/students',              'AdminController@students',       ['admin']);
$router->get('/admin/students/{id}',          'AdminController@studentShow',    ['admin']);
$router->post('/admin/students/{id}/delete', 'AdminController@studentDelete',  ['admin']);

// Admin - Projects
$router->get('/admin/projects',              'AdminController@projects',       ['admin']);
$router->get('/admin/projects/create',       'AdminController@projectCreate',  ['admin']);
$router->post('/admin/projects',             'AdminController@projectStore',   ['admin']);
$router->get('/admin/projects/{id}/edit',    'AdminController@projectEdit',    ['admin']);
$router->post('/admin/projects/{id}',        'AdminController@projectUpdate',  ['admin']);
$router->post('/admin/projects/{id}/delete', 'AdminController@projectDelete',  ['admin']);

// Admin - Project War
$router->get('/admin/war',                    'AdminController@war',              ['admin']);
$router->post('/admin/war',                   'AdminController@warUpdate',        ['admin']);
$router->get('/admin/claims',                 'AdminController@claims',           ['admin']);
$router->get('/admin/violations',             'AdminController@violations',       ['admin']);
$router->post('/admin/violations/{id}/review','AdminController@violationReview',  ['admin']);